<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

/**
 * Document PDF dont chaque page porte l'identité de l'acheteur en en-tête et en pied de page.
 *
 * FPDF appelle Header() et Footer() à chaque page, y compris celles créées par un saut de page automatique,
 * et sans déclencher de nouveau saut : les mentions ne peuvent donc ni manquer ni produire de page blanche.
 */
class WatermarkedPdf extends Fpdi
{
    public function __construct(
        private string $headerText,
        private string $footerText,
    ) {
        parent::__construct();
    }

    public function Header(): void
    {
        $this->SetFont('Helvetica', '', 7);
        $this->SetTextColor(150, 150, 150);
        $this->SetXY(10, 5);
        $this->Cell($this->GetPageWidth() - 20, 5, $this->encode($this->headerText), 0, 0, 'R');
        $this->SetXY($this->lMargin, $this->tMargin);
    }

    public function Footer(): void
    {
        $this->SetFont('Helvetica', 'I', 7.5);
        $this->SetTextColor(120, 120, 120);
        $this->SetXY(10, $this->GetPageHeight() - 10);
        $this->Cell($this->GetPageWidth() - 20, 5, $this->encode($this->footerText.' - Page '.$this->PageNo()), 0, 0, 'C');
    }

    /**
     * Les polices intégrées de FPDF attendent du Windows-1252 (accents, guillemets, tirets, œ).
     */
    public function encode(string $text): string
    {
        return (string) iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
    }
}
