<?php

namespace Tests\Feature;

use App\Models\PurchasedDownload;
use App\Services\PdfProtectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfProtectionAndDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_unsigned_pdf_download_url_is_forbidden(): void
    {
        $download = PurchasedDownload::first();

        // Tentative d'accès sans signature Laravel
        $response = $this->get('/telechargement/securise/'.$download->download_token);

        $response->assertStatus(403);
    }

    public function test_valid_signed_url_streams_watermarked_pdf_and_updates_quota(): void
    {
        $download = PurchasedDownload::first();
        $initialCount = $download->download_count;

        $pdfService = app(PdfProtectionService::class);
        $signedUrl = $pdfService->generateSignedDownloadUrl($download, 10);

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        $download->refresh();
        $this->assertEquals($initialCount + 1, $download->download_count);
        $this->assertNotNull($download->last_downloaded_at);

        // Vérifier le journal d'audit
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'pdf_downloaded',
        ]);
    }

    public function test_valid_signed_url_streams_audio_and_updates_quota(): void
    {
        $download = PurchasedDownload::where('format', 'audio')->first();
        $this->assertNotNull($download);
        $initialCount = $download->download_count;

        $pdfService = app(PdfProtectionService::class);
        $signedUrl = $pdfService->generateSignedDownloadUrl($download, 10);

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'audio/mpeg');

        $download->refresh();
        $this->assertEquals($initialCount + 1, $download->download_count);
        $this->assertNotNull($download->last_downloaded_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'audio_downloaded',
        ]);
    }

    public function test_download_quota_exhaustion_is_blocked(): void
    {
        $download = PurchasedDownload::first();
        $download->update([
            'download_count' => 15,
            'max_downloads' => 15,
        ]);

        $pdfService = app(PdfProtectionService::class);
        $signedUrl = $pdfService->generateSignedDownloadUrl($download, 10);

        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    }
}
