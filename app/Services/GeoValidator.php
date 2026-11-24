<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de validation et normalisation des géométries GeoJSON
 * Forçage SRID 4326 (WGS84) sur tout le périmètre SIG.
 */
class GeoValidator
{
    public const SRID = 4326;
    public const ALLOWED_TYPES = ['Polygon', 'MultiPolygon'];

    /**
     * Valide et normalise une géométrie GeoJSON.
     *
     * @param  mixed $geometry  Tableau GeoJSON, chaîne JSON ou null.
     * @return array{ok: bool, geometry: ?array, errors: array<string>, repaired: bool}
     */
    public function validate($geometry, array $options = []): array
    {
        $allowedTypes = $options['allowed_types'] ?? self::ALLOWED_TYPES;
        $autoRepair = $options['auto_repair'] ?? true;
        $errors = [];
        $repaired = false;

        if (is_string($geometry)) {
            $decoded = json_decode($geometry, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['ok' => false, 'geometry' => null, 'errors' => ['JSON invalide.'], 'repaired' => false];
            }
            $geometry = $decoded;
        }

        if (!is_array($geometry) || empty($geometry['type']) || empty($geometry['coordinates'])) {
            return ['ok' => false, 'geometry' => null, 'errors' => ['Structure GeoJSON invalide (type/coordinates manquants).'], 'repaired' => false];
        }

        if (!in_array($geometry['type'], $allowedTypes, true)) {
            return [
                'ok' => false,
                'geometry' => null,
                'errors' => ['Type de géométrie non supporté: ' . $geometry['type'] . '. Attendu: ' . implode(', ', $allowedTypes)],
                'repaired' => false,
            ];
        }

        if (!$this->coordinatesAreWgs84($geometry)) {
            $errors[] = 'Coordonnées hors plage WGS84 (lon ∈ [-180,180], lat ∈ [-90,90]). SRID 4326 requis.';
            return ['ok' => false, 'geometry' => null, 'errors' => $errors, 'repaired' => false];
        }

        if (!$this->ringsAreClosed($geometry)) {
            if ($autoRepair) {
                $geometry = $this->closeRings($geometry);
                $repaired = true;
            } else {
                $errors[] = 'Anneau de polygone non fermé.';
                return ['ok' => false, 'geometry' => null, 'errors' => $errors, 'repaired' => false];
            }
        }

        // Validation PostGIS optionnelle (ST_IsValid + tentative ST_MakeValid)
        $validity = $this->postgisValidate($geometry, $autoRepair);
        if ($validity['ok'] === false) {
            $errors = array_merge($errors, $validity['errors']);
            return ['ok' => false, 'geometry' => null, 'errors' => $errors, 'repaired' => $repaired];
        }
        if ($validity['geometry'] !== null) {
            $geometry = $validity['geometry'];
            $repaired = $repaired || $validity['repaired'];
        }

        return ['ok' => true, 'geometry' => $geometry, 'errors' => $errors, 'repaired' => $repaired];
    }

    private function coordinatesAreWgs84(array $geometry): bool
    {
        $check = function ($coords) use (&$check) {
            if (!is_array($coords)) {
                return false;
            }
            // Position [lon, lat] ?
            if (isset($coords[0]) && is_numeric($coords[0])) {
                $lon = (float) $coords[0];
                $lat = (float) ($coords[1] ?? 0);
                return $lon >= -180 && $lon <= 180 && $lat >= -90 && $lat <= 90;
            }
            foreach ($coords as $c) {
                if (!$check($c)) {
                    return false;
                }
            }
            return true;
        };

        return $check($geometry['coordinates']);
    }

    private function ringsAreClosed(array $geometry): bool
    {
        $polys = $geometry['type'] === 'Polygon'
            ? [$geometry['coordinates']]
            : $geometry['coordinates'];

        foreach ($polys as $poly) {
            foreach ($poly as $ring) {
                if (!is_array($ring) || count($ring) < 4) {
                    return false;
                }
                $first = $ring[0];
                $last = $ring[count($ring) - 1];
                if ($first !== $last) {
                    return false;
                }
            }
        }
        return true;
    }

    private function closeRings(array $geometry): array
    {
        $closeRing = function ($ring) {
            if (!is_array($ring) || count($ring) < 1) {
                return $ring;
            }
            $first = $ring[0];
            $last = $ring[count($ring) - 1];
            if ($first !== $last) {
                $ring[] = $first;
            }
            return $ring;
        };

        if ($geometry['type'] === 'Polygon') {
            $geometry['coordinates'] = array_map($closeRing, $geometry['coordinates']);
        } else { // MultiPolygon
            $geometry['coordinates'] = array_map(function ($poly) use ($closeRing) {
                return array_map($closeRing, $poly);
            }, $geometry['coordinates']);
        }
        return $geometry;
    }

    /**
     * @return array{ok: bool, geometry: ?array, errors: array<string>, repaired: bool}
     */
    private function postgisValidate(array $geometry, bool $autoRepair): array
    {
        try {
            $json = json_encode($geometry);
            $sridSql = self::SRID;
            $row = DB::selectOne(
                "SELECT ST_IsValid(ST_SetSRID(ST_GeomFromGeoJSON(?), {$sridSql})) AS ok,
                        ST_IsValidReason(ST_SetSRID(ST_GeomFromGeoJSON(?), {$sridSql})) AS reason",
                [$json, $json]
            );
            if ($row && $row->ok) {
                return ['ok' => true, 'geometry' => null, 'errors' => [], 'repaired' => false];
            }
            if (!$autoRepair) {
                return ['ok' => false, 'geometry' => null, 'errors' => ['Géométrie invalide: ' . ($row->reason ?? 'inconnu')], 'repaired' => false];
            }
            $repaired = DB::selectOne(
                "SELECT ST_AsGeoJSON(ST_MakeValid(ST_SetSRID(ST_GeomFromGeoJSON(?), {$sridSql})))::text AS gj,
                        ST_IsValid(ST_MakeValid(ST_SetSRID(ST_GeomFromGeoJSON(?), {$sridSql}))) AS ok",
                [$json, $json]
            );
            if ($repaired && $repaired->ok) {
                $decoded = json_decode($repaired->gj, true);
                if (is_array($decoded) && in_array($decoded['type'] ?? '', self::ALLOWED_TYPES, true)) {
                    return ['ok' => true, 'geometry' => $decoded, 'errors' => [], 'repaired' => true];
                }
            }
            return ['ok' => false, 'geometry' => null, 'errors' => ['Géométrie invalide non réparable: ' . ($row->reason ?? 'inconnu')], 'repaired' => false];
        } catch (\Throwable $e) {
            // Si PostGIS n'est pas disponible, on ne bloque pas (validation basique déjà faite)
            Log::warning('GeoValidator PostGIS check skipped: ' . $e->getMessage());
            return ['ok' => true, 'geometry' => null, 'errors' => [], 'repaired' => false];
        }
    }
}
