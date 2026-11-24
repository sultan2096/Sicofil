<?php

namespace Tests\Feature;

use App\Models\Commune;
use App\Models\UniteCommerciale;
use App\Models\User;
use App\Models\ZoneFiscale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiCommuneIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function commune(string $nom): Commune
    {
        return Commune::create([
            'nom' => $nom,
            'code' => strtoupper(substr($nom, 0, 6)),
            'actif' => true,
        ]);
    }

    private function user(string $role, Commune $commune, ?ZoneFiscale $zone = null): User
    {
        return User::withoutGlobalScopes()->create([
            'name' => ucfirst($role) . ' ' . $commune->nom,
            'identifiant' => $role . '_' . strtolower($commune->code) . '_' . uniqid(),
            'email' => $role . '_' . strtolower($commune->code) . '_' . uniqid() . '@test.local',
            'password' => Hash::make('password'),
            'role' => $role,
            'commune_id' => $commune->id,
            'zone_id' => $zone?->id,
        ]);
    }

    public function test_gestionnaire_cannot_create_collector_with_zone_from_other_commune(): void
    {
        $communeA = $this->commune('Alpha');
        $communeB = $this->commune('Beta');
        $gestionnaireA = $this->user('gestionnaire', $communeA);
        $zoneB = ZoneFiscale::create(['nom' => 'Zone B', 'commune_id' => $communeB->id]);

        $response = $this->actingAs($gestionnaireA)->post(route('users.store'), [
            'name' => 'Collecteur interdit',
            'identifiant' => 'collecteur_interdit',
            'password' => 'password',
            'password_confirmation' => 'password',
            'zone_id' => $zoneB->id,
        ]);

        $response->assertSessionHasErrors('zone_id');
        $this->assertDatabaseMissing('users', ['identifiant' => 'collecteur_interdit']);
    }

    public function test_gestionnaire_cannot_access_user_from_other_commune(): void
    {
        $communeA = $this->commune('Alpha');
        $communeB = $this->commune('Beta');
        $gestionnaireA = $this->user('gestionnaire', $communeA);
        $collecteurB = $this->user('collecteur', $communeB);

        $this->actingAs($gestionnaireA)
            ->get(route('users.show', $collecteurB))
            ->assertNotFound();
    }

    public function test_collecteur_cannot_access_unit_outside_his_zone(): void
    {
        $commune = $this->commune('Alpha');
        $zoneA = ZoneFiscale::create(['nom' => 'Zone A', 'commune_id' => $commune->id]);
        $zoneB = ZoneFiscale::create(['nom' => 'Zone B', 'commune_id' => $commune->id]);
        $collecteurA = $this->user('collecteur', $commune, $zoneA);
        $uniteB = UniteCommerciale::create([
            'nom_proprietaire' => 'Proprietaire B',
            'prenom_proprietaire' => 'Prenom',
            'categorie' => 'activite_commerciale',
            'nature' => 'Boutique ou Magasin',
            'type_commerce' => 'Alimentation',
            'zone_id' => $zoneB->id,
            'commune_id' => $commune->id,
            'latitude' => 14.5,
            'longitude' => -16.8,
            'actif' => true,
        ]);

        $this->actingAs($collecteurA)
            ->get(route('unites.show', $uniteB))
            ->assertForbidden();
    }

    public function test_admin_sicofil_can_create_user_only_with_zone_from_target_commune(): void
    {
        $communeA = $this->commune('Alpha');
        $communeB = $this->commune('Beta');
        $admin = User::withoutGlobalScopes()->create([
            'name' => 'Admin SICOFIL',
            'identifiant' => 'admin_sicofil_test',
            'email' => 'admin_sicofil_test@test.local',
            'password' => Hash::make('password'),
            'role' => 'admin_sicofil',
            'commune_id' => null,
        ]);
        $zoneB = ZoneFiscale::create(['nom' => 'Zone B', 'commune_id' => $communeB->id]);

        $response = $this->actingAs($admin)->post(route('communes.users.store', $communeA), [
            'name' => 'Collecteur mauvais scope',
            'identifiant' => 'collecteur_mauvais_scope',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'collecteur',
            'zone_id' => $zoneB->id,
        ]);

        $response->assertSessionHasErrors('zone_id');
        $this->assertDatabaseMissing('users', ['identifiant' => 'collecteur_mauvais_scope']);
    }
}
