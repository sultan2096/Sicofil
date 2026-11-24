<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class RapportFinancierExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new ResumePeriodeSheet($this->data),
            new RepartitionZoneSheet($this->data),
            new PerformanceCollecteursSheet($this->data),
            new StatistiquesSheet($this->data),
            new EvolutionRecettesSheet($this->data),
        ];
    }
}

class ResumePeriodeSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $collection = new Collection();
        
        // Informations générales
        $collection->push(['Période', $this->data['periode']['libelle']]);
        $collection->push(['Du', $this->data['periode']['debut']]);
        $collection->push(['Au', $this->data['periode']['fin']]);
        $collection->push(['Zone', $this->data['zone_selectionnee'] ? $this->data['zone_selectionnee']->nom : 'Toutes les zones']);
        $collection->push(['', '']); // Ligne vide
        
        // Résumé des recettes
        $collection->push(['RÉSUMÉ DES RECETTES', '']);
        $collection->push(['Recettes totales', number_format($this->data['recettes_periode']['total'], 0, ',', ' ') . ' FCFA']);
        $collection->push(['Nombre de paiements', number_format($this->data['recettes_periode']['nombre_paiements'])]);
        $collection->push(['Montant moyen', number_format($this->data['recettes_periode']['montant_moyen'], 0, ',', ' ') . ' FCFA']);
        $collection->push(['', '']); // Ligne vide
        
        // Répartition par taxe
        $collection->push(['RÉPARTITION PAR TYPE DE TAXE', '']);
        $collection->push(['Type de taxe', 'Montant collecté', 'Taux', '% du total']);
        
        foreach ($this->data['recettes_periode']['par_taxe'] as $taxe) {
            $pourcentage = $this->data['recettes_periode']['total'] > 0 
                ? ($taxe->total / $this->data['recettes_periode']['total']) * 100 
                : 0;
            
            $collection->push([
                $taxe->nom,
                number_format($taxe->total, 0, ',', ' ') . ' FCFA',
                $taxe->taux . '%',
                number_format($pourcentage, 1) . '%'
            ]);
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return ['Indicateur', 'Valeur'];
    }

    public function title(): string
    {
        return 'Résumé Période';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 25,
            'C' => 15,
            'D' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            6 => ['font' => ['bold' => true, 'color' => ['rgb' => '2E8B57']]],
            11 => ['font' => ['bold' => true, 'color' => ['rgb' => '2E8B57']]],
            12 => ['font' => ['bold' => true]],
        ];
    }
}

class RepartitionZoneSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $collection = new Collection();
        $totalZones = $this->data['recettes_zones']->sum('paiements_sum_montant');
        
        foreach ($this->data['recettes_zones'] as $zone) {
            $pourcentage = $totalZones > 0 
                ? (($zone->paiements_sum_montant ?? 0) / $totalZones) * 100 
                : 0;
            
            $performance = 'À améliorer';
            if ($pourcentage >= 30) {
                $performance = 'Excellent';
            } elseif ($pourcentage >= 15) {
                $performance = 'Bon';
            }
            
            $collection->push([
                $zone->nom,
                number_format($zone->paiements_sum_montant ?? 0, 0, ',', ' ') . ' FCFA',
                $zone->paiements_count ?? 0,
                number_format($pourcentage, 1) . '%',
                $performance
            ]);
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return ['Zone fiscale', 'Montant collecté', 'Nb paiements', '% du total', 'Performance'];
    }

    public function title(): string
    {
        return 'Répartition par Zone';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 15,
            'D' => 12,
            'E' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class PerformanceCollecteursSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $collection = new Collection();
        
        foreach ($this->data['performance_collecteurs'] as $index => $collecteur) {
            $moyenne = ($collecteur->paiements_collectes_count ?? 0) > 0 
                ? ($collecteur->paiements_collectes_sum_montant ?? 0) / $collecteur->paiements_collectes_count 
                : 0;
            
            $classement = $index + 1;
            if ($index == 0) $classement = '1er';
            elseif ($index == 1) $classement = '2ème';
            elseif ($index == 2) $classement = '3ème';
            else $classement = $classement . 'ème';
            
            $collection->push([
                $collecteur->name,
                $collecteur->zoneFiscale->nom ?? 'Non assignée',
                number_format($collecteur->paiements_collectes_sum_montant ?? 0, 0, ',', ' ') . ' FCFA',
                $collecteur->paiements_collectes_count ?? 0,
                number_format($moyenne, 0, ',', ' ') . ' FCFA',
                $classement
            ]);
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return ['Collecteur', 'Zone assignée', 'Montant collecté', 'Nb paiements', 'Moyenne par paiement', 'Classement'];
    }

    public function title(): string
    {
        return 'Performance Collecteurs';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 18,
            'D' => 15,
            'E' => 20,
            'F' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class StatistiquesSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $collection = new Collection();
        $stats = $this->data['statistiques'];
        
        $collection->push(['INDICATEURS DE PERFORMANCE', '']);
        $collection->push(['Taux de réussite des paiements', number_format($stats['taux_reussite'], 1) . '%']);
        $collection->push(['Paiements validés', $stats['paiements_valides']]);
        $collection->push(['Paiements annulés', $stats['paiements_annules']]);
        $collection->push(['Total des paiements', $stats['total_paiements']]);
        $collection->push(['', '']);
        
        $collection->push(['UNITÉS COMMERCIALES', '']);
        $collection->push(['Taux de participation', number_format($stats['taux_participation'], 1) . '%']);
        $collection->push(['Unités actives', $stats['unites_actives']]);
        $collection->push(['Total des unités', $stats['total_unites']]);
        
        return $collection;
    }

    public function headings(): array
    {
        return ['Indicateur', 'Valeur'];
    }

    public function title(): string
    {
        return 'Statistiques Détaillées';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '2E8B57']]],
            7 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '2E8B57']]],
        ];
    }
}

class EvolutionRecettesSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $collection = new Collection();
        
        foreach ($this->data['evolution_recettes'] as $periode) {
            $moyenne = $periode->nombre > 0 ? $periode->total / $periode->nombre : 0;
            
            $collection->push([
                $periode->periode,
                number_format($periode->total, 0, ',', ' ') . ' FCFA',
                $periode->nombre,
                number_format($moyenne, 0, ',', ' ') . ' FCFA'
            ]);
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return ['Période', 'Montant collecté', 'Nombre de paiements', 'Moyenne par paiement'];
    }

    public function title(): string
    {
        return 'Évolution Recettes';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 18,
            'C' => 18,
            'D' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
} 