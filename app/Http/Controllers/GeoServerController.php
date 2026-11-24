<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * GeoServer WMS/WFS - Intégration SIG intercommunal
 */
class GeoServerController extends Controller
{
    protected $baseUrl;
    protected $workspace;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('geoserver.base_url', 'http://localhost:8081/geoserver'), '/');
        $this->workspace = config('geoserver.workspace', 'sicofil');
    }

    /**
     * Obtenir l'URL WMS pour une couche
     */
    public function wmsUrl(Request $request)
    {
        if (!config('geoserver.enabled')) {
            return response()->json([
                'enabled' => false,
                'message' => 'GeoServer non activé. Définir GEOSERVER_ENABLED=true dans .env',
            ]);
        }

        $layer = $request->get('layer', 'zones_fiscales');
        $layers = config('geoserver.layers', []);
        $layerName = $layers[$layer] ?? $layer;

        $params = [
            'SERVICE' => 'WMS',
            'VERSION' => config('geoserver.wms.version', '1.3.0'),
            'REQUEST' => 'GetMap',
            'LAYERS' => "{$this->workspace}:{$layerName}",
            'FORMAT' => config('geoserver.wms.format', 'image/png'),
            'WIDTH' => $request->get('width', 800),
            'HEIGHT' => $request->get('height', 600),
            'CRS' => 'EPSG:4326',
            'BBOX' => $request->get('bbox', '-17.5,14.5,-17.3,14.7'),
        ];

        $url = "{$this->baseUrl}/wms?" . http_build_query($params);

        return response()->json([
            'enabled' => true,
            'url' => $url,
            'capabilities_url' => "{$this->baseUrl}/wms?service=WMS&version=1.3.0&request=GetCapabilities",
        ]);
    }

    /**
     * Obtenir les features WFS (GeoJSON)
     */
    public function wfsFeatures(Request $request)
    {
        if (!config('geoserver.enabled')) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }

        $layer = $request->get('layer', 'zones_fiscales');
        $layers = config('geoserver.layers', []);
        $layerName = $layers[$layer] ?? $layer;

        $params = [
            'SERVICE' => 'WFS',
            'VERSION' => config('geoserver.wfs.version', '2.0.0'),
            'REQUEST' => 'GetFeature',
            'TYPENAME' => "{$this->workspace}:{$layerName}",
            'OUTPUTFORMAT' => config('geoserver.wfs.output_format', 'application/json'),
            'SRSNAME' => 'EPSG:4326',
        ];

        if ($request->has('bbox')) {
            $params['BBOX'] = $request->bbox;
        }

        $url = "{$this->baseUrl}/wfs?" . http_build_query($params);

        try {
            $response = Http::timeout(10)->get($url);
            if ($response->successful()) {
                return response($response->body())->header('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            \Log::warning('GeoServer WFS error: ' . $e->getMessage());
        }

        return response()->json(['type' => 'FeatureCollection', 'features' => []]);
    }

    /**
     * URL de tuiles vectorielles (GWC / GeoServer)
     */
    public function vectorTilesUrl(Request $request)
    {
        if (!config('geoserver.enabled')) {
            return response()->json([
                'enabled' => false,
                'message' => 'GeoServer non activé. Définir GEOSERVER_ENABLED=true dans .env',
            ]);
        }

        $layer = $request->get('layer', 'zones_fiscales');
        $layers = config('geoserver.layers', []);
        $layerName = $layers[$layer] ?? $layer;
        $workspace = $this->workspace;
        $baseUrl = rtrim($this->baseUrl, '/');

        $template = "{$baseUrl}/gwc/service/tms/1.0.0/{$workspace}:{$layerName}@EPSG%3A3857@pbf/{z}/{x}/{-y}.pbf";

        return response()->json([
            'enabled' => true,
            'template' => $template,
            'layer' => $layerName,
        ]);
    }

    /**
     * Page de test GeoServer
     */
    public function index()
    {
        return view('geoserver.index');
    }
}
