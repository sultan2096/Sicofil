<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Paiement;
use App\Models\UniteCommerciale;
use App\Models\Taxe;
use App\Models\Commune;
use App\Models\ZoneFiscale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $collecteur;
    protected User $gestionnaire;
    protected Commune $commune;
    protected ZoneFiscale $zone;
    protected UniteCommerciale $unite;
    protected Taxe $taxe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commune = Commune::factory()->create();
        $this->zone = ZoneFiscale::factory()->create(['commune_id' => $this->commune->id]);
        $this->taxe = Taxe::factory()->create(['commune_id' => $this->commune->id, 'montant' => 5000]);
        $this->unite = UniteCommerciale::factory()->create([
            'commune_id' => $this->commune->id,
            'zone_id' => $this->zone->id,
            'taxe_id' => $this->taxe->id,
        ]);

        $this->gestionnaire = User::factory()->create([
            'role' => 'gestionnaire',
            'commune_id' => $this->commune->id,
        ]);

        $this->collecteur = User::factory()->create([
            'role' => 'collecteur',
            'commune_id' => $this->commune->id,
            'zone_id' => $this->zone->id,
        ]);
    }

    /**
     * Test que les collecteurs doivent fournir une géolocalisation pour créer un paiement.
     */
    public function test_collector_must_provide_gps_for_payment(): void
    {
        $this->actingAs($this->collecteur);

        // Sans géolocalisation - doit échouer
        $response = $this->post(route('paiements.store'), [
            'unite_id' => $this->unite->id,
            'taxe_id' => $this->taxe->id,
            'montant' => 5000,
            'date_paiement' => now()->format('Y-m-d'),
            'mode_paiement' => 'cash',
        ]);

        $response->assertSessionHasErrors(['payment_latitude', 'payment_longitude']);
    }

    /**
     * Test que les gestionnaires peuvent créer un paiement sans géolocalisation.
     */
    public function test_manager_can_create_payment_without_gps(): void
    {
        $this->actingAs($this->gestionnaire);

        $response = $this->post(route('paiements.store'), [
            'unite_id' => $this->unite->id,
            'taxe_id' => $this->taxe->id,
            'montant' => 5000,
            'date_paiement' => now()->format('Y-m-d'),
            'mode_paiement' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('paiements', [
            'unite_id' => $this->unite->id,
            'montant' => 5000,
        ]);
    }

    /**
     * Test que les collecteurs peuvent créer un paiement avec géolocalisation valide.
     */
    public function test_collector_can_create_payment_with_valid_gps(): void
    {
        $this->actingAs($this->collecteur);

        $response = $this->post(route('paiements.store'), [
            'unite_id' => $this->unite->id,
            'taxe_id' => $this->taxe->id,
            'montant' => 5000,
            'date_paiement' => now()->format('Y-m-d'),
            'mode_paiement' => 'cash',
            'payment_latitude' => 14.7167,
            'payment_longitude' => -17.4677,
            'payment_accuracy' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('paiements', [
            'unite_id' => $this->unite->id,
            'montant' => 5000,
            'payment_latitude' => 14.7167,
            'payment_longitude' => -17.4677,
        ]);
    }

    /**
     * Test que les collecteurs ne peuvent pas payer une unité hors de leur zone.
     */
    public function test_collector_cannot_pay_unit_outside_zone(): void
    {
        $otherZone = ZoneFiscale::factory()->create(['commune_id' => $this->commune->id]);
        $otherUnit = UniteCommerciale::factory()->create([
            'commune_id' => $this->commune->id,
            'zone_id' => $otherZone->id,
            'taxe_id' => $this->taxe->id,
        ]);

        $this->actingAs($this->collecteur);

        $response = $this->post(route('paiements.store'), [
            'unite_id' => $otherUnit->id,
            'taxe_id' => $this->taxe->id,
            'montant' => 5000,
            'date_paiement' => now()->format('Y-m-d'),
            'mode_paiement' => 'cash',
            'payment_latitude' => 14.7167,
            'payment_longitude' => -17.4677,
            'payment_accuracy' => 10,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test que les coordonnées GPS invalides sont rejetées.
     */
    public function test_invalid_gps_coordinates_are_rejected(): void
    {
        $this->actingAs($this->collecteur);

        // Latitude hors plage
        $response = $this->post(route('paiements.store'), [
            'unite_id' => $this->unite->id,
            'taxe_id' => $this->taxe->id,
            'montant' => 5000,
            'date_paiement' => now()->format('Y-m-d'),
            'mode_paiement' => 'cash',
            'payment_latitude' => 95, // invalide (> 90)
            'payment_longitude' => -17.4677,
            'payment_accuracy' => 10,
        ]);

        $response->assertSessionHasErrors(['payment_latitude']);

        // Longitude hors plage
        $response = $this->post(route('paiements.store'), [
            'unite_id' => $this->unite->id,
            'taxe_id' => $this->taxe->id,
            'montant' => 5000,
            'date_paiement' => now()->format('Y-m-d'),
            'mode_paiement' => 'cash',
            'payment_latitude' => 14.7167,
            'payment_longitude' => 200, // invalide (> 180)
            'payment_accuracy' => 10,
        ]);

        $response->assertSessionHasErrors(['payment_longitude']);
    }

    /**
     * Test que la précision GPS est obligatoire pour les collecteurs.
     */
    public function test_gps_accuracy_required_for_collectors(): void
    {
        $this->actingAs($this->collecteur);

        $response = $this->post(route('paiements.store'), [
            'unite_id' => $this->unite->id,
            'taxe_id' => $this->taxe->id,
            'montant' => 5000,
            'date_paiement' => now()->format('Y-m-d'),
            'mode_paiement' => 'cash',
            'payment_latitude' => 14.7167,
            'payment_longitude' => -17.4677,
            // payment_accuracy manquant
        ]);

        $response->assertSessionHasErrors(['payment_accuracy']);
    }
}
