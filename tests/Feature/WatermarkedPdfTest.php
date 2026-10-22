<?php

namespace Tests\Feature;

use App\Models\PurchasedDownload;
use App\Services\PdfProtectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class WatermarkedPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_every_page_of_the_purchased_book_carries_the_buyer_reference(): void
    {
        $download = $this->pdfDownload();
        $download->book->update(['pdf_path' => 'books/livre-test.pdf']);
        Storage::disk('local')->put('books/livre-test.pdf', $this->sourcePdf(pages: 5));

        $pages = $this->renderedPages($download);

        $this->assertCount(5, $pages);

        foreach ($pages as $index => $content) {
            $this->assertStringContainsString($download->order->order_number, $content, 'Référence absente de la page '.($index + 1));
            $this->assertStringContainsString($download->customer_email, $content);
            $this->assertStringContainsString('Page '.($index + 1), $content);
            $this->assertStringContainsString("NSOROMMA \x97 \xC9dition num\xE9rique certifi\xE9e", $content);
        }
    }

    public function test_generated_edition_repeats_the_reference_on_every_page(): void
    {
        $download = $this->pdfDownload();
        $download->book->update(['pdf_path' => null, 'description' => str_repeat('Une longue présentation du livre. ', 400)]);

        $pages = $this->renderedPages($download);

        $this->assertGreaterThan(1, count($pages));

        foreach ($pages as $content) {
            $this->assertStringContainsString($download->order->order_number, $content);
        }
    }

    private function pdfDownload(): PurchasedDownload
    {
        return PurchasedDownload::with('book', 'order')->where('format', 'pdf')->firstOrFail();
    }

    private function sourcePdf(int $pages): string
    {
        $pdf = new Fpdi;
        $pdf->SetFont('Helvetica', '', 12);

        for ($page = 1; $page <= $pages; $page++) {
            $pdf->AddPage();
            $pdf->Cell(0, 10, 'Chapitre '.$page);
        }

        return $pdf->Output('S');
    }

    /**
     * Contenu (décompressé) du flux de chaque page du PDF délivré à l'acheteur.
     *
     * @return list<string>
     */
    private function renderedPages(PurchasedDownload $download): array
    {
        $response = app(PdfProtectionService::class)->streamWatermarkedPdf($download);

        ob_start();
        $response->sendContent();
        $pdf = (string) ob_get_clean();

        $this->assertStringStartsWith('%PDF', $pdf);

        // FPDF numérote les objets de page 3, 5, 7… et place leur contenu dans l'objet suivant
        preg_match_all('/(\d+) 0 obj\s*<<\/Type \/Page\b.*?\/Contents (\d+) 0 R/s', $pdf, $pageObjects, PREG_SET_ORDER);

        return array_map(function (array $pageObject) use ($pdf): string {
            preg_match('/\b'.$pageObject[2].' 0 obj\s*<<(.*?)>>\s*stream\r?\n(.*?)\r?\nendstream/s', $pdf, $stream);

            return str_contains($stream[1], 'FlateDecode') ? (string) gzuncompress($stream[2]) : $stream[2];
        }, $pageObjects);
    }
}
