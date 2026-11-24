@extends('layouts.app')

@section('title', 'Mettre a jour une unite - SICOFIL')

@section('content')
<style>
/* Mise en page carte / formulaire */
#update-fields > .row {
    align-items: start;
}
#update-fields .col-lg-6:last-child .card {
    max-height: 100%;
}
#update-fields .map-card-wrapper .card-body,
.map-card-wrapper .card-body {
    padding: 0 !important;
    overflow: hidden;
    min-height: 0;
}
/* Carte interactive */
#interactive-map {
    height: 300px !important;
    max-height: 300px !important;
    width: 100% !important;
    min-height: 240px;
    border-radius: 0 0 0.25rem 0.25rem;
}
@media (max-width: 991px) {
    #interactive-map {
        height: 280px !important;
        max-height: 280px !important;
    }
}
/* Dropdown recherche */
.dropdown-suggestions{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #ddd;border-top:none;border-radius:0 0 6px 6px;max-height:300px;overflow-y:auto;z-index:1000;display:none;box-shadow:0 4px 6px rgba(0,0,0,.1)}
.suggestion-item{padding:12px 15px;cursor:pointer;border-bottom:1px solid #f1f1f1;transition:background-color .2s}
.suggestion-item:hover{background-color:#f8f9fa}
.suggestion-item.selected{background-color:#e3f2fd;border-left:3px solid #2196f3}
.selected-indicator{position:absolute;top:0;left:0;right:0;bottom:0;background:#f0f9ff;border:2px solid #10b981;border-radius:6px;padding:8px 35px 8px 12px;display:flex;align-items:center;font-size:14px;color:#065f46}
.btn-clear{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b7280;padding:4px 6px;border-radius:50%;cursor:pointer;font-size:12px}
.btn-clear:hover{background-color:#fee2e2;color:#dc2626}
.form-section-disabled{opacity:.6;pointer-events:none}
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary mb-1"><i class="fas fa-edit"></i> Mettre a jour une unite commerciale</h2>
            <p class="text-muted mb-0">Selectionnez une unite existante, recapturez sa position GPS et modifiez ses informations</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('collecteur.unites.update-submit') }}" enctype="multipart/form-data" id="update-form" onsubmit="return validateGPSBeforeSubmit()">
        @csrf
        <input type="hidden" name="gps_recaptured" id="gps-recaptured" value="0">
        <input type="hidden" name="latitude" id="form-latitude" value="">
        <input type="hidden" name="longitude" id="form-longitude" value="">
        <input type="hidden" name="altitude" id="form-altitude" value="">
        <input type="hidden" name="precision" id="form-precision" value="">

        <div class="card border-warning shadow-sm mb-4">
            <div class="card-header bg-warning text-dark"><h5 class="mb-0"><i class="fas fa-search"></i> Unite commerciale a mettre a jour <span class="text-danger">*</span></h5></div>
            <div class="card-body">
                <div class="mb-3 position-relative">
                    <label for="unite-search" class="form-label">Rechercher par nom, prenom ou type de commerce...</label>
                    <input type="text" class="form-control mb-2 @error('unite_id') is-invalid @enderror" id="unite-search" placeholder="Tapez pour rechercher..." autocomplete="off">
                    <select class="form-select @error('unite_id') is-invalid @enderror" id="unite_id" name="unite_id" required>
                        <option value="">-- Selectionnez une unite --</option>
                        @foreach($unites as $u)
                        <option value="{{ $u->id }}" data-search="{{ strtolower($u->nom_proprietaire.' '.$u->prenom_proprietaire.' '.$u->type_commerce.' '.$u->adresse) }}">
                            {{ $u->nom_complet_proprietaire }} - {{ $u->type_commerce }} @if($u->adresse) ({{ $u->adresse }}) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('unite_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if($unites->isEmpty())
                        <div class="form-text text-warning mt-2"><i class="fas fa-exclamation-triangle"></i> Aucune unite commerciale disponible dans votre zone</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Carte interactive toujours visible -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4 map-card-wrapper">
                <div class="card border-info shadow-sm h-100">
                    <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-map"></i> Carte interactive</h5></div>
                    <div class="card-body p-0"><div id="interactive-map"></div></div>
                    <div class="card-footer text-center bg-light"><small class="text-muted"><i class="fas fa-info-circle"></i> Deplacez le marqueur rouge pour ajuster votre position</small></div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-crosshairs"></i> Recapture de Position GPS</h5></div>
                    <div class="card-body">
                        <div class="alert alert-info"><h6><i class="fas fa-info-circle"></i> Instructions</h6><ul class="mb-0"><li>Placez-vous devant l'unite commerciale</li><li>Assurez-vous d'avoir une bonne reception GPS</li><li>Autorisez l'acces a votre position si demande</li></ul></div>
                        <div id="gps-status" class="alert alert-secondary" style="display:none"></div>
                        <div class="d-grid gap-3">
                            <button type="button" class="btn btn-success btn-lg" id="capture-gps-btn" disabled><i class="fas fa-crosshairs"></i> Selectionnez d'abord une unite</button>
                        </div>
                        <div id="position-info" class="mt-4" style="display:none">
                            <div class="card bg-light">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Position capturee</h6></div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6"><label class="form-label text-muted">Latitude</label><div class="fw-bold text-primary" id="current-latitude">-</div></div>
                                        <div class="col-6"><label class="form-label text-muted">Longitude</label><div class="fw-bold text-primary" id="current-longitude">-</div></div>
                                    </div>
                                    <div class="mt-2"><small class="text-muted">Precision: <span id="current-accuracy">-</span>m | Methode: <span id="current-method">-</span></small></div>
                                </div>
                            </div>
                        </div>
                        <div id="continue-section" class="mt-4" style="display:none">
                            <div class="d-grid"><button type="button" class="btn btn-primary btn-lg" id="continue-to-form-btn"><i class="fas fa-arrow-right"></i> Continuer vers le formulaire</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="update-fields" class="form-section-disabled">
            <div id="editable-form" style="display:none">
                <div class="card border-primary shadow-sm mb-4">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informations de base</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categorie" class="form-label">Categorie d'Unite Commerciale <span class="text-danger">*</span></label>
                                    <select class="form-select @error('categorie') is-invalid @enderror" id="categorie" name="categorie" required>
                                        <option value="">Selectionnez une categorie</option>
                                        <option value="activite_commerciale">Activite commerciale</option>
                                        <option value="point_interet">Point d'interet</option>
                                    </select>
                                    @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nature" class="form-label">Nature de l'Activite Commerciale ou du Point d'Interet <span class="text-danger">*</span></label>
                                    <select class="form-select @error('nature') is-invalid @enderror" id="nature" name="nature" required>
                                        <option value="">Selectionner la nature</option>
                                    </select>
                                    @error('nature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sous_type_commerce" class="form-label">Type de Commerce ou du Point d'Interet <span class="text-danger">*</span></label>
                                    <select class="form-select @error('sous_type_commerce') is-invalid @enderror" id="sous_type_commerce" name="sous_type_commerce" required>
                                        <option value="">Selectionner le type</option>
                                    </select>
                                    @error('sous_type_commerce')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-primary shadow-sm mb-4">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-user me-2"></i> Informations du proprietaire</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom_proprietaire" class="form-label">Nom du proprietaire <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nom_proprietaire') is-invalid @enderror" id="nom_proprietaire" name="nom_proprietaire" required>
                                    @error('nom_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prenom_proprietaire" class="form-label">Prenom du proprietaire <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('prenom_proprietaire') is-invalid @enderror" id="prenom_proprietaire" name="prenom_proprietaire" required>
                                    @error('prenom_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sexe_proprietaire" class="form-label">Sexe</label>
                                    <select class="form-select @error('sexe_proprietaire') is-invalid @enderror" id="sexe_proprietaire" name="sexe_proprietaire">
                                        <option value="">Non specifie</option>
                                        <option value="masculin">Masculin</option>
                                        <option value="feminin">Feminin</option>
                                    </select>
                                    @error('sexe_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Telephone</label>
                                    <input type="tel" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" maxlength="9" pattern="[0-9]{9}" placeholder="77 123 45 67">
                                    @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="email@exemple.com">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nin_proprietaire" class="form-label">NIN du proprietaire</label>
                                    <input type="text" class="form-control @error('nin_proprietaire') is-invalid @enderror" id="nin_proprietaire" name="nin_proprietaire" maxlength="13" pattern="[0-9]{0,13}" placeholder="13 chiffres maximum">
                                    @error('nin_proprietaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ninea" class="form-label" id="ninea-label">NINEA</label>
                                    <input type="text" class="form-control @error('ninea') is-invalid @enderror" id="ninea" name="ninea" placeholder="Numero d'identification des entreprises">
                                    <div class="form-text" id="ninea-help">Pour les entreprises</div>
                                    @error('ninea')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Photo de l'unite commerciale <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" capture="camera" required>
                                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">Photo obligatoire de l'unite commerciale</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-primary shadow-sm mb-4">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Adresse</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse complete</label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="2" placeholder="Adresse detaillee de l'unite commerciale..."></textarea>
                            @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('collecteur.dashboard') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Enregistrer la mise a jour</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Leaflet CSS et JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
const unitesData = @json($unitesData);
let currentPosition=null,interactiveMap=null,currentMarker=null;

document.addEventListener('DOMContentLoaded',function(){
    const uniteSelect=document.getElementById('unite_id');
    const uniteSearch=document.getElementById('unite-search');
    const updateFields=document.getElementById('update-fields');
    const gpsBtn=document.getElementById('capture-gps-btn');

    // Initialiser la carte au chargement (centre par defaut) apres un court delai pour le rendu DOM
    setTimeout(function(){initMap();},300);

    if(uniteSearch&&uniteSelect){
        uniteSearch.addEventListener('input',function(){
            const term=this.value.toLowerCase().trim();
            const options=uniteSelect.querySelectorAll('option');
            let firstVisible=null;
            options.forEach(function(option,index){
                if(index===0)return;
                const searchData=(option.dataset.search||'').toLowerCase();
                const text=option.textContent.toLowerCase();
                if(!term||searchData.includes(term)||text.includes(term)){option.style.display='';if(!firstVisible)firstVisible=option;}else{option.style.display='none';}
            });
            uniteSelect.size=(term.length>0&&firstVisible)?Math.min(10,uniteSelect.options.length):1;
        });

        uniteSelect.addEventListener('change',function(){
            uniteSelect.size=1;
            uniteSearch.value='';
            uniteSelect.querySelectorAll('option').forEach(function(o){o.style.display='';});
            const uniteId=uniteSelect.value;
            if(uniteId){
                const data=unitesData.find(u=>u.id==uniteId);
                if(data){
                    prefillForm(data);
                    updateFields.classList.remove('form-section-disabled');
                    // Activer le bouton GPS et recentrer la carte
                    gpsBtn.disabled=false;
                    gpsBtn.innerHTML='<i class="fas fa-crosshairs"></i> Capturer ma position GPS';
                    initMap(data);
                }
            }else{
                updateFields.classList.add('form-section-disabled');
                document.getElementById('editable-form').style.display='none';
                gpsBtn.disabled=true;
                gpsBtn.innerHTML='<i class="fas fa-crosshairs"></i> Selectionnez d\'abord une unite';
                initMap();
            }
        });
        uniteSelect.addEventListener('click',function(){if(uniteSelect.size>1)uniteSelect.size=1;});
    }

    gpsBtn.addEventListener('click',captureGPS);
    document.getElementById('continue-to-form-btn').addEventListener('click',function(){
        if(!currentPosition){alert("Veuillez d'abord capturer votre position GPS.");return;}
        document.getElementById('form-latitude').value=currentPosition.lat.toFixed(6);
        document.getElementById('form-longitude').value=currentPosition.lng.toFixed(6);
        document.getElementById('form-altitude').value=currentPosition.altitude||'';
        document.getElementById('form-precision').value=currentPosition.accuracy||'';
        document.getElementById('gps-recaptured').value='1';
        document.getElementById('editable-form').style.display='block';
        window.scrollTo({top:document.getElementById('editable-form').offsetTop-20,behavior:'smooth'});
    });

    document.getElementById('nin_proprietaire').addEventListener('input',function(){this.value=this.value.replace(/[^0-9]/g,'').substring(0,13);});
    document.getElementById('telephone').addEventListener('input',function(){this.value=this.value.replace(/[^0-9]/g,'').substring(0,9);});

    @if(old('unite_id'))
    const oldId='{{ old('unite_id') }}';
    const oldData=unitesData.find(u=>u.id==oldId);
    if(oldData){
        uniteSelect.value=oldId;
        prefillForm(oldData);
        updateFields.classList.remove('form-section-disabled');
        gpsBtn.disabled=false;
        gpsBtn.innerHTML='<i class="fas fa-crosshairs"></i> Capturer ma position GPS';
        initMap(oldData);
        document.getElementById('editable-form').style.display='block';
        @if(old('gps_recaptured')=='1')
        document.getElementById('gps-recaptured').value='1';
        @endif
    }
    @endif
});

function prefillForm(data){
    document.getElementById('categorie').value=data.categorie||'';
    populateNature(data.categorie||'');
    document.getElementById('nature').value=data.nature||'';
    populateTypeByNature(data.nature||'');
    document.getElementById('sous_type_commerce').value=data.sous_type_commerce||'';
    document.getElementById('nom_proprietaire').value=data.nom_proprietaire||'';
    document.getElementById('prenom_proprietaire').value=data.prenom_proprietaire||'';
    document.getElementById('sexe_proprietaire').value=data.sexe_proprietaire||'';
    document.getElementById('telephone').value=data.telephone||'';
    document.getElementById('email').value=data.email||'';
    document.getElementById('nin_proprietaire').value=data.nin_proprietaire||'';
    document.getElementById('ninea').value=data.ninea||'';
    document.getElementById('adresse').value=data.adresse||'';

    document.getElementById('form-latitude').value=data.latitude||'';
    document.getElementById('form-longitude').value=data.longitude||'';
    document.getElementById('form-altitude').value=data.altitude||'';
    document.getElementById('form-precision').value=data.precision||'';
    document.getElementById('gps-recaptured').value='0';
}

function initMap(data){
    try{
        const defLat=parseFloat(data?.latitude)||14.435400;
        const defLng=parseFloat(data?.longitude)||-16.791530;
        if(!interactiveMap){
            interactiveMap=L.map('interactive-map').setView([defLat,defLng],16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'OpenStreetMap',maxZoom:19}).addTo(interactiveMap);
            // Forcer Leaflet a recalculer la taille apres le rendu DOM
            setTimeout(function(){interactiveMap.invalidateSize();},200);
        }else{
            interactiveMap.setView([defLat,defLng],16);
            setTimeout(function(){interactiveMap.invalidateSize();},200);
        }
        if(currentMarker)interactiveMap.removeLayer(currentMarker);
        if(data?.latitude&&data?.longitude){
            const ri=L.divIcon({className:'custom-red-marker',html:'<div style="background:#dc3545;width:20px;height:20px;border-radius:50%;border:3px solid #fff;box-shadow:0 3px 6px rgba(0,0,0,.3)"></div>',iconSize:[20,20],iconAnchor:[10,10]});
            currentMarker=L.marker([defLat,defLng],{icon:ri,draggable:true}).addTo(interactiveMap);
        }
    }catch(e){console.error('carte:',e);}
}

function captureGPS(){
    if(!navigator.geolocation){showStatus('error','Geolocalisation non supportee');return;}
    const btn=document.getElementById('capture-gps-btn');
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Capture en cours...';btn.disabled=true;
    showStatus('info','Recherche de votre position...');
    navigator.geolocation.getCurrentPosition(
        function(pos){
            currentPosition={lat:pos.coords.latitude,lng:pos.coords.longitude,accuracy:pos.coords.accuracy,altitude:pos.coords.altitude||null,method:pos.coords.accuracy<50?'GPS/WiFi':'Reseau (approximatif)'};
            document.getElementById('current-latitude').textContent=currentPosition.lat.toFixed(6);
            document.getElementById('current-longitude').textContent=currentPosition.lng.toFixed(6);
            document.getElementById('current-accuracy').textContent=Math.round(currentPosition.accuracy);
            document.getElementById('current-method').textContent=currentPosition.method;
            document.getElementById('position-info').style.display='block';
            showStatus('success','Position GPS capturee avec succes !');
            btn.innerHTML='<i class="fas fa-check"></i> Position capturee';btn.classList.remove('btn-success');btn.classList.add('btn-outline-success');btn.disabled=false;
            document.getElementById('continue-section').style.display='block';
            if(interactiveMap){
                if(currentMarker)interactiveMap.removeLayer(currentMarker);
                interactiveMap.setView([currentPosition.lat,currentPosition.lng],17);
                const ri=L.divIcon({className:'custom-red-marker',html:'<div style="background:#dc3545;width:20px;height:20px;border-radius:50%;border:3px solid #fff;box-shadow:0 3px 6px rgba(0,0,0,.3)"></div>',iconSize:[20,20],iconAnchor:[10,10]});
                currentMarker=L.marker([currentPosition.lat,currentPosition.lng],{icon:ri,draggable:true}).addTo(interactiveMap).bindPopup('<strong>Position de l\'unite</strong>').openPopup();
                currentMarker.on('dragend',function(e){const np=e.target.getLatLng();currentPosition.lat=np.lat;currentPosition.lng=np.lng;currentPosition.method='Ajuste manuellement';document.getElementById('current-latitude').textContent=np.lat.toFixed(6);document.getElementById('current-longitude').textContent=np.lng.toFixed(6);});
            }
        },
        function(err){
            let msg='Erreur geolocalisation';
            if(err.code===err.PERMISSION_DENIED)msg='Autorisation refusee. Verifiez les parametres du navigateur.';
            else if(err.code===err.POSITION_UNAVAILABLE)msg='Position indisponible. Verifiez que le GPS est active.';
            else if(err.code===err.TIMEOUT)msg='Delai depasse. Essayez en zone degagee.';
            showStatus('error',msg);
            btn.innerHTML='<i class="fas fa-crosshairs"></i> Reessayer la capture GPS';btn.disabled=false;
        },
        {enableHighAccuracy:true,timeout:15000,maximumAge:300000}
    );
}

function showStatus(type,message){
    const el=document.getElementById('gps-status');
    el.className='alert alert-'+(type==='success'?'success':type==='error'?'danger':'info');
    el.innerHTML=message;el.style.display='block';
}

// --- Synchronisation Nature / Type de commerce ---
const data = {
  "Boutique ou Magasin": ["Alimentation générale", "Grossiste", "Mercerie", "Quincaillerie", "Prêt-à-porter", "Cosmétique"],
  "Atelier": ["Mécanique", "Couture", "Forge", "Cordonnerie", "Menuiserie", "Boulangerie"],
  "Forail": ["Chevaux", "Ânes", "Vaches", "Moutons", "Chèvres"],
  "Parcelle agricole": ["Petites exploitations agricoles (celui des particuliers ou l'agriculture familiale)"],
  "Stationnement": ["Bus", "Camion-citerne de carburant", "Camionnette", "Calèche", "Camion gros porteur", "Taxi clando", "Moto Jakarta", "Car"],
  "Étal": ["Vente sur étal (sans distinction spécifique)"],
  "Table ou Tente commerciale": ["Vente sur table ou sous tente (sans distinction spécifique)"],
  "Cantine ou Kiosque": ["Restaurant", "Gargote", "Dibiterie", "Fast-Food"],
  "Entreprise ou Service": ["Entrepôt", "Grande exploitation agricole (celui des entreprises)", "Assurance", "Banque", "Multiservice", "Supermarché", "Station-service"],
  "Foncier": ["Location de terrain", "Location de bâtiments"],
  "Publicité": ["Véhicules publicitaires", "Panneaux publicitaires"],
  "Spectacles": ["Lieux dédiés aux événements et spectacles"]
};
const natureCategorieMap = {
  "activite_commerciale": [
    "Boutique ou Magasin", "Atelier", "Forail", "Parcelle agricole", "Stationnement", "Étal", "Table ou Tente commerciale", "Cantine ou Kiosque", "Entreprise ou Service"
  ],
  "point_interet": [
    "Foncier", "Publicité", "Spectacles"
  ]
};
function populateNature(selectedCat) {
  const natureSelect = document.getElementById('nature');
  if (!natureSelect) return;
  natureSelect.innerHTML = '<option value="">Selectionner la nature</option>';
  let natures = Object.keys(data);
  if (selectedCat && natureCategorieMap[selectedCat]) {
    natures = natureCategorieMap[selectedCat];
  }
  natures.forEach(nature => {
    const opt = document.createElement('option');
    opt.value = nature;
    opt.textContent = nature;
    natureSelect.appendChild(opt);
  });
}
function populateTypeByNature(natureValue) {
  const typeSelect = document.getElementById('sous_type_commerce');
  if (!typeSelect) return;
  typeSelect.value = '';
  typeSelect.innerHTML = '<option value="">Selectionner le type</option>';
  if (natureValue && data[natureValue] && data[natureValue].length > 0) {
    data[natureValue].forEach(type => {
      const opt = document.createElement('option');
      opt.value = type;
      opt.textContent = type;
      typeSelect.appendChild(opt);
    });
    typeSelect.disabled = false;
  } else {
    typeSelect.disabled = true;
  }
}
// Initialiser les listeners
document.addEventListener('DOMContentLoaded', function() {
  const categorieSelect = document.getElementById('categorie');
  const natureSelect = document.getElementById('nature');
  if (categorieSelect) {
    categorieSelect.addEventListener('change', function() {
      populateNature(this.value);
      populateTypeByNature('');
      document.getElementById('nature').value = '';
    });
  }
  if (natureSelect) {
    natureSelect.addEventListener('change', function() {
      populateTypeByNature(this.value);
    });
  }
});

function validateGPSBeforeSubmit(){
    const recaptured=document.getElementById('gps-recaptured').value;
    const lat=document.getElementById('form-latitude').value;
    const lng=document.getElementById('form-longitude').value;
    if(!document.getElementById('unite_id').value){alert('Veuillez selectionner une unite commerciale.');return false;}
    if(recaptured!=='1'){alert("Veuillez d'abord recapturer la position GPS avant d'enregistrer.");return false;}
    if(!lat||!lng){alert("Veuillez d'abord capturer la position GPS.");return false;}
    return true;
}

</script>
@endsection
