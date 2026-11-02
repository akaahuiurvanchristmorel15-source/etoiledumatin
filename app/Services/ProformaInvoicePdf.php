<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use setasign\Fpdi\Fpdi;

/**
 * Facture proforma d'une commande (livres PDF, livres papier, abonnements audio).
 *
 * Mise en page volontairement sobre : encre sur fond blanc, filets fins, aucune case colorée.
 * Tous les blocs s'alignent sur la même marge de 20 mm ; un tableau long continue sur une nouvelle page.
 */
class ProformaInvoicePdf extends Fpdi
{
    private const INK = [30, 34, 48];

    private const SOFT = [74, 75, 82];

    private const MUTED = [107, 103, 94];

    private const LINE = [222, 220, 213];

    private const PAID = [6, 118, 71];

    private const MARGIN = 20.0;

    private const CONTENT_WIDTH = 170.0;

    private const TITLE_COLUMN = 98.0;

    private const QUANTITY_COLUMN = 14.0;

    private const PRICE_COLUMN = 29.0;

    private const TOTALS_WIDTH = 72.0;

    public function __construct(private Order $order)
    {
        parent::__construct('P', 'mm', 'A4');

        $this->SetMargins(self::MARGIN, self::MARGIN, self::MARGIN);
        $this->SetAutoPageBreak(false);
        $this->cMargin = 0;
        $this->AliasNbPages();
        $this->SetCreator('NSOROMMA');
        $this->SetTitle($this->encode('Facture proforma '.$this->number()));
    }

    /**
     * Numéro du document, dérivé de celui de la commande : EDM-20261102-A1B2C3 devient PF-20261102-A1B2C3.
     */
    public function number(): string
    {
        return preg_replace('/^[A-Z]+-/', 'PF-', $this->order->order_number);
    }

    public function render(): string
    {
        $this->AddPage();
        $this->drawMasthead();
        $this->drawReferences();
        $this->drawParties();
        $this->drawItems();
        $this->drawTotals();
        $this->drawNote();

        return $this->Output('S');
    }

    /**
     * Rappel discret du document sur les pages suivantes.
     */
    public function Header(): void
    {
        if ($this->PageNo() > 1) {
            $this->setFontStyle('Helvetica', '', 8, self::MUTED);
            $this->SetXY(self::MARGIN, 12);
            $this->Cell(self::CONTENT_WIDTH, 4, $this->encode('Facture proforma '.$this->number()), 0, 0, 'R');
            $this->SetY(self::MARGIN + 2);
        }
    }

    /**
     * Mentions légales renseignées dans Paramètres › Contenus du site, et pagination.
     */
    public function Footer(): void
    {
        $legal = collect([
            Setting::content('legal_form'),
            Setting::content('legal_rccm') !== '' ? 'RCCM '.Setting::content('legal_rccm') : '',
            Setting::content('legal_tax_id') !== '' ? 'NCC '.Setting::content('legal_tax_id') : '',
        ])->filter()->implode('  ·  ');

        $this->drawRule(self::MARGIN, 279, self::CONTENT_WIDTH);
        $this->setFontStyle('Helvetica', '', 7.5, self::MUTED);
        $this->SetXY(self::MARGIN, 281);
        $this->Cell(self::CONTENT_WIDTH - 20, 4, $this->encode($legal));
        $this->Cell(20, 4, 'Page '.$this->PageNo().' / {nb}', 0, 0, 'R');
    }

    /**
     * Les polices intégrées de FPDF attendent du Windows-1252 ; un caractère hors de cette table est remplacé ou ignoré.
     */
    public function encode(string $text): string
    {
        return (string) iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
    }

    private function drawMasthead(): void
    {
        $logo = public_path('images/brand/nsoromma-mark.png');

        if (is_file($logo)) {
            $this->Image($logo, self::MARGIN, 19, 11, 11);
        }

        $this->setFontStyle('Times', '', 13, self::INK);
        $this->drawTrackedText(self::MARGIN + 14, 26.2, 'NSOROMMA', 0.9);

        $this->setFontStyle('Times', '', 24, self::INK);
        $this->SetXY(self::MARGIN, 18);
        $this->Cell(self::CONTENT_WIDTH, 12, $this->encode('Facture proforma'), 0, 0, 'R');

        $this->drawRule(self::MARGIN, 38, self::CONTENT_WIDTH);
    }

    /**
     * Une seule ligne de références : numéro, date, commande et état du paiement.
     */
    private function drawReferences(): void
    {
        $columns = [
            ['Numéro', $this->number()],
            ['Commande', $this->order->order_number],
            ['Date', $this->order->created_at->format('d/m/Y')],
            ['Paiement', $this->paymentStatusLabel()],
        ];
        $widths = [59.0, 59.0, 26.0, 26.0];
        $x = self::MARGIN;

        foreach ($columns as $index => [$label, $value]) {
            $this->drawLabel($x, 45, $label);
            $isStatus = $index === 3;
            $this->setFontStyle('Helvetica', $isStatus ? 'B' : '', 9.5, $isStatus && $this->order->isPaid() ? self::PAID : self::INK);
            $this->SetXY($x, 47);
            $this->Cell($widths[$index], 5, $this->encode($value));
            $x += $widths[$index];
        }
    }

    /**
     * Vendeur, client et, pour un livre papier, adresse de livraison : trois colonnes égales.
     */
    private function drawParties(): void
    {
        $shipping = $this->order->shippingAddress;

        $blocks = array_filter([
            'Vendeur' => [
                Setting::content('legal_company_name'),
                Setting::content('legal_address'),
                Setting::content('contact_email'),
                Setting::content('contact_phone'),
            ],
            'Client' => [
                $this->order->customer_name,
                $this->order->customer_email,
                $this->order->customer_phone,
            ],
            'Livraison' => $shipping ? [
                $shipping->recipient_name,
                $shipping->address_line,
                collect([$shipping->city, $shipping->country])->filter()->implode(', '),
                $shipping->phone,
            ] : null,
        ]);

        $columnWidth = 52.0;
        $gutter = (self::CONTENT_WIDTH - 3 * $columnWidth) / 2;
        $x = self::MARGIN;
        $bottom = 0.0;

        foreach ($blocks as $title => $lines) {
            $this->drawLabel($x, 64, $title);
            $y = 66.0;

            foreach (array_values(array_filter($lines, fn (?string $line): bool => filled($line))) as $index => $line) {
                $this->setFontStyle('Helvetica', $index === 0 ? 'B' : '', 9, $index === 0 ? self::INK : self::SOFT);

                foreach ($this->wrap($line, $columnWidth) as $wrappedLine) {
                    $this->SetXY($x, $y);
                    $this->Cell($columnWidth, 4.6, $wrappedLine);
                    $y += 4.6;
                }
            }

            $bottom = max($bottom, $y);
            $x += $columnWidth + $gutter;
        }

        $this->SetY($bottom + 11);
    }

    private function drawItems(): void
    {
        $this->drawItemsHeading();

        foreach ($this->order->items as $item) {
            $this->drawItem($item);
        }
    }

    private function drawItemsHeading(): void
    {
        $y = $this->GetY();
        $x = self::MARGIN;

        $this->drawLabel($x, $y + 2, 'Désignation');
        $x += self::TITLE_COLUMN;
        $this->drawLabel($x, $y + 2, 'Qté', self::QUANTITY_COLUMN, 'C');
        $x += self::QUANTITY_COLUMN;
        $this->drawLabel($x, $y + 2, 'Prix unitaire', self::PRICE_COLUMN, 'R');
        $x += self::PRICE_COLUMN;
        $this->drawLabel($x, $y + 2, 'Total', self::PRICE_COLUMN, 'R');

        $this->drawRule(self::MARGIN, $y + 5, self::CONTENT_WIDTH, self::INK);
        $this->SetY($y + 5);
    }

    private function drawItem(OrderItem $item): void
    {
        $this->setFontStyle('Helvetica', 'B', 9.5, self::INK);
        $titleLines = $this->wrap($item->book_title, self::TITLE_COLUMN - 6);

        $this->setFontStyle('Helvetica', '', 8.5, self::MUTED);
        $detailLines = $this->wrap(collect([$item->book?->author, $item->formatLabel()])->filter()->implode(' · '), self::TITLE_COLUMN - 6);

        if ($item->isAudioSubscription() && filled($item->part?->title)) {
            $detailLines = [...$detailLines, ...$this->wrap($item->part->title, self::TITLE_COLUMN - 6)];
        }

        $rowHeight = 7 + count($titleLines) * 4.6 + count($detailLines) * 4.0;
        $this->ensureRoom($rowHeight, repeatHeading: true);

        $top = $this->GetY();
        $y = $top + 3.5;

        $this->setFontStyle('Helvetica', 'B', 9.5, self::INK);
        foreach ($titleLines as $line) {
            $this->SetXY(self::MARGIN, $y);
            $this->Cell(self::TITLE_COLUMN, 4.6, $line);
            $y += 4.6;
        }

        $this->setFontStyle('Helvetica', '', 8.5, self::MUTED);
        foreach ($detailLines as $line) {
            $this->SetXY(self::MARGIN, $y);
            $this->Cell(self::TITLE_COLUMN, 4.0, $line);
            $y += 4.0;
        }

        $this->setFontStyle('Helvetica', '', 9.5, self::INK);
        $this->SetXY(self::MARGIN + self::TITLE_COLUMN, $top + 3.5);
        $this->Cell(self::QUANTITY_COLUMN, 4.6, (string) $item->quantity, 0, 0, 'C');
        $this->Cell(self::PRICE_COLUMN, 4.6, $this->money($item->unit_price), 0, 0, 'R');
        $this->Cell(self::PRICE_COLUMN, 4.6, $this->money($item->subtotal), 0, 0, 'R');

        $this->drawRule(self::MARGIN, $top + $rowHeight, self::CONTENT_WIDTH);
        $this->SetY($top + $rowHeight);
    }

    /**
     * Total à droite ; à gauche, les références du règlement quand il a eu lieu.
     */
    private function drawTotals(): void
    {
        // Total et mention restent ensemble : la mention n'arrive jamais seule sur une page
        $this->ensureRoom(50);

        $top = $this->GetY() + 7;
        $y = $top;
        $x = self::MARGIN + self::CONTENT_WIDTH - self::TOTALS_WIDTH;

        // Sans livraison, le sous-total répéterait simplement le total
        if ($this->order->shipping_cost > 0) {
            $this->setFontStyle('Helvetica', '', 9.5, self::SOFT);
            $this->SetXY($x, $y);
            $this->Cell(self::TOTALS_WIDTH / 2, 5, 'Sous-total');
            $this->SetTextColor(...self::INK);
            $this->Cell(self::TOTALS_WIDTH / 2, 5, $this->money($this->order->total_amount - $this->order->shipping_cost), 0, 0, 'R');

            $y += 6;
            $this->SetTextColor(...self::SOFT);
            $this->SetXY($x, $y);
            $this->Cell(self::TOTALS_WIDTH / 2, 5, 'Livraison');
            $this->SetTextColor(...self::INK);
            $this->Cell(self::TOTALS_WIDTH / 2, 5, $this->money($this->order->shipping_cost), 0, 0, 'R');

            $y += 8;
        }

        $this->drawRule($x, $y, self::TOTALS_WIDTH, self::INK);

        $this->setFontStyle('Helvetica', 'B', 9.5, self::INK);
        $this->SetXY($x, $y + 3);
        $this->Cell(22, 8, 'Total');
        $this->setFontStyle('Times', '', 17, self::INK);
        $this->Cell(self::TOTALS_WIDTH - 22, 8, $this->money($this->order->total_amount).' FCFA', 0, 0, 'R');

        if ($this->order->isPaid()) {
            $this->drawLabel(self::MARGIN, $top + 2, 'Règlement');
            $this->setFontStyle('Helvetica', '', 9, self::SOFT);

            foreach (array_filter([
                $this->paymentMethodLabel(),
                $this->order->paid_at ? 'Reçu le '.$this->order->paid_at->format('d/m/Y') : null,
                $this->order->payment_reference ? 'Réf. '.$this->order->payment_reference : null,
            ]) as $index => $line) {
                $this->SetXY(self::MARGIN, $top + 4 + $index * 4.6);
                $this->Cell(self::CONTENT_WIDTH - self::TOTALS_WIDTH - 8, 4.6, $this->encode($line));
            }
        }

        $this->SetY(max($y + 15, $top + 22));
    }

    private function drawNote(): void
    {
        $this->setFontStyle('Helvetica', '', 8, self::MUTED);
        $y = $this->GetY() + 3;

        foreach ($this->wrap('Document informatif : cette facture proforma ne constitue pas une facture définitive. Montants en francs CFA (FCFA).', self::CONTENT_WIDTH) as $line) {
            $this->SetXY(self::MARGIN, $y);
            $this->Cell(self::CONTENT_WIDTH, 4, $line);
            $y += 4;
        }
    }

    private function paymentStatusLabel(): string
    {
        return match ($this->order->payment_status) {
            'paid' => 'Payée',
            'pending' => 'En attente',
            'refunded' => 'Remboursée',
            default => 'Non aboutie',
        };
    }

    /**
     * Moyen de paiement lisible : « paystack_sandbox (Wave) » devient « Wave », « paystack » devient « Paystack ».
     */
    private function paymentMethodLabel(): string
    {
        $method = (string) $this->order->payment_method;

        if (preg_match('/\((.+)\)/', $method, $matches)) {
            return $matches[1];
        }

        return ucfirst($method);
    }

    private function money(int $amount): string
    {
        return number_format($amount, 0, ',', ' ');
    }

    private function ensureRoom(float $height, bool $repeatHeading = false): void
    {
        if ($this->GetY() + $height > 272) {
            $this->AddPage();

            if ($repeatHeading) {
                $this->drawItemsHeading();
            }
        }
    }

    /**
     * Découpe un texte UTF-8 en lignes encodées tenant dans la largeur, avec la police courante.
     *
     * @return array<int, string>
     */
    private function wrap(string $text, float $width): array
    {
        $lines = [];
        $current = '';

        foreach (preg_split('/\s+/', trim($this->encode($text))) as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if ($this->GetStringWidth($candidate) <= $width) {
                $current = $candidate;

                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            // Un mot plus long que la colonne (adresse e-mail, référence) est coupé
            while ($this->GetStringWidth($word) > $width) {
                $length = strlen($word);

                while ($length > 1 && $this->GetStringWidth(substr($word, 0, $length)) > $width) {
                    $length--;
                }

                $lines[] = substr($word, 0, $length);
                $word = substr($word, $length);
            }

            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /**
     * Petit titre de bloc : capitales espacées, discrètes.
     */
    private function drawLabel(float $x, float $y, string $text, ?float $width = null, string $align = 'L'): void
    {
        $this->setFontStyle('Helvetica', 'B', 6.5, self::MUTED);
        $label = $this->encode(mb_strtoupper($text));
        $spacing = 0.35;
        $textWidth = $this->GetStringWidth($label) + strlen($label) * $spacing / $this->k;

        $left = match ($align) {
            'R' => $x + (float) $width - $textWidth,
            'C' => $x + ((float) $width - $textWidth) / 2,
            default => $x,
        };

        $this->drawTrackedText($left, $y, $label, $spacing, alreadyEncoded: true);
    }

    private function drawTrackedText(float $x, float $y, string $text, float $spacing, bool $alreadyEncoded = false): void
    {
        $this->_out(sprintf('%.3F Tc', $spacing));
        $this->Text($x, $y, $alreadyEncoded ? $text : $this->encode($text));
        $this->_out('0 Tc');
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $color
     */
    private function drawRule(float $x, float $y, float $width, array $color = self::LINE): void
    {
        $this->SetDrawColor(...$color);
        $this->SetLineWidth(0.2);
        $this->Line($x, $y, $x + $width, $y);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $color
     */
    private function setFontStyle(string $family, string $style, float $size, array $color): void
    {
        $this->SetFont($family, $style, $size);
        $this->SetTextColor(...$color);
    }
}
