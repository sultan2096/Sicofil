<?php

namespace App\Console\Commands;

use App\Models\Commune;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\ZoneFiscale;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Synchronise commune_id et lance le seeder démo
 */
class SeedDemoData extends Command
{
    protected $signature = 'sicofil:seed-demo {--fresh : Réexécuter migrate:fresh --seed}';

    protected $description = 'Synchronise commune_id sur les données existantes et ajoute les données démo SICOFIL';

    public function handle(): int
    {
        $sandiara = Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->first();
        if (!$sandiara) {
            $this->error('Commune Sandiara introuvable. Lancez les migrations.');
            return self::FAILURE;
        }

        // Sync zones sans commune_id
        $zonesUpdated = ZoneFiscale::whereNull('commune_id')->update(['commune_id' => $sandiara->id]);
        if ($zonesUpdated > 0) {
            $this->info("{$zonesUpdated} zone(s) mise(s) à jour avec commune_id.");
        }

        // Sync unités (via zone)
        $unites = UniteCommerciale::whereNull('commune_id')->with('zone')->get();
        foreach ($unites as $u) {
            $u->commune_id = $u->zone?->commune_id ?? $sandiara->id;
            $u->save();
        }
        if ($unites->count() > 0) {
            $this->info("{$unites->count()} unité(s) commerciale(s) mise(s) à jour.");
        }

        // Sync paiements (via unité)
        $paiements = Paiement::whereNull('commune_id')->with('unite')->get();
        foreach ($paiements as $p) {
            $p->commune_id = $p->unite?->commune_id ?? $sandiara->id;
            $p->save();
        }
        if ($paiements->count() > 0) {
            $this->info("{$paiements->count()} paiement(s) mis à jour.");
        }

        $this->call('db:seed', ['--class' => \Database\Seeders\DemoSicofilSeeder::class]);

        // Ajouter des paiements du mois en cours si nécessaire (visibles sur les dashboards)
        $countMois = Paiement::whereMonth('date_paiement', now()->month)
            ->whereYear('date_paiement', now()->year)
            ->where('statut', 'valide')
            ->count();
        if ($countMois < 3) {
            $unites = UniteCommerciale::where('commune_id', $sandiara->id)->limit(5)->get();
            $collecteurs = User::where('role', 'collecteur')->get();
            if ($unites->count() > 0 && $collecteurs->count() > 0) {
                $montants = [5000, 10000, 15000, 20000, 25000];
                foreach (array_slice($montants, 0, 5 - $countMois) as $i => $m) {
                    Paiement::create([
                        'commune_id' => $sandiara->id,
                        'unite_id' => $unites[$i % $unites->count()]->id,
                        'collecteur_id' => $collecteurs->random()->id,
                        'montant' => $m,
                        'date_paiement' => now()->subDays($i * 2),
                        'mode_paiement' => ['cash', 'wave', 'orange_money'][$i % 3],
                        'statut' => 'valide',
                        'commentaire' => 'Paiement démo mois en cours',
                        'reference_transaction' => 'DEMO-' . now()->format('Ymd') . '-' . ($i + 1),
                    ]);
                }
                $this->info('Paiements du mois en cours ajoutés.');
            }
        }

        \Illuminate\Support\Facades\Cache::forget('maire_dashboard_stats');
        \Illuminate\Support\Facades\Cache::flush();
        $this->info('Cache vidé.');

        $this->info('Données démo SICOFIL prêtes. Actualisez le dashboard.');
        return self::SUCCESS;
    }
}
