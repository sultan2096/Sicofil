@extends('layouts.app')

@section('title', 'Gestion multi-communes - SICOFIL')

@push('styles')
    <style>
        .sicofil-hero {
            background: linear-gradient(135deg, #0ea5e9 0%, #4f46e5 55%, #8b5cf6 100%);
            border-radius: 18px;
            color: #fff;
            padding: 1.25rem 1.25rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
        }
        .sicofil-hero-logo {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.92);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.18);
        }
        .sicofil-hero-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .sicofil-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.22);
            padding: .25rem .6rem;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: .04em;
            font-size: .75rem;
        }
        .kpi-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10);
            overflow: hidden;
            height: 100%;
        }
        .kpi-card .card-body { padding: 1rem 1rem; }
        .kpi-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }
        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex: 0 0 auto;
        }
        .kpi-label { font-size: .8rem; color: rgba(15,23,42,.65); margin-bottom: .15rem; }
        .kpi-value { font-size: 1.35rem; font-weight: 800; line-height: 1.2; color: #0f172a; }
        .kpi-sub { font-size: .75rem; color: rgba(15,23,42,.55); }

        .card-soft {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.10);
        }
        .card-soft .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(148,163,184,.35);
            padding: .9rem 1rem;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }
        .card-soft .card-body { padding: 1rem 1rem; }

        .table thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
        .table tbody td { vertical-align: middle; }
        /* Tableau Communes : défilement horizontal pour voir toutes les colonnes (Logo → Recettes (FCFA) → actions) */
        .communes-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .communes-table-wrap .table { min-width: 960px; }
        .commune-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: .78rem;
            background: rgba(148,163,184,.18);
            padding: .15rem .4rem;
            border-radius: 8px;
        }

        @media (max-width: 576px) {
            .kpi-value { font-size: 1.2rem; }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="sicofil-hero mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="sicofil-hero-logo">
                    <img src="{{ asset('images/logos/sicofil.png') }}" alt="Logo SICOFIL" onerror="this.onerror=null; this.src='{{ asset('images/logos/police.PNG') }}';">
                </div>
                <div>
                    <div class="sicofil-badge mb-2">
                        <i class="fas fa-layer-group"></i> SICOFIL
                    </div>
                    <h1 class="h4 mb-1 fw-bold">Gestion multi-communes</h1>
                    <div class="opacity-90">Tableau de bord global — toutes les communes et données</div>
                    <div class="opacity-75 small mt-1">
                        <i class="fas fa-clock me-1"></i>
                        Mis à jour le {{ ($generationDate ?? now())->format('d/m/Y à H:i') }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('communes.create') }}" class="btn btn-light fw-semibold">
                    <i class="fas fa-plus me-1"></i> Ajouter une commune
                </a>
                @if(session('commune_scope_id'))
                    <form method="POST" action="{{ route('communes.scope.clear') }}">
                        @csrf
                        <button type="submit" class="btn btn-warning fw-semibold">
                            <i class="fas fa-layer-group me-1"></i> Quitter le scope
                        </button>
                    </form>
                @endif
                <a href="{{ route('communes.index') }}" class="btn btn-outline-light fw-semibold">
                    <i class="fas fa-rotate me-1"></i> Actualiser
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-row">
                        <div>
                            <div class="kpi-label">Communes actives</div>
                            <div class="kpi-value text-primary">{{ $stats['total_communes'] ?? 0 }}</div>
                            <div class="kpi-sub">Communes disponibles</div>
                        </div>
                        <div class="kpi-icon" style="background:#2563eb;">
                            <i class="fas fa-city"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-row">
                        <div>
                            <div class="kpi-label">Unités commerciales</div>
                            <div class="kpi-value">{{ number_format($stats['total_unites'] ?? 0, 0, ',', ' ') }}</div>
                            <div class="kpi-sub">UC recensées</div>
                        </div>
                        <div class="kpi-icon" style="background:#0ea5e9;">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-row">
                        <div>
                            <div class="kpi-label">Paiements valides</div>
                            <div class="kpi-value">{{ number_format($stats['total_paiements'] ?? 0, 0, ',', ' ') }}</div>
                            <div class="kpi-sub">Total validé</div>
                        </div>
                        <div class="kpi-icon" style="background:#f59e0b;">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-row">
                        <div>
                            <div class="kpi-label">Recettes totales</div>
                            <div class="kpi-value text-success">{{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }}</div>
                            <div class="kpi-sub">FCFA collectés</div>
                        </div>
                        <div class="kpi-icon" style="background:#16a34a;">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-row">
                        <div>
                            <div class="kpi-label">Maires</div>
                            <div class="kpi-value">{{ $stats['total_maires'] ?? 0 }}</div>
                            <div class="kpi-sub">Comptes actifs</div>
                        </div>
                        <div class="kpi-icon" style="background:#6366f1;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card kpi-card">
                <div class="card-body">
                    <div class="kpi-row">
                        <div>
                            <div class="kpi-label">Gestionnaires</div>
                            <div class="kpi-value">{{ $stats['total_gestionnaires'] ?? 0 }}</div>
                            <div class="kpi-sub">Comptes actifs</div>
                        </div>
                        <div class="kpi-icon" style="background:#8b5cf6;">
                            <i class="fas fa-user-gear"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card card-soft h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-slate-800">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Recettes (12 derniers mois)
                    </div>
                    <div class="small text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        {{ now()->subMonths(11)->format('m/Y') }} → {{ now()->format('m/Y') }}
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartMonthly" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-soft h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="fw-bold">
                        <i class="fas fa-users me-2 text-indigo-600"></i>Utilisateurs
                    </div>
                    <div class="small text-muted">Répartition</div>
                </div>
                <div class="card-body">
                    <canvas id="chartUsers" height="180"></canvas>
                    <div class="mt-3 small text-muted">
                        <div class="d-flex justify-content-between"><span>Maires</span><strong class="text-dark">{{ $stats['total_maires'] ?? 0 }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Gestionnaires</span><strong class="text-dark">{{ $stats['total_gestionnaires'] ?? 0 }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Collecteurs</span><strong class="text-dark">{{ $stats['total_collecteurs'] ?? 0 }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card card-soft h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="fw-bold">
                        <i class="fas fa-chart-column me-2 text-success"></i>Recettes par commune
                    </div>
                    <div class="small text-muted">FCFA</div>
                </div>
                <div class="card-body">
                    <canvas id="chartByCommune" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-soft h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="fw-bold">
                        <i class="fas fa-trophy me-2 text-primary"></i>Classement (recettes)
                    </div>
                    <a href="{{ route('communes.map') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-map me-1"></i>Voir la carte
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $sorted = $communes->sortByDesc(fn($c) => (float) ($c->total_recettes ?? 0));
                        $maxRecettes = max(1, (float) ($sorted->first()->total_recettes ?? 0));
                    @endphp

                    @forelse($sorted->take(6) as $c)
                        @php
                            $rec = (float) ($c->total_recettes ?? 0);
                            $pct = min(100, max(0, ($rec / $maxRecettes) * 100));
                        @endphp
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $c->nom }}</div>
                                <div class="small text-muted">
                                    {{ number_format($rec, 0, ',', ' ') }} FCFA • {{ $c->zones_fiscales_count }} zones • {{ $c->unites_commerciales_count }} UC
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">{{ $c->users_count }} utilisateurs</span>
                        </div>
                        <div class="progress mt-2 mb-3" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background: linear-gradient(90deg, #10b981, #4f46e5);"></div>
                        </div>
                    @empty
                        <div class="text-muted">Aucune commune.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card card-soft h-100">
                <div class="card-header">
                    <div class="fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Analyse rapide</div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Commune la plus performante</div>
                        <div class="fw-bold">
                            {{ $insights['top_commune_nom'] ?? '—' }}
                        </div>
                        <div class="text-success small">
                            {{ number_format($insights['top_commune_recettes'] ?? 0, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Moyenne recette / paiement</div>
                        <div class="fw-bold">
                            {{ number_format($insights['avg_recette_par_paiement'] ?? 0, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small">Moyenne recette / unité</div>
                        <div class="fw-bold">
                            {{ number_format($insights['avg_recette_par_unite'] ?? 0, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-soft">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="fw-bold"><i class="fas fa-list me-2"></i>Communes</div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="max-width: 320px;">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input id="communesSearch" type="text" class="form-control" placeholder="Rechercher une commune…">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive communes-table-wrap">
                        <table class="table table-hover align-middle mb-0" id="communesTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 52px;">Logo</th>
                                    <th>Commune</th>
                                    <th>Code</th>
                                    <th>Région / Département</th>
                                    <th class="text-center">Zones</th>
                                    <th class="text-center">UC</th>
                                    <th class="text-center">Utilisateurs</th>
                                    <th class="text-end" style="min-width: 120px;">Recettes (FCFA)</th>
                                    <th style="min-width: 180px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($communes as $c)
                                    @php
                                        $search = strtolower(trim(($c->nom ?? '') . ' ' . ($c->code ?? '') . ' ' . ($c->region ?? '') . ' ' . ($c->departement ?? '')));
                                    @endphp
                                    <tr data-search="{{ $search }}">
                                        <td>
                                            @if($c->logo_url)
                                                <img src="{{ $c->logo_url }}" alt="Logo {{ $c->nom }}" class="rounded commune-logo-img" style="height: 36px; width: 36px; object-fit: contain;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-muted commune-logo-fallback" style="height: 36px; width: 36px; display: none;"><i class="fas fa-city"></i></span>
                                            @else
                                                <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-muted" style="height: 36px; width: 36px;"><i class="fas fa-city"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('communes.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->nom }}</a>
                                            @if(!$c->actif)
                                                <span class="badge bg-secondary ms-2">Inactif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="commune-code">{{ $c->code ?? '—' }}</span>
                                        </td>
                                        <td class="small text-muted">{{ $c->region ?? '—' }} / {{ $c->departement ?? '—' }}</td>
                                        <td class="text-center">{{ $c->zones_fiscales_count }}</td>
                                        <td class="text-center">{{ $c->unites_commerciales_count }}</td>
                                        <td class="text-center">{{ $c->users_count }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($c->total_recettes ?? 0, 0, ',', ' ') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('communes.show', $c) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                            <a href="{{ route('communes.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                            <form method="POST" action="{{ route('communes.scope.set', $c) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Scope</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Aucune commune.
                                            <a href="{{ route('communes.create') }}">Ajouter une commune</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        (function () {
            const charts = @json($charts ?? []);

            const formatFcfa = (n) => {
                try {
                    return new Intl.NumberFormat('fr-FR').format(Number(n || 0));
                } catch (e) {
                    return String(n || 0);
                }
            };

            // --- Chart: Monthly ---
            const monthlyEl = document.getElementById('chartMonthly');
            if (monthlyEl && charts.monthly) {
                new Chart(monthlyEl, {
                    type: 'line',
                    data: {
                        labels: charts.monthly.labels || [],
                        datasets: [{
                            label: 'Recettes (FCFA)',
                            data: charts.monthly.totals || [],
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.15)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${formatFcfa(ctx.parsed.y)} FCFA`
                                }
                            }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => `${formatFcfa(value)}`
                                },
                                grid: { color: 'rgba(148,163,184,.25)' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // --- Chart: Users ---
            const usersEl = document.getElementById('chartUsers');
            if (usersEl && charts.users) {
                new Chart(usersEl, {
                    type: 'doughnut',
                    data: {
                        labels: charts.users.labels || [],
                        datasets: [{
                            data: charts.users.totals || [],
                            backgroundColor: ['#6366f1', '#8b5cf6', '#0ea5e9'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        plugins: { legend: { position: 'bottom' } },
                        cutout: '62%'
                    }
                });
            }

            // --- Chart: By commune ---
            const byCommuneEl = document.getElementById('chartByCommune');
            if (byCommuneEl && charts.by_commune) {
                new Chart(byCommuneEl, {
                    type: 'bar',
                    data: {
                        labels: charts.by_commune.labels || [],
                        datasets: [{
                            label: 'Recettes (FCFA)',
                            data: charts.by_commune.totals || [],
                            backgroundColor: charts.by_commune.colors || '#10b981',
                            borderRadius: 10,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                ticks: { callback: (value) => `${formatFcfa(value)}` },
                                grid: { color: 'rgba(148,163,184,.25)' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // --- Search table ---
            const searchEl = document.getElementById('communesSearch');
            const tableEl = document.getElementById('communesTable');
            if (searchEl && tableEl) {
                const rows = Array.from(tableEl.querySelectorAll('tbody tr'));
                searchEl.addEventListener('input', () => {
                    const q = (searchEl.value || '').toLowerCase().trim();
                    rows.forEach((tr) => {
                        const hay = (tr.getAttribute('data-search') || '');
                        tr.style.display = hay.includes(q) ? '' : 'none';
                    });
                });
            }
        })();
    </script>
@endpush
