<?php

namespace Tests\Feature;

use App\Models\Commune;
use App\Models\User;
use App\Models\ZoneFiscale;
use App\Services\GeoValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GeoValidationTest extends TestCase
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

    public function test_geometry_outside_wgs84_is_rejected(): void
    {
        $validator = new GeoValidator();
        $geom = [
            'type' => 'Polygon',
            'coordinates' => [[
                [500000.0, 1500000.0],
                [500100.0, 1500000.0],
                [500100.0, 1500100.0],
                [500000.0, 1500100.0],
                [500000.0, 1500000.0],
            ]],
        ];
        $result = $validator->validate($geom);
        $this->assertFalse($result['ok']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_unclosed_ring_is_auto_repaired(): void
    {
        $validator = new GeoValidator();
        $geom = [
            'type' => 'Polygon',
            'coordinates' => [[
                [-16.80, 14.43],
                [-16.79, 14.43],
                [-16.79, 14.44],
                [-16.80, 14.44],
            ]],
        ];
        $result = $validator->validate($geom);
        $this->assertTrue($result['ok']);
        $first = $result['geometry']['coordinates'][0][0];
        $last = $result['geometry']['coordinates'][0][count($result['geometry']['coordinates'][0]) - 1];
        $this->assertSame($first, $last);
    }

    public function test_invalid_geometry_type_is_rejected(): void
    {
        $validator = new GeoValidator();
        $result = $validator->validate(['type' => 'Point', 'coordinates' => [-16.8, 14.4]]);
        $this->assertFalse($result['ok']);
    }

    public function test_zone_assign_collecteur_blocks_cross_commune(): void
    {
        $communeA = $this->commune('Alpha');
        $communeB = $this->commune('Beta');
        $zoneA = ZoneFiscale::create(['nom' => 'Zone A', 'commune_id' => $communeA->id]);
        $collecteurB = $this->user('collecteur', $communeB);

        $this->assertFalse($zoneA->assignCollecteur($collecteurB));
        $this->assertNull($collecteurB->fresh()->zone_id);
    }

    public function test_zone_assign_collecteur_succeeds_same_commune(): void
    {
        $commune = $this->commune('Gamma');
        $zone = ZoneFiscale::create(['nom' => 'Zone G', 'commune_id' => $commune->id]);
        $c1 = $this->user('collecteur', $commune);
        $c2 = $this->user('collecteur', $commune);

        $this->assertTrue($zone->assignCollecteur($c1));
        $this->assertTrue($zone->assignCollecteur($c2));
        $this->assertSame(2, $zone->collecteurs()->count());
    }
}
