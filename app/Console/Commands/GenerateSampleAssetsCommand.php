<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSampleAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sample-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les couvertures graphiques et les fichiers PDF modèles pour le catalogue.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Génération des assets de démonstration...');

        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');

        $publicDisk->makeDirectory('covers');
        $privateDisk->makeDirectory('books');

        $books = Book::all();

        $palettes = [
            ['bg' => [15, 23, 42], 'accent' => [197, 160, 89], 'text' => [255, 255, 255]], // Indigo & Or
            ['bg' => [67, 30, 15], 'accent' => [225, 175, 105], 'text' => [255, 250, 240]], // Terre Sienne & Ambre
            ['bg' => [19, 42, 31], 'accent' => [180, 205, 130], 'text' => [245, 255, 245]], // Émeraude & Or végétal
            ['bg' => [48, 16, 32], 'accent' => [235, 190, 135], 'text' => [255, 245, 250]], // Pourpre impérial & Or
        ];

        foreach ($books as $index => $book) {
            // 1. Couverture Image
            $palette = $palettes[$index % count($palettes)];
            $width = 400;
            $height = 600;
            $img = imagecreatetruecolor($width, $height);

            $bg = imagecolorallocate($img, $palette['bg'][0], $palette['bg'][1], $palette['bg'][2]);
            $accent = imagecolorallocate($img, $palette['accent'][0], $palette['accent'][1], $palette['accent'][2]);
            $white = imagecolorallocate($img, $palette['text'][0], $palette['text'][1], $palette['text'][2]);
            $subtle = imagecolorallocate($img, 180, 180, 180);

            imagefill($img, 0, 0, $bg);

            // Double cadre doré
            imagesetthickness($img, 3);
            imagerectangle($img, 18, 18, $width - 18, $height - 18, $accent);
            imagesetthickness($img, 1);
            imagerectangle($img, 24, 24, $width - 24, $height - 24, $accent);

            // Collection
            imagestring($img, 3, 50, 50, 'COLLECTION NSOROMMA', $accent);

            // Auteur
            imagestring($img, 5, 45, 120, strtoupper($book->author), $accent);

            // Titre avec découpage
            $words = explode(' ', $book->title);
            $line = '';
            $y = 200;
            foreach ($words as $word) {
                if (strlen($line.' '.$word) > 23) {
                    imagestring($img, 5, 45, $y, $line, $white);
                    $y += 28;
                    $line = $word;
                } else {
                    $line = trim($line.' '.$word);
                }
            }
            if (! empty($line)) {
                imagestring($img, 5, 45, $y, $line, $white);
            }

            // Bas de page
            imageline($img, 45, 510, $width - 45, 510, $accent);
            imagestring($img, 4, 90, 530, 'LIBRAIRIE & EDITIONS', $subtle);

            $coverRelPath = 'covers/'.$book->slug.'.jpg';
            $coverFullPath = storage_path('app/public/'.$coverRelPath);
            imagejpeg($img, $coverFullPath, 90);
            imagedestroy($img);

            $book->cover_image = 'storage/'.$coverRelPath;

            // 2. Fichier PDF source dans storage privé
            $pdfRelPath = 'books/'.$book->slug.'.pdf';
            $pdfFullPath = storage_path('app/private/'.$pdfRelPath);

            $fpdf = new \FPDF;
            $fpdf->AddPage();
            $fpdf->SetFont('Helvetica', 'B', 18);
            $fpdf->Cell(0, 15, utf8_decode($book->title), 0, 1, 'C');
            $fpdf->SetFont('Helvetica', 'I', 12);
            $fpdf->Cell(0, 10, utf8_decode('Auteur : '.$book->author), 0, 1, 'C');
            $fpdf->Ln(10);

            $fpdf->SetFont('Helvetica', 'B', 14);
            $fpdf->Cell(0, 10, utf8_decode('Présentation générale de l\'ouvrage'), 0, 1, 'L');
            $fpdf->SetFont('Helvetica', '', 11);
            $fpdf->MultiCell(0, 7, utf8_decode($book->description));
            $fpdf->Ln(10);

            $fpdf->SetFont('Helvetica', 'B', 14);
            $fpdf->Cell(0, 10, utf8_decode('Extrait des enseignements'), 0, 1, 'L');
            $fpdf->SetFont('Helvetica', 'I', 11);
            $fpdf->MultiCell(0, 7, utf8_decode($book->excerpt ?: 'La connaissance transmise par les anciens est le véritable flambeau de la libération de l\'homme.'));
            $fpdf->Ln(10);

            $fpdf->SetFont('Helvetica', '', 9);
            $fpdf->MultiCell(0, 6, utf8_decode('Tous droits réservés © NSOROMMA — Diffusion autorisée uniquement sous licence personnelle certifiée.'));
            $fpdf->Output('F', $pdfFullPath);

            $book->pdf_path = $pdfRelPath;
            $book->save();

            $this->line("  ✓ {$book->title} : Couverture & PDF générés.");
        }

        $this->info('Tous les assets ont été générés avec succès !');

        return self::SUCCESS;
    }
}
