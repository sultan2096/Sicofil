<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class FacturesAbonnementExport implements FromCollection, WithHeadings
{
    protected $factures;

    public function __construct($factures)
    {
        $this->factures = $factures;
    }

    public function headings(): array
    {
        return [
            'Commune',
            'Numéro facture',
            'Période',
            'Date émission',
            'Date échéance',
            'Montant fixe (FCFA)',
            'Montant variable (FCFA)',
            'Montant total (FCFA)',
            'Statut',
            'Date paiement',
            'Mode paiement',
        ];
    }

    public function collection(): Collection
    {
        return collect($this->factures)->map(function ($f) {
            return [
                $f->commune_nom ?? '',
                $f->numero_facture ?? '',
                ucfirst($f->periode ?? ''),
                $f->date_emission ? \Carbon\Carbon::parse($f->date_emission)->format('d/m/Y') : '',
                $f->date_echeance ? \Carbon\Carbon::parse($f->date_echeance)->format('d/m/Y') : '',
                number_format((float) ($f->montant_fixe ?? 0), 0, ',', ' '),
                number_format((float) ($f->montant_variable ?? 0), 0, ',', ' '),
                number_format((float) ($f->montant_total ?? 0), 0, ',', ' '),
                ucfirst(str_replace('_', ' ', $f->statut ?? '')),
                $f->date_paiement ? \Carbon\Carbon::parse($f->date_paiement)->format('d/m/Y') : '—',
                $f->mode_paiement ?? '—',
            ];
        });
    }
}
