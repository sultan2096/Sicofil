<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\Log;
use Carbon\Carbon;

class PaymentAlertController extends Controller
{
    /**
     * Afficher les unités en retard de paiement
     */
    public function index()
    {
        if (!in_array(auth()->user()->role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès non autorisé');
        }
        
        $unitesEnRetard = $this->getOverdueUnits();
        $alertes = $this->getRecentAlerts();
        
        return view('alerts.payment-overdue', compact('unitesEnRetard', 'alertes'));
    }
    
    /**
     * API pour obtenir le nombre d'unités en retard (pour le dashboard)
     */
    public function getOverdueCount()
    {
        $count = $this->getOverdueUnits()->count();
        
        return response()->json([
            'count' => $count,
            'message' => $count > 0 ? "$count unité(s) en retard" : "Aucun retard"
        ]);
    }
    
    /**
     * Marquer une alerte comme vue
     */
    public function markAsSeen($alertId)
    {
        if (!in_array(auth()->user()->role, ['maire', 'gestionnaire'])) {
            abort(403, 'Accès non autorisé');
        }
        
        $log = Log::find($alertId);
        if ($log && $log->action === 'payment_overdue_alert') {
            $details = $log->details;
            $details['seen_by'] = auth()->user()->id;
            $details['seen_at'] = now()->format('Y-m-d H:i:s');
            
            $log->update(['details' => $details]);
        }
        
        return redirect()->back()->with('success', 'Alerte marquée comme vue.');
    }
    
    /**
     * Obtenir les unités en retard.
     * Aligné sur le dashboard maire: unités ayant déjà payé mais pas ce mois.
     * + unités jamais payées (après période de grâce) si taxe périodique.
     */
    private function getOverdueUnits()
    {
        $unitesEnRetard = collect();
        $today = Carbon::today();
        $moisCourant = $today->month;
        $anneeCourante = $today->year;

        // 1) Unités ayant déjà payé mais pas ce mois (définition dashboard maire)
        $unitesAyantPayePasCeMois = UniteCommerciale::with(['taxe', 'zone', 'paiements'])
            ->where('actif', true)
            ->whereHas('paiements', fn($q) => $q->where('statut', 'valide'))
            ->whereDoesntHave('paiements', function($q) use ($moisCourant, $anneeCourante) {
                $q->where('statut', 'valide')
                    ->whereMonth('date_paiement', $moisCourant)
                    ->whereYear('date_paiement', $anneeCourante);
            })
            ->get();

        foreach ($unitesAyantPayePasCeMois as $unite) {
            $dernierPaiement = $unite->paiements()->where('statut', 'valide')->orderBy('date_paiement', 'desc')->first();
            $dateDernier = $dernierPaiement ? Carbon::parse($dernierPaiement->date_paiement) : null;
            $prochaineEcheance = $dateDernier ? $dateDernier->copy()->addMonth() : $today->copy();
            $retardJours = $today->gt($prochaineEcheance) ? $today->diffInDays($prochaineEcheance) : 1;
            $unitesEnRetard->push([
                'unite' => $unite,
                'en_retard' => true,
                'retard_jours' => $retardJours,
                'periode_manquee' => $prochaineEcheance->format('F Y'),
                'dernier_paiement' => $dernierPaiement?->date_paiement,
                'prochaine_echeance' => $prochaineEcheance
            ]);
        }

        // 2) Unités jamais payées (taxe périodique) après période de grâce
        $unitesJamaisPayees = UniteCommerciale::with(['taxe', 'zone', 'paiements'])
            ->where('actif', true)
            ->whereDoesntHave('paiements', fn($q) => $q->where('statut', 'valide'))
            ->whereHas('taxe', fn($q) => $q->whereIn('periodicite', ['jour', 'mois', 'an']))
            ->get();

        foreach ($unitesJamaisPayees as $unite) {
            $dateRecensement = $unite->date_recensement ? Carbon::parse($unite->date_recensement) : $unite->created_at;
            $graceJours = $this->getGracePeriod($unite->taxe?->periodicite ?? 'mois');
            $joursSansPayement = $today->diffInDays($dateRecensement);
            if ($joursSansPayement > $graceJours) {
                $unitesEnRetard->push([
                    'unite' => $unite,
                    'en_retard' => true,
                    'retard_jours' => $joursSansPayement - $graceJours,
                    'periode_manquee' => 'Première période',
                    'dernier_paiement' => null,
                    'prochaine_echeance' => $dateRecensement->copy()->addDays($graceJours)
                ]);
            }
        }

        return $unitesEnRetard->sortByDesc('retard_jours');
    }
    
    /**
     * Calculer le retard pour une unité
     */
    private function calculateOverdue($unite, $today)
    {
        $taxe = $unite->taxe;
        $dernierPaiement = $unite->paiements()
            ->where('statut', 'valide')
            ->orderBy('date_paiement', 'desc')
            ->first();
            
        // Si jamais payé, considérer comme en retard après la période de grâce
        if (!$dernierPaiement) {
            $dateRecensement = $unite->date_recensement ? Carbon::parse($unite->date_recensement) : $unite->created_at;
            $graceJours = $this->getGracePeriod($taxe->periodicite);
            $joursSansPayement = $today->diffInDays($dateRecensement);
            
            $enRetard = $joursSansPayement > $graceJours;
            
            return [
                'unite' => $unite,
                'en_retard' => $enRetard,
                'retard_jours' => $enRetard ? $joursSansPayement - $graceJours : 0,
                'periode_manquee' => $enRetard ? 'Première période' : null,
                'dernier_paiement' => null,
                'prochaine_echeance' => $dateRecensement->copy()->addDays($graceJours)
            ];
        }
        
        // Calculer la prochaine échéance selon la périodicité
        $dateDernierPaiement = $dernierPaiement->date_paiement;
        $prochaineEcheance = $this->calculateNextDueDate($dateDernierPaiement, $taxe->periodicite);
        
        $enRetard = $today->gt($prochaineEcheance);
        $retardJours = $enRetard ? $today->diffInDays($prochaineEcheance) : 0;
        
        return [
            'unite' => $unite,
            'en_retard' => $enRetard,
            'retard_jours' => $retardJours,
            'periode_manquee' => $enRetard ? $this->getPeriodeName($prochaineEcheance, $taxe->periodicite) : null,
            'dernier_paiement' => $dateDernierPaiement,
            'prochaine_echeance' => $prochaineEcheance
        ];
    }
    
    /**
     * Calculer la prochaine date d'échéance
     */
    private function calculateNextDueDate($lastPaymentDate, $periodicite)
    {
        $date = Carbon::parse($lastPaymentDate);
        
        switch ($periodicite) {
            case 'jour':
                return $date->addDay();
            case 'mois':
                return $date->addMonth();
            case 'an':
                return $date->addYear();
            default:
                return $date->addMonth();
        }
    }
    
    /**
     * Obtenir la période de grâce selon la périodicité
     */
    private function getGracePeriod($periodicite)
    {
        switch ($periodicite) {
            case 'jour':
                return 3; // 3 jours de grâce
            case 'mois':
                return 30; // 30 jours de grâce
            case 'an':
                return 365; // 1 an de grâce
            default:
                return 30;
        }
    }
    
    /**
     * Obtenir le nom de la période manquée
     */
    private function getPeriodeName($date, $periodicite)
    {
        switch ($periodicite) {
            case 'jour':
                return $date->format('d/m/Y');
            case 'mois':
                return $date->format('F Y');
            case 'an':
                return $date->format('Y');
            default:
                return $date->format('F Y');
        }
    }
    
    /**
     * Obtenir les alertes récentes
     */
    private function getRecentAlerts()
    {
        return Log::where('action', 'payment_overdue_alert')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
} 