<?php

namespace App\Console\Commands;

use App\Models\Paiement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Commande pour générer les factures d'abonnement mensuelles (SaaS)
 * À lancer via le scheduler (cron) ou manuellement
 */
class GenerateFacturesCommand extends Command
{
    protected $signature = 'sicofil:generate-factures 
                            {--periode=mensuel : mensuel|trimestriel|annuel}
                            {--mois= : Mois (1-12), défaut: mois courant}
                            {--annee= : Année, défaut: année courante}';

    protected $description = 'Génère les factures d\'abonnement pour les communes actives';

    public function handle(): int
    {
        $periode = $this->option('periode');
        $mois = (int) ($this->option('mois') ?: now()->month);
        $annee = (int) ($this->option('annee') ?: now()->year);

        if (!in_array($periode, ['mensuel', 'trimestriel', 'annuel'])) {
            $this->error('Période invalide. Utilisez: mensuel, trimestriel ou annuel.');
            return self::FAILURE;
        }

        $abonnements = DB::table('abonnements')
            ->whereIn('statut', ['actif', 'test'])
            ->get();

        if ($abonnements->isEmpty()) {
            $this->warn('Aucun abonnement actif ou test trouvé.');
            return self::SUCCESS;
        }

        $facturesCreees = 0;
        $dateEmission = now()->setMonth($mois)->setYear($annee)->startOfMonth();

        foreach ($abonnements as $abonnement) {
            $existante = DB::table('factures_abonnement')
                ->where('abonnement_id', $abonnement->id)
                ->where('periode', $periode)
                ->whereMonth('date_emission', $mois)
                ->whereYear('date_emission', $annee)
                ->exists();

            if ($existante) {
                continue;
            }

            $diviseur = match ($periode) {
                'mensuel' => 12,
                'trimestriel' => 4,
                'annuel' => 1,
                default => 12,
            };

            $recettes = Paiement::withoutGlobalScopes()
                ->where('commune_id', $abonnement->commune_id)
                ->whereMonth('date_paiement', $mois)
                ->whereYear('date_paiement', $annee)
                ->where('statut', 'valide')
                ->sum('montant');

            $montantVariable = ($recettes * ($abonnement->pourcentage_recettes ?? 0)) / 100;
            $montantFixe = ($abonnement->montant_fixe_annuel ?? 0) / $diviseur;
            $montantTotal = $montantFixe + $montantVariable;

            DB::table('factures_abonnement')->insert([
                'abonnement_id' => $abonnement->id,
                'commune_id' => $abonnement->commune_id,
                'numero_facture' => 'FACT-' . strtoupper(uniqid()),
                'periode' => $periode,
                'date_emission' => $dateEmission->format('Y-m-d'),
                'date_echeance' => $dateEmission->copy()->addDays(30)->format('Y-m-d'),
                'montant_fixe' => $montantFixe,
                'montant_variable' => $montantVariable,
                'montant_total' => $montantTotal,
                'statut' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $facturesCreees++;
        }

        $this->info("{$facturesCreees} facture(s) générée(s) pour {$periode} {$mois}/{$annee}.");

        return self::SUCCESS;
    }
}
