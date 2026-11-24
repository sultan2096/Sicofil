@extends('layouts.app')

@section('title', 'GeoServer - SIG Intercommunal')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-map"></i> GeoServer - WMS / WFS</h5>
        </div>
        <div class="card-body">
            @if(config('geoserver.enabled'))
            <div class="row">
                <div class="col-md-6">
                    <h6>WMS (Web Map Service)</h6>
                    <p class="text-muted">Rendu cartographique pour overlays</p>
                    <a href="{{ config('geoserver.base_url') }}/wms?service=WMS&version=1.3.0&request=GetCapabilities" target="_blank" class="btn btn-outline-primary btn-sm">
                        GetCapabilities WMS
                    </a>
                </div>
                <div class="col-md-6">
                    <h6>WFS (Web Feature Service)</h6>
                    <p class="text-muted">Données vectorielles GeoJSON</p>
                    <a href="{{ config('geoserver.base_url') }}/wfs?service=WFS&version=2.0.0&request=GetCapabilities" target="_blank" class="btn btn-outline-primary btn-sm">
                        GetCapabilities WFS
                    </a>
                </div>
            </div>
            <hr>
            <p><strong>URL de base:</strong> <code>{{ config('geoserver.base_url') }}</code></p>
            <p><strong>Workspace:</strong> <code>{{ config('geoserver.workspace') }}</code></p>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> GeoServer n'est pas activé.
                <br>Ajoutez <code>GEOSERVER_ENABLED=true</code> et <code>GEOSERVER_BASE_URL=http://localhost:8081/geoserver</code> dans votre fichier .env
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
