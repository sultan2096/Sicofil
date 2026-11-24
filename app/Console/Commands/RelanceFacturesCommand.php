<?php

namespace App\Console\Commands;

use App\Models\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RelanceFacturesCommand extends Command
{
    protected $signature = 'sicofil:relance-factures';
    protected $description = 'Relance automatique des factures en retard et mise à jour des statuts';

    public function handle(): int
    {
        // Mettre à jour les factures en attente dont la date d'échéance est dépassée
        $misesEnRetard = DB::table('factures_abonnement')
            ->where('statut', 'en_attente')
            ->where('date_echeance', '<', now())
            ->update([
                'statut' => 'en_retard',
                'updated_at' => now(),
            ]);

        if ($misesEnRetard > 0) {
            $this->info("{$misesEnRetard} facture(s) mise(s) en retard.");
            Log::createLog('factures_relance_retard', [
                'count' => $misesEnRetard,
                'type' => 'auto_status_update',
            ]);
        }

        // Compter les factures en retard pour relance
        $facturesEnRetard = DB::table('factures_abonnement')
            ->join('communes', 'factures_abonnement.commune_id', '=', 'communes.id')
            ->where('factures_abonnement.statut', 'en_retard')
            ->select('factures_abonnement.*', 'communes.nom as commune_nom')
            ->get();

        $relances = 0;
        foreach ($facturesEnRetard as $facture) {
            // Créer une notification de relance dans la table notifications
            if (DB::table('notifications')->where('paiement_id', $facture->id)->where('categorie', 'alerte_retard')->doesntExist()) {
                DB::table('notifications')->insert([
                    'commune_id' => $facture->commune_id,
                    'paiement_id' => $facture->id,
                    'type' => 'email',
                    'categorie' => 'alerte_retard',
                    'destinataire' => 'admin@sicofil.local',
                    'objet' => "Relance facture #{$facture->numero_facture} - {$facture->commune_nom}",
                    'message' => "La facture #{$facture->numero_facture} de la commune {$facture->commune_nom} est en retard. Montant : " . number_format($facture->montant_total, 0, ',', ' ') . " FCFA.",
                    'statut' => 'en_attente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $relances++;
            }
        }

        if ($relances > 0) {
            $this->info("{$relances} relance(s) créée(s).");
            Log::createLog('factures_relance_notification', [
                'count' => $relances,
                'type' => 'auto_relance',
            ]);
        } else {
            $this->info('Aucune nouvelle relance nécessaire.');
        }

        return self::SUCCESS;
    }
}
