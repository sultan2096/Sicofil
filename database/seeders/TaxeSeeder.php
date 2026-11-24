<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Taxe;
use App\Models\Commune;

class TaxeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sandiara = Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->first();
        $communeId = $sandiara?->id;

        // Taxes journalières
        $taxesJournalieres = [
            ['nom' => 'Mini car 10-14 places', 'activite_produit' => 'Mini car de 10 à 14 places', 'montant' => 300, 'nature' => 'produits_domaniaux'],
            ['nom' => 'Car 25 places et plus', 'activite_produit' => 'Car de 25 places et plus', 'montant' => 500, 'nature' => 'produits_domaniaux'],
            ['nom' => 'Camionnette', 'activite_produit' => 'Camionnette', 'montant' => 500, 'nature' => 'produits_domaniaux'],
            ['nom' => 'Camion gros-porteur', 'activite_produit' => 'Camion gros-porteur', 'montant' => 2000, 'nature' => 'produits_domaniaux'],
            ['nom' => 'Camion-citerne carburant', 'activite_produit' => 'Camion-citerne de carburant', 'montant' => 2000, 'nature' => 'produits_domaniaux'],
            ['nom' => 'Bus', 'activite_produit' => 'Bus', 'montant' => 1000, 'nature' => 'produits_domaniaux'],
            ['nom' => 'Taxe spectacles', 'activite_produit' => 'Taxe sur les spectacles', 'montant' => 5000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Véhicules publicitaires', 'activite_produit' => 'Taxe sur les véhicules publicitaires', 'montant' => 10000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Fourrière cheval/âne', 'activite_produit' => 'Fourrière cheval et âne', 'montant' => 3000, 'nature' => 'taxes_municipales', 'unite' => 'Tête/Jour'],
            ['nom' => 'Fourrière ovin/caprin', 'activite_produit' => 'Fourrière ovin et caprin', 'montant' => 2000, 'nature' => 'taxes_municipales', 'unite' => 'Tête/Jour'],
            ['nom' => 'Fourrière bovin', 'activite_produit' => 'Fourrière bovin', 'montant' => 3000, 'nature' => 'taxes_municipales', 'unite' => 'Tête/Jour'],
            ['nom' => 'Étals', 'activite_produit' => 'Étals', 'montant' => 200, 'nature' => 'taxes_municipales'],
            ['nom' => 'Calèche', 'activite_produit' => 'Calèche', 'montant' => 100, 'nature' => 'taxes_municipales'],
            ['nom' => 'Chèvre et mouton', 'activite_produit' => 'Chèvre et mouton', 'montant' => 100, 'nature' => 'taxes_municipales'],
        ];

        foreach ($taxesJournalieres as $taxe) {
            Taxe::create(array_merge($taxe, ['periodicite' => 'jour', 'commune_id' => $communeId]));
        }

        // Taxes mensuelles
        $taxesMensuelles = [
            ['nom' => 'Taxi clando', 'activite_produit' => 'Taxi clando', 'montant' => 3000, 'nature' => 'produits_domaniaux'],
            ['nom' => 'Distributeur carburant', 'activite_produit' => 'Distributeur de carburant', 'montant' => 15000, 'nature' => 'taxes_municipales', 'unite' => 'Pompe/Mois'],
            ['nom' => 'Restaurant gargote', 'activite_produit' => 'Restaurant gargote', 'montant' => 2000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Dibiterie', 'activite_produit' => 'Dibiterie', 'montant' => 2000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Cantine privée', 'activite_produit' => 'Cantine privée', 'montant' => 1000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Cantine publique', 'activite_produit' => 'Cantine publique', 'montant' => 2000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Moto Jakarta', 'activite_produit' => 'Moto Jakarta', 'montant' => 2000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Vache', 'activite_produit' => 'Vache', 'montant' => 3000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Cheval', 'activite_produit' => 'Cheval', 'montant' => 2000, 'nature' => 'taxes_municipales'],
        ];

        foreach ($taxesMensuelles as $taxe) {
            Taxe::create(array_merge($taxe, ['periodicite' => 'mois', 'commune_id' => $communeId]));
        }

        // Taxes annuelles
        $taxesAnnuelles = [
            ['nom' => 'Autorisation panneaux pub', 'activite_produit' => 'Autorisation d\'implantation panneaux publicitaires ou enseignes', 'montant' => 20000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Panneaux pub < 1m²', 'activite_produit' => 'Panneaux publicitaires et enseignes de moins 1m²', 'montant' => 35000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Panneaux pub 1-3m²', 'activite_produit' => 'Panneaux publicitaires et enseignes de 1m² à 3m²', 'montant' => 65000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Panneaux pub 3-5m²', 'activite_produit' => 'Panneaux publicitaires et enseignes de 3m² à 5m²', 'montant' => 200000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Panneaux pub > 5m²', 'activite_produit' => 'Panneaux publicitaires et enseignes de plus de 5m²', 'montant' => 300000, 'nature' => 'taxes_municipales'],
            ['nom' => 'Panneaux opérateurs télécom', 'activite_produit' => 'Panneaux publicitaires et enseignes YAS/ORANGE/EXPRESSO', 'montant' => 20000, 'nature' => 'taxes_municipales'],
        ];

        foreach ($taxesAnnuelles as $taxe) {
            Taxe::create(array_merge($taxe, ['periodicite' => 'an', 'commune_id' => $communeId]));
        }

        // Taxes non périodiques
        $taxesNonPeriodiques = [
            ['nom' => 'Actes état civil', 'activite_produit' => 'Extrait de naissance, actes mariage et décès', 'montant' => 300, 'nature' => 'produits_exploitation'],
            ['nom' => 'Autres actes administratifs', 'activite_produit' => 'Tout autre acte administratif d\'état civil', 'montant' => 300, 'nature' => 'produits_exploitation'],
        ];

        foreach ($taxesNonPeriodiques as $taxe) {
            Taxe::create(array_merge($taxe, ['periodicite' => 'non_periodique', 'commune_id' => $communeId]));
        }
    }
} 