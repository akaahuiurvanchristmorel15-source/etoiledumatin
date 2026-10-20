<?php

namespace App\Services;

use App\Models\Book;
use App\Models\PurchasedDownload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfProtectionService
{
    /**
     * Génère un lien de téléchargement temporaire signé.
     */
    public function generateSignedDownloadUrl(PurchasedDownload $download, int $expirationMinutes = 15): string
    {
        return URL::temporarySignedRoute(
            'downloads.process',
            now()->addMinutes($expirationMinutes),
            ['token' => $download->download_token]
        );
    }

    /**
     * Applique le filigrane dynamique et renvoie le flux binaire en téléchargement direct.
     */
    public function streamWatermarkedPdf(PurchasedDownload $download): StreamedResponse
    {
        $book = $download->book;
        $relativePath = $book->pdf_path;

        // Si le livre n'a pas de PDF ou si le fichier n'existe pas, on génère un PDF modèle sécurisé
        $sourceFullPath = $relativePath && Storage::disk('local')->exists($relativePath)
            ? Storage::disk('local')->path($relativePath)
            : null;

        $watermarkText = sprintf(
            'Exemplaire personnel de %s (%s) - Réf: %s - Diffusion interdite',
            $download->customer_name,
            $download->customer_email,
            $download->order->order_number ?? 'EDM'
        );

        $filename = Str::slug($book->title).'-edition-securisee.pdf';

        return response()->stream(function () use ($sourceFullPath, $book, $watermarkText) {
            $pdf = new Fpdi;

            if ($sourceFullPath && file_exists($sourceFullPath)) {
                $pageCount = $pdf->setSourceFile($sourceFullPath);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    // Filigrane discret en pied de page
                    $pdf->SetFont('Helvetica', 'I', 8);
                    $pdf->SetTextColor(130, 130, 130);
                    $pdf->SetXY(10, $size['height'] - 10);
                    $pdf->Cell(0, 5, utf8_decode($watermarkText), 0, 0, 'C');

                    // En-tête haut de page
                    $pdf->SetXY(10, 5);
                    $pdf->SetFont('Helvetica', '', 7);
                    $pdf->SetTextColor(160, 160, 160);
                    $pdf->Cell(0, 5, utf8_decode('Librairie NSOROMMA — Édition numérique certifiée'), 0, 0, 'R');
                }
            } else {
                // Génération d'un document complet représentatif
                $pdf->AddPage('P', 'A4');
                $pdf->SetFont('Helvetica', 'B', 20);
                $pdf->SetTextColor(30, 41, 59);
                $pdf->Cell(0, 20, utf8_decode($book->title), 0, 1, 'C');

                $pdf->SetFont('Helvetica', 'I', 13);
                $pdf->SetTextColor(100, 116, 139);
                $pdf->Cell(0, 10, utf8_decode('Par '.$book->author), 0, 1, 'C');
                $pdf->Ln(15);

                $pdf->SetFont('Helvetica', 'B', 12);
                $pdf->SetTextColor(15, 23, 42);
                $pdf->Cell(0, 10, utf8_decode("Notice d'authenticité et de protection numérique"), 0, 1, 'L');

                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetTextColor(51, 65, 85);
                $notice = "Cet ouvrage numérique vous a été délivré par la librairie en ligne « NSOROMMA ».\n\n"
                    ."Résumé de l'œuvre :\n".$book->description."\n\n"
                    ."Extrait sélectionné :\n".($book->excerpt ?: 'Consultez les écrits et sagesses ancestrales dans cette édition originale.')."\n\n"
                    .'Rappel légal : Conformément au Code de la propriété intellectuelle et aux conditions générales de vente, '
                    .'cet exemplaire est concédé pour un usage strictement individuel et privé. Toute reproduction, partage public, '
                    .'téléversement sur des réseaux ou revente est rigoureusement prohibé et passible de poursuites.';
                $pdf->MultiCell(0, 6, utf8_decode($notice));

                // Pied de page sécurisé
                $pdf->SetFont('Helvetica', 'I', 8);
                $pdf->SetTextColor(130, 130, 130);
                $pdf->SetXY(10, 280);
                $pdf->Cell(0, 5, utf8_decode($watermarkText), 0, 0, 'C');
            }

            echo $pdf->Output('S');
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Génère et diffuse un extrait / aperçu PDF limité (3 premières pages ou document de présentation).
     */
    public function streamPreviewPdf(Book $book): StreamedResponse
    {
        $relativePath = $book->pdf_path;
        $sourceFullPath = $relativePath && Storage::disk('local')->exists($relativePath)
            ? Storage::disk('local')->path($relativePath)
            : null;

        $watermarkText = 'EXTRAIT SPÉCIMEN — APERÇU LIMITÉ — NSOROMMA';
        $filename = 'extrait-'.Str::slug($book->title).'.pdf';

        return response()->stream(function () use ($sourceFullPath, $book, $watermarkText) {
            $pdf = new Fpdi;

            if ($sourceFullPath && file_exists($sourceFullPath)) {
                $totalAvailable = $pdf->setSourceFile($sourceFullPath);
                // Aperçu limité aux 3 premières pages maximum
                $pageLimit = min($totalAvailable, 3);

                for ($pageNo = 1; $pageNo <= $pageLimit; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    // Bandeau filigrane haut et bas
                    $pdf->SetFont('Helvetica', 'B', 8);
                    $pdf->SetTextColor(180, 80, 50);
                    $pdf->SetXY(10, 5);
                    $pdf->Cell(0, 5, utf8_decode('EXTRAIT DE CONSULTATION GRATUITE — ÉDITION LIMITÉE'), 0, 0, 'C');

                    $pdf->SetXY(10, $size['height'] - 10);
                    $pdf->SetFont('Helvetica', 'I', 8);
                    $pdf->SetTextColor(120, 120, 120);
                    $pdf->Cell(0, 5, utf8_decode($watermarkText.' — letoiledumatin.ci'), 0, 0, 'C');
                }
            } else {
                // Page 1 : Couverture & Informations
                $pdf->AddPage('P', 'A4');
                $pdf->SetFillColor(244, 235, 221);
                $pdf->Rect(0, 0, 210, 297, 'F');

                $pdf->SetFont('Helvetica', 'B', 11);
                $pdf->SetTextColor(190, 140, 50);
                $pdf->SetXY(20, 25);
                $pdf->Cell(0, 8, utf8_decode('LIBRAIRIE ÉDITIONS NSOROMMA'), 0, 1, 'C');

                $pdf->SetFont('Helvetica', 'B', 20);
                $pdf->SetTextColor(30, 29, 27);
                $pdf->SetXY(20, 45);
                $pdf->MultiCell(170, 11, utf8_decode($book->title), 0, 'C');

                $pdf->Ln(5);
                $pdf->SetFont('Helvetica', 'I', 13);
                $pdf->SetTextColor(120, 107, 90);
                $pdf->Cell(0, 10, utf8_decode('Par '.$book->author), 0, 1, 'C');

                if ($book->category) {
                    $pdf->SetFont('Helvetica', '', 10);
                    $pdf->SetTextColor(139, 133, 124);
                    $pdf->Cell(0, 8, utf8_decode('Collection : '.$book->category->name), 0, 1, 'C');
                }

                $pdf->Ln(15);
                $pdf->SetFillColor(255, 253, 249);
                $pdf->Rect(20, 110, 170, 65, 'F');
                $pdf->SetXY(25, 116);
                $pdf->SetFont('Helvetica', 'B', 12);
                $pdf->SetTextColor(25, 25, 25);
                $pdf->Cell(0, 8, utf8_decode("Notice de l'Aperçu"), 0, 1, 'L');
                $pdf->SetXY(25, 126);
                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetTextColor(60, 60, 60);
                $noticeText = "Vous consultez l'aperçu gratuit et limité de cet ouvrage.\n\n"
                    ."Cet extrait vous permet de découvrir les premières lignes et la démarche de l'auteur. "
                    ."L'intégralité de l'œuvre est disponible en téléchargement immédiat au format PDF ou en livraison physique reliée sur notre librairie en ligne.";
                $pdf->MultiCell(160, 6, utf8_decode($noticeText));

                // Page 2 : Résumé & Extrait
                $pdf->AddPage('P', 'A4');
                $pdf->SetFont('Helvetica', 'B', 14);
                $pdf->SetTextColor(30, 29, 27);
                $pdf->Cell(0, 12, utf8_decode("Résumé de l'œuvre"), 0, 1, 'L');

                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetTextColor(50, 50, 50);
                $pdf->MultiCell(0, 6, utf8_decode($book->description));

                $pdf->Ln(10);
                $pdf->SetFont('Helvetica', 'B', 14);
                $pdf->SetTextColor(30, 29, 27);
                $pdf->Cell(0, 12, utf8_decode('Extrait choisi'), 0, 1, 'L');

                $pdf->SetFont('Helvetica', 'I', 11);
                $pdf->SetTextColor(60, 45, 25);
                $excerpt = $book->excerpt ?: "L'auteur nous plonge ici au cœur d'une réflexion singulière...";
                $pdf->MultiCell(0, 7, utf8_decode($excerpt));

                // Bas de page
                $pdf->SetXY(10, 280);
                $pdf->SetFont('Helvetica', 'I', 8);
                $pdf->SetTextColor(130, 130, 130);
                $pdf->Cell(0, 5, utf8_decode($watermarkText.' — www.letoiledumatin.ci'), 0, 0, 'C');
            }

            echo $pdf->Output('S');
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
