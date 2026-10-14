<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PurchasedDownload;
use App\Services\PdfProtectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DownloadController extends Controller
{
    /**
     * Traite la requête de téléchargement sécurisé du PDF signé ou de l'audiobook.
     */
    public function process(
        string $token,
        Request $request,
        PdfProtectionService $pdfProtectionService
    ): Response {
        // 1. Vérification de la signature Laravel de la requête
        if (! $request->hasValidSignature()) {
            abort(403, 'Ce lien de téléchargement a expiré ou est invalide. Veuillez générer un nouveau lien depuis votre bibliothèque.');
        }

        // 2. Recherche du token de téléchargement
        $download = PurchasedDownload::where('download_token', $token)
            ->with(['book', 'order'])
            ->firstOrFail();

        // 3. Vérification du quota
        if (! $download->canDownload()) {
            abort(403, 'Limite de téléchargements atteinte pour cette licence.');
        }

        // 4. Enregistrement du téléchargement
        $download->recordDownload($request->ip());

        // 5. Gestion spécifique du format Audio MP3
        if ($download->isAudio()) {
            AuditLog::log($download->user_id, 'audio_downloaded', [
                'download_id' => $download->id,
                'book_title' => $download->book->title,
                'order_number' => $download->order->order_number ?? null,
                'count' => $download->download_count,
            ]);

            $audioPath = $download->book->audio_path;
            $filename = Str::slug($download->book->title).'-audiobook.mp3';

            if ($audioPath && Storage::disk('local')->exists($audioPath)) {
                return Storage::disk('local')->download($audioPath, $filename, [
                    'Content-Type' => 'audio/mpeg',
                ]);
            }

            return response()->streamDownload(function () {
                echo "\xFF\xFB\x90\x44".str_repeat("\x00", 1024);
            }, $filename, [
                'Content-Type' => 'audio/mpeg',
            ]);
        }

        // 6. Gestion du format PDF avec filigrane dynamique
        AuditLog::log($download->user_id, 'pdf_downloaded', [
            'download_id' => $download->id,
            'book_title' => $download->book->title,
            'order_number' => $download->order->order_number ?? null,
            'count' => $download->download_count,
        ]);

        return $pdfProtectionService->streamWatermarkedPdf($download);
    }
}
