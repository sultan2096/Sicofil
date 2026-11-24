<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NatureActivite;
use App\Models\TypeCommerce;

class UpdateTypeCommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapping des types de commerce par nature d'activité
        $typesParNature = [
            'Boutique ou Magasin' => [
                'Alimentation', 'Grossiste', 'Mercerie', 'Quincaillerie', 
                'Shopping', 'Cosmétique', 'Pharmacie', 'Bijouterie'
            ],
            'Atelier' => [
                'Atelier mécanicien', 'Atelier de couture', 'Atelier de coiffure', 
                'Atelier de forgeron', 'Atelier de cordonnerie', 'Atelier de menuiserie', 
                'Boulangerie', 'Atelier de soudure'
            ],
            'Agriculture' => [
                'Chevaux', 'Ânes', 'Ovins', 'Caprins', 'Bovins', 
                'Vaches', 'Moutons', 'Chèvres', 'Volailles'
            ],
            'Transport' => [
                'Bus', 'Camion-citerne de carburant', 'Camionnette', 'Calèche', 
                'Camion gros porteur', 'Taxi clando', 'Moto Jakarta', 'Car rapide'
            ],
            'Étal' => [
                'Vente sur étal'
            ],
            'Table ou Tente commerciale' => [
                'Vente sur table', 'Vente sous tente'
            ],
            'Cantine ou Kiosque' => [
                'Restaurant', 'Gargote', 'Dibiterie', 'Fast Food', 
                'Café', 'Bar', 'Kiosque'
            ],
            'Entreprise' => [
                'Entrepôt', 'Grande exploitation agricole', 'Supermarché', 
                'Station-service', 'Usine', 'Bureau'
            ],
            'Foncier' => [
                'Location de terrain', 'Location de bâtiments'
            ],
            'Publicité' => [
                'Véhicules publicitaires', 'Panneaux publicitaires', 'Enseignes'
            ],
            'Spectacles' => [
                'Lieux d\'événements', 'Salles de spectacle', 'Terrains de sport'
            ],
            'Papiers administratifs' => [
                'Extrait de naissance', 'Certificat de décès', 'Acte de mariage', 
                'Photocopie', 'Dactylographie'
            ]
        ];

        foreach ($typesParNature as $nomNature => $types) {
            // Trouver la nature d'activité
            $nature = NatureActivite::where('nom', $nomNature)->first();
            
            if (!$nature) {
                $this->command->warn("Nature d'activité '$nomNature' non trouvée");
                continue;
            }

            foreach ($types as $index => $nomType) {
                // Vérifier si le type existe déjà
                $typeExistant = TypeCommerce::where('nom', $nomType)->first();
                
                if ($typeExistant) {
                    // Mettre à jour la relation
                    $typeExistant->update([
                        'nature_activite_id' => $nature->id
                    ]);
                    $this->command->info("Type '$nomType' lié à la nature '$nomNature'");
                } else {
                    // Créer le nouveau type
                    TypeCommerce::create([
                        'nom' => $nomType,
                        'description' => "Type de commerce: $nomType",
                        'nature_activite_id' => $nature->id,
                        'is_transport' => $nomNature === 'Transport',
                        'actif' => true,
                        'ordre' => $index + 1
                    ]);
                    $this->command->info("Type '$nomType' créé et lié à la nature '$nomNature'");
                }
            }
        }

        $this->command->info('Types de commerce mis à jour avec succès !');
    }
}
