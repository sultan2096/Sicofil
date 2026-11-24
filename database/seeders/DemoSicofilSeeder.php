<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Abonnement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Données de démonstration pour le dashboard Admin SICOFIL
 * Abonnements, factures, webhooks, notifications
 */
class DemoSicofilSeeder extends Seeder
{
    public function run(): void
    {
        $sandiara = Commune::where('code', 'SANDIARA')->orWhere('nom', 'Sandiara')->first();
        if (!$sandiara) {
            $this->command->warn('Commune Sandiara introuvable.');
            return;
        }

        // 1. Abonnement actif pour Sandiara
        $abonnement = Abonnement::updateOrCreate(
            ['commune_id' => $sandiara->id],
            [
                'statut' => 'actif',
                'formule' => 'standard',
                'montant_fixe_annuel' => 500000,
                'pourcentage_recettes' => 2.5,
                'date_debut' => now()->subMonths(6),
                'date_expiration' => now()->addMonths(6),
            ]
        );

        // 2. Factures fictives
        if (DB::getSchemaBuilder()->hasTable('factures_abonnement') && DB::table('factures_abonnement')->where('abonnement_id', $abonnement->id)->count() === 0) {
            $factures = [
                ['periode' => 'mensuel', 'montant' => 45000, 'statut' => 'payee'],
                ['periode' => 'mensuel', 'montant' => 45000, 'statut' => 'payee'],
                ['periode' => 'mensuel', 'montant' => 48000, 'statut' => 'en_attente'],
            ];
            foreach ($factures as $i => $f) {
                $dateEmission = now()->subMonths(2 - $i);
                DB::table('factures_abonnement')->insert([
                    'abonnement_id' => $abonnement->id,
                    'commune_id' => $sandiara->id,
                    'numero_facture' => 'FAC-DEMO-' . ($sandiara->code ?? 'SAN') . '-' . $dateEmission->format('Ym') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'periode' => $f['periode'],
                    'date_emission' => $dateEmission->format('Y-m-d'),
                    'date_echeance' => $dateEmission->copy()->addDays(30)->format('Y-m-d'),
                    'montant_fixe' => $f['montant'],
                    'montant_variable' => 0,
                    'montant_total' => $f['montant'],
                    'statut' => $f['statut'],
                    'date_paiement' => $f['statut'] === 'payee' ? $dateEmission->copy()->addDays(5)->format('Y-m-d') : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Logs webhooks fictifs
        if (DB::getSchemaBuilder()->hasTable('webhook_logs')) {
            $payload = json_encode(['transaction_id' => 'DEMO-' . uniqid(), 'montant' => 5000, 'statut' => 'success']);
            foreach (['wave', 'orange_money'] as $provider) {
                DB::table('webhook_logs')->insert([
                    'commune_id' => $sandiara->id,
                    'provider' => $provider,
                    'transaction_id' => 'TXN-DEMO-' . strtoupper(substr($provider, 0, 2)) . '-' . rand(1000, 9999),
                    'reference_externe' => 'REF-' . rand(100000, 999999),
                    'payload' => $payload,
                    'statut' => 'success',
                    'montant' => '5000',
                    'telephone_client' => '771234567',
                    'webhook_received_at' => now()->subHours(rand(1, 20)),
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Notifications fictives
        if (DB::getSchemaBuilder()->hasTable('notifications')) {
            DB::table('notifications')->insert([
                [
                    'commune_id' => $sandiara->id,
                    'type' => 'sms',
                    'categorie' => 'recu_paiement',
                    'destinataire' => '771234567',
                    'message' => 'Paiement de 5000 FCFA reçu. Réf: REF-12345',
                    'statut' => 'envoye',
                    'envoye_at' => now()->subHours(2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'commune_id' => $sandiara->id,
                    'type' => 'whatsapp',
                    'categorie' => 'rappel_taxe',
                    'destinataire' => '772345678',
                    'message' => 'Rappel: échéance taxe trimestrielle le 15/03/2026',
                    'statut' => 'en_attente',
                    'envoye_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $this->command->info('Seeder DemoSicofil: abonnements, factures, webhooks, notifications créés.');
    }
}
