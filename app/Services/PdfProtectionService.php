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
    /** Palette NSOROMMA (RVB) pour la mise en page des extraits */
    private const INK = [30, 34, 48];

    private const INK_SOFT = [95, 94, 90];

    private const MUTED = [154, 151, 143];

    private const CREAM = [250, 249, 245];

    private const LINE = [215, 213, 205];

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
            'Exemplaire personnel de %s (%s) - Réf : %s - Diffusion interdite',
            $download->customer_name,
            $download->customer_email,
            $download->order->order_number ?? 'EDM'
        );

        $filename = Str::slug($book->title).'-edition-securisee.pdf';

        return response()->stream(function () use ($sourceFullPath, $book, $watermarkText) {
            $pdf = new WatermarkedPdf('Librairie NSOROMMA — Édition numérique certifiée', $watermarkText);
            $pdf->SetTitle($pdf->encode($book->title));
            $pdf->SetAuthor($pdf->encode($book->author));
            $pdf->SetCreator('NSOROMMA');

            if ($sourceFullPath && file_exists($sourceFullPath)) {
                // Les pages importées occupent toute la feuille : aucun saut automatique ne doit s'ajouter
                $pdf->SetAutoPageBreak(false);
                $pageCount = $pdf->setSourceFile($sourceFullPath);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            } else {
                // Génération d'un document complet représentatif ; les pages suivantes reçoivent aussi les mentions
                $pdf->SetAutoPageBreak(true, 20);
                $pdf->AddPage('P', 'A4');
                $pdf->SetFont('Helvetica', 'B', 20);
                $pdf->SetTextColor(30, 34, 48);
                $pdf->MultiCell(0, 10, $pdf->encode($book->title), 0, 'C');
                $pdf->Ln(2);

                $pdf->SetFont('Helvetica', 'I', 13);
                $pdf->SetTextColor(95, 94, 90);
                $pdf->Cell(0, 10, $pdf->encode('Par '.$book->author), 0, 1, 'C');
                $pdf->Ln(15);

                $pdf->SetFont('Helvetica', 'B', 12);
                $pdf->SetTextColor(30, 34, 48);
                $pdf->Cell(0, 10, $pdf->encode("Notice d'authenticité et de protection numérique"), 0, 1, 'L');

                $pdf->SetFont('Helvetica', '', 10);
                $pdf->SetTextColor(51, 51, 51);
                $notice = "Cet ouvrage numérique vous a été délivré par la librairie en ligne « NSOROMMA ».\n\n"
                    ."Résumé de l'œuvre :\n".$book->description."\n\n"
                    ."Extrait sélectionné :\n".($book->excerpt ?: 'Consultez les écrits et sagesses ancestrales dans cette édition originale.')."\n\n"
                    .'Rappel légal : Conformément au Code de la propriété intellectuelle et aux conditions générales de vente, '
                    .'cet exemplaire est concédé pour un usage strictement individuel et privé. Toute reproduction, partage public, '
                    .'téléversement sur des réseaux ou revente est rigoureusement prohibé et passible de poursuites.';
                $pdf->MultiCell(0, 6, $pdf->encode($notice));
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
     * Extrait gratuit : les 3 premières pages du PDF, ou une plaquette générée si le fichier est absent.
     */
    public function streamPreviewPdf(Book $book): StreamedResponse
    {
        $relativePath = $book->pdf_path;
        $sourceFullPath = $relativePath && Storage::disk('local')->exists($relativePath)
            ? Storage::disk('local')->path($relativePath)
            : null;

        $filename = 'extrait-'.Str::slug($book->title).'.pdf';

        return response()->stream(function () use ($sourceFullPath, $book) {
            $pdf = new Fpdi;
            $pdf->SetAutoPageBreak(false);
            $pdf->SetTitle($this->latin('Extrait — '.$book->title));
            $pdf->SetAuthor($this->latin($book->author));
            $pdf->SetCreator('NSOROMMA');

            if ($sourceFullPath && file_exists($sourceFullPath)) {
                $pageLimit = min($pdf->setSourceFile($sourceFullPath), 3);

                for ($pageNo = 1; $pageNo <= $pageLimit; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                    $this->drawPreviewFooter($pdf, $size['width'], $size['height'], $pageNo, $pageLimit);
                }

                $this->drawPreviewClosingPage($pdf, $book);
            } else {
                $this->drawPreviewCoverPage($pdf, $book);
                $this->drawPreviewSummaryPage($pdf, $book);
                $this->drawPreviewClosingPage($pdf, $book);
            }

            echo $pdf->Output('S');
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Couverture de la plaquette : fond crème, titre en serif, filets fins.
     */
    protected function drawPreviewCoverPage(Fpdi $pdf, Book $book): void
    {
        $pdf->AddPage('P', 'A4');
        $this->fill($pdf, self::CREAM);
        $pdf->Rect(0, 0, 210, 297, 'F');

        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, self::INK);
        $pdf->SetXY(0, 30);
        $pdf->Cell(210, 6, 'N S O R O M M A', 0, 1, 'C');
        $this->rule($pdf, 95, 40, 20);

        $pdf->SetFont('Helvetica', '', 8);
        $this->text($pdf, self::MUTED);
        $pdf->SetXY(0, 78);
        $pdf->Cell(210, 5, 'EXTRAIT GRATUIT', 0, 1, 'C');

        $pdf->SetFont('Times', 'B', 30);
        $this->text($pdf, self::INK);
        $pdf->SetXY(25, 92);
        $pdf->MultiCell(160, 13, $this->latin($book->title), 0, 'C');

        $pdf->Ln(6);
        $pdf->SetFont('Times', 'I', 16);
        $this->text($pdf, self::INK_SOFT);
        $pdf->Cell(0, 9, $this->latin($book->author), 0, 1, 'C');

        if ($book->category) {
            $pdf->Ln(4);
            $pdf->SetFont('Helvetica', '', 8);
            $this->text($pdf, self::MUTED);
            $pdf->Cell(0, 6, $this->latin(mb_strtoupper($book->category->name)), 0, 1, 'C');
        }

        $this->rule($pdf, 25, 250, 160);
        $pdf->SetFont('Helvetica', '', 8.5);
        $this->text($pdf, self::INK_SOFT);
        $pdf->SetXY(25, 256);
        $pdf->MultiCell(160, 5, $this->latin("Vous lisez un extrait gratuit de ce livre. L'ouvrage complet est disponible en e-book PDF, en livre papier ou en livre audio sur ".$this->siteHost().'.'), 0, 'C');
    }

    /**
     * Résumé et passage choisi, mis en page comme une page de livre.
     */
    protected function drawPreviewSummaryPage(Fpdi $pdf, Book $book): void
    {
        $pdf->AddPage('P', 'A4');

        $pdf->SetFont('Helvetica', '', 8);
        $this->text($pdf, self::MUTED);
        $pdf->SetXY(25, 30);
        $pdf->Cell(0, 5, $this->latin('À PROPOS DU LIVRE'), 0, 1);

        $pdf->SetFont('Times', 'B', 22);
        $this->text($pdf, self::INK);
        $pdf->SetX(25);
        $pdf->Cell(0, 12, $this->latin('Résumé'), 0, 1);

        $pdf->Ln(3);
        $pdf->SetFont('Times', '', 12);
        $this->text($pdf, self::INK_SOFT);
        $pdf->SetX(25);
        $pdf->MultiCell(160, 6.5, $this->latin(Str::limit(strip_tags((string) $book->description), 1400)));

        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', '', 8);
        $this->text($pdf, self::MUTED);
        $pdf->SetX(25);
        $pdf->Cell(0, 5, 'EXTRAIT', 0, 1);
        $pdf->Ln(3);

        $excerptTop = $pdf->GetY();
        $pdf->SetFont('Times', 'I', 13);
        $this->text($pdf, self::INK);
        $pdf->SetX(32);
        $pdf->MultiCell(153, 7.5, $this->latin(Str::limit(strip_tags((string) ($book->excerpt ?: "L'auteur nous plonge ici au cœur d'une réflexion singulière…")), 1200)));
        $this->draw($pdf, self::INK);
        $pdf->SetLineWidth(0.6);
        $pdf->Line(25, $excerptTop + 1, 25, $pdf->GetY() - 1);

        $this->drawPreviewFooter($pdf, 210, 297, 2, 3);
    }

    /**
     * Dernière page : invitation à lire la suite, avec lien vers la fiche du livre.
     */
    protected function drawPreviewClosingPage(Fpdi $pdf, Book $book): void
    {
        $pdf->AddPage('P', 'A4');
        $this->fill($pdf, self::INK);
        $pdf->Rect(0, 0, 210, 297, 'F');

        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, self::CREAM);
        $pdf->SetXY(0, 30);
        $pdf->Cell(210, 6, 'N S O R O M M A', 0, 1, 'C');

        $pdf->SetFont('Times', 'I', 30);
        $pdf->SetXY(25, 118);
        $pdf->MultiCell(160, 13, 'Envie de lire la suite ?', 0, 'C');

        $pdf->Ln(6);
        $pdf->SetFont('Helvetica', '', 10);
        $this->text($pdf, self::MUTED);
        $pdf->SetX(30);
        $pdf->MultiCell(150, 6, $this->latin('« '.$book->title.' » de '.$book->author.' vous attend en intégralité.'), 0, 'C');

        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', 'B', 11);
        $this->text($pdf, self::CREAM);
        $pdf->SetX(0);
        $pdf->Cell(210, 8, $this->siteHost(), 0, 1, 'C', false, route('catalog.show', $book->slug));
    }

    /**
     * Pied de page discret : mention d'extrait et pagination.
     */
    protected function drawPreviewFooter(Fpdi $pdf, float $width, float $height, int $pageNo, int $pageCount): void
    {
        $this->draw($pdf, self::LINE);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, $height - 14, $width - 15, $height - 14);

        $pdf->SetFont('Helvetica', '', 7);
        $this->text($pdf, self::MUTED);
        $pdf->SetXY(15, $height - 12);
        $pdf->Cell(($width - 30) / 2, 5, $this->latin('Extrait gratuit · NSOROMMA · '.$this->siteHost()), 0, 0, 'L');
        $pdf->Cell(($width - 30) / 2, 5, $pageNo.' / '.$pageCount, 0, 0, 'R');
    }

    /**
     * @param  array{int, int, int}  $color
     */
    private function text(Fpdi $pdf, array $color): void
    {
        $pdf->SetTextColor(...$color);
    }

    /**
     * @param  array{int, int, int}  $color
     */
    private function fill(Fpdi $pdf, array $color): void
    {
        $pdf->SetFillColor(...$color);
    }

    /**
     * @param  array{int, int, int}  $color
     */
    private function draw(Fpdi $pdf, array $color): void
    {
        $pdf->SetDrawColor(...$color);
    }

    private function rule(Fpdi $pdf, float $x, float $y, float $length): void
    {
        $this->draw($pdf, self::INK);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($x, $y, $x + $length, $y);
    }

    /**
     * Les polices intégrées de FPDF attendent du Windows-1252 (accents, guillemets, tirets, œ).
     */
    private function latin(string $text): string
    {
        return (string) iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
    }

    private function siteHost(): string
    {
        return (string) parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'etoilematin.com';
    }
}
