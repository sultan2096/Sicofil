<?php

namespace Database\Seeders;

use App\Models\Paiement;
use App\Models\UniteCommerciale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaiementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer les paiements existants
        Paiement::truncate();
        
        // Récupérer les unités commerciales et collecteurs
        $unites = UniteCommerciale::all();
        $collecteurs = User::where('role', 'collecteur')->get();

        if ($unites->count() == 0 || $collecteurs->count() == 0) {
            $this->command->warn('Aucune unité commerciale ou collecteur trouvé. Veuillez d\'abord exécuter les autres seeders.');
            return;
        }

        // Paiements réalistes avec des données cohérentes
        $paiementsData = [
            // Juillet 2025
            // Mois en cours (démo visible sur les dashboards)
            [
                'date' => now()->format('Y-m-d'),
                'unite_type' => 'Station',
                'proprietaire' => 'Ousmane Fall',
                'montant' => 15000,
                'mode' => 'wave',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            [
                'date' => now()->subDays(2)->format('Y-m-d'),
                'unite_type' => 'Boutique',
                'proprietaire' => 'Mamadou Diop',
                'montant' => 5000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            [
                'date' => now()->subDays(5)->format('Y-m-d'),
                'unite_type' => 'Restaurant',
                'proprietaire' => 'Aminata Ba',
                'montant' => 25000,
                'mode' => 'orange_money',
                'collecteur' => 'Ibrahima Diop',
                'zone' => 'Zone RN1',
                'statut' => 'valide'
            ],
            [
                'date' => now()->subDays(8)->format('Y-m-d'),
                'unite_type' => 'Épicerie',
                'proprietaire' => 'Cheikh Sy',
                'montant' => 8000,
                'mode' => 'cash',
                'collecteur' => 'Mamadou Ba',
                'zone' => 'Zone Périphérique Est',
                'statut' => 'valide'
            ],
            [
                'date' => now()->subDays(12)->format('Y-m-d'),
                'unite_type' => 'Pharmacie',
                'proprietaire' => 'Khadija Thiam',
                'montant' => 12000,
                'mode' => 'wave',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            // Juillet 2025
            [
                'date' => '2025-07-05',
                'unite_type' => 'Station',
                'proprietaire' => 'Ousmane Fall',
                'montant' => 2000,
                'mode' => 'cash',
                'collecteur' => 'Fatou Sow',
                'zone' => 'Zone Marché Hebdomadaire',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-07-04',
                'unite_type' => 'Épicerie',
                'proprietaire' => 'Cheikh Sy',
                'montant' => 10000,
                'mode' => 'cash',
                'collecteur' => 'Mamadou Ba',
                'zone' => 'Zone Périphérique Est',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-07-03',
                'unite_type' => 'Atelier',
                'proprietaire' => 'Moussa Diouf',
                'montant' => 5000,
                'mode' => 'cash',
                'collecteur' => 'Ibrahima Diop',
                'zone' => 'Zone RN1',
                'statut' => 'valide'
            ],
            
            // Juin 2025
            [
                'date' => '2025-06-30',
                'unite_type' => 'Pharmacie',
                'proprietaire' => 'Khadija Thiam',
                'montant' => 3000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'en_attente'
            ],
            [
                'date' => '2025-06-23',
                'unite_type' => 'Cybercafé',
                'proprietaire' => 'Ndeye Awa Diallo',
                'montant' => 20000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'en_attente'
            ],
            [
                'date' => '2025-06-20',
                'unite_type' => 'Boutique',
                'proprietaire' => 'Mamadou Diop',
                'montant' => 1500,
                'mode' => 'wave',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-06-15',
                'unite_type' => 'Station',
                'proprietaire' => 'Ousmane Fall',
                'montant' => 15000,
                'mode' => 'cash',
                'collecteur' => 'Fatou Sow',
                'zone' => 'Zone Marché Hebdomadaire',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-06-14',
                'unite_type' => 'Marché',
                'proprietaire' => 'Ibrahima Ndiaye',
                'montant' => 2000,
                'mode' => 'wave',
                'collecteur' => 'Fatou Sow',
                'zone' => 'Zone Marché Hebdomadaire',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-06-14',
                'unite_type' => 'Restaurant',
                'proprietaire' => 'Aminata Ba',
                'montant' => 35000,
                'mode' => 'orange_money',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-06-10',
                'unite_type' => 'Marché',
                'proprietaire' => 'Ibrahima Ndiaye',
                'montant' => 5000,
                'mode' => 'wave',
                'collecteur' => 'Fatou Sow',
                'zone' => 'Zone Marché Hebdomadaire',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-06-10',
                'unite_type' => 'Pharmacie',
                'proprietaire' => 'Khadija Thiam',
                'montant' => 20000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-06-02',
                'unite_type' => 'Cybercafé',
                'proprietaire' => 'Ndeye Awa Diallo',
                'montant' => 20000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            
            // Mai 2025
            [
                'date' => '2025-05-29',
                'unite_type' => 'Station',
                'proprietaire' => 'Ousmane Fall',
                'montant' => 500,
                'mode' => 'wave',
                'collecteur' => 'Fatou Sow',
                'zone' => 'Zone Marché Hebdomadaire',
                'statut' => 'en_attente'
            ],
            [
                'date' => '2025-05-22',
                'unite_type' => 'Boutique',
                'proprietaire' => 'Mamadou Diop',
                'montant' => 5000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-05-16',
                'unite_type' => 'Salon',
                'proprietaire' => 'Fatou Sall',
                'montant' => 3000,
                'mode' => 'wave',
                'collecteur' => 'Ibrahima Diop',
                'zone' => 'Zone RN1',
                'statut' => 'en_attente'
            ],
            [
                'date' => '2025-05-14',
                'unite_type' => 'Salon',
                'proprietaire' => 'Fatou Sall',
                'montant' => 10000,
                'mode' => 'cash',
                'collecteur' => 'Ibrahima Diop',
                'zone' => 'Zone RN1',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-05-09',
                'unite_type' => 'Atelier',
                'proprietaire' => 'Moussa Diouf',
                'montant' => 10000,
                'mode' => 'orange_money',
                'collecteur' => 'Ibrahima Diop',
                'zone' => 'Zone RN1',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-05-09',
                'unite_type' => 'Épicerie',
                'proprietaire' => 'Cheikh Sy',
                'montant' => 1500,
                'mode' => 'cash',
                'collecteur' => 'Mamadou Ba',
                'zone' => 'Zone Périphérique Est',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-05-07',
                'unite_type' => 'Cybercafé',
                'proprietaire' => 'Ndeye Awa Diallo',
                'montant' => 2000,
                'mode' => 'wave',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'en_attente'
            ],
            [
                'date' => '2025-05-02',
                'unite_type' => 'Salon',
                'proprietaire' => 'Fatou Sall',
                'montant' => 20000,
                'mode' => 'cash',
                'collecteur' => 'Ibrahima Diop',
                'zone' => 'Zone RN1',
                'statut' => 'valide'
            ],
            
            // Avril 2025
            [
                'date' => '2025-04-29',
                'unite_type' => 'Marché',
                'proprietaire' => 'Ibrahima Ndiaye',
                'montant' => 20000,
                'mode' => 'wave',
                'collecteur' => 'Fatou Sow',
                'zone' => 'Zone Marché Hebdomadaire',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-04-28',
                'unite_type' => 'Quincaillerie',
                'proprietaire' => 'Babacar Faye',
                'montant' => 10000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-04-09',
                'unite_type' => 'Atelier',
                'proprietaire' => 'Moussa Diouf',
                'montant' => 20000,
                'mode' => 'cash',
                'collecteur' => 'Ibrahima Diop',
                'zone' => 'Zone RN1',
                'statut' => 'valide'
            ],
            [
                'date' => '2025-04-08',
                'unite_type' => 'Quincaillerie',
                'proprietaire' => 'Babacar Faye',
                'montant' => 20000,
                'mode' => 'orange_money',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'en_attente'
            ],
            [
                'date' => '2025-04-08',
                'unite_type' => 'Quincaillerie',
                'proprietaire' => 'Babacar Faye',
                'montant' => 10000,
                'mode' => 'cash',
                'collecteur' => 'Gueyki Faye',
                'zone' => 'Zone Centre-Ville',
                'statut' => 'valide'
            ]
        ];

        // Créer les paiements réalistes
        foreach ($paiementsData as $data) {
            // Trouver l'unité correspondante ou créer une correspondance
            $unite = $unites->where('nature', $data['unite_type'])->where('nom_proprietaire', $data['proprietaire'])->first();
            if (!$unite) {
                // Prendre une unité au hasard si pas de correspondance exacte
                $unite = $unites->random();
            }
            
            // Trouver le collecteur correspondant
            $collecteur = $collecteurs->where('name', $data['collecteur'])->first();
            if (!$collecteur) {
                // Prendre un collecteur au hasard si pas de correspondance exacte
                $collecteur = $collecteurs->random();
            }
            
            $datePaiement = Carbon::parse($data['date']);
            
            Paiement::create([
                    'collecteur_id' => $collecteur->id,
                    'unite_id' => $unite->id,
                    'commune_id' => $unite->commune_id ?? $unite->zone?->commune_id ?? \App\Models\Commune::where('code', 'SANDIARA')->value('id'),
                'montant' => $data['montant'],
                    'date_paiement' => $datePaiement,
                'mode_paiement' => $data['mode'],
                'statut' => $data['statut'],
                'commentaire' => 'Paiement réaliste - ' . $data['unite_type'] . ' ' . $data['proprietaire'],
                    'reference_transaction' => 'REF' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                    'created_at' => $datePaiement,
                    'updated_at' => $datePaiement,
            ]);
        }

        $this->command->info('Seeder Paiement terminé: ' . count($paiementsData) . ' paiements réalistes créés.');
    }
} 