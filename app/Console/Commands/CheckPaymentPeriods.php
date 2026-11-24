<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UniteCommerciale;
use App\Models\Paiement;
use App\Models\Log;
use Carbon\Carbon;

class CheckPaymentPeriods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-periods {--alert : Envoyer des alertes pour les retards}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les périodes de paiement et identifie les unités en retard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification des périodes de paiement...');
        
        $unitesEnRetard = $this->checkOverdueUnits();
        
        if ($unitesEnRetard->isEmpty()) {
            $this->info('✅ Aucune unité en retard de paiement.');
            return 0;
        }
        
        $this->warn("⚠️  {$unitesEnRetard->count()} unité(s) en retard de paiement :");
        
        foreach ($unitesEnRetard as $data) {
            $unite = $data['unite'];
            $retard = $data['retard_jours'];
            $periodeManquee = $data['periode_manquee'];
            
            $this->line("📍 {$unite->nom_proprietaire} ({$unite->nature})");
            $this->line("   Zone: {$unite->zone->nom}");
            $this->line("   Taxe: {$unite->taxe->nom} - {$unite->taxe->periodicite}");
            $this->line("   Retard: {$retard} jour(s) - Période manquée: {$periodeManquee}");
            $this->line("   Dernier paiement: " . ($data['dernier_paiement'] ? $data['dernier_paiement']->format('d/m/Y') : 'Jamais'));
            $this->line('');
        }
        
        if ($this->option('alert')) {
            $this->createAlerts($unitesEnRetard);
            $this->info('📧 Alertes créées pour les gestionnaires.');
        }
        
        return 0;
    }
    
    /**
     * Vérifier les unités en retard
     */
    private function checkOverdueUnits()
    {
        $unitesEnRetard = collect();
        $today = Carbon::today();
        
        // Récupérer toutes les unités actives avec une taxe périodique
        $unites = UniteCommerciale::with(['taxe', 'zone', 'paiements'])
            ->where('actif', true)
            ->whereHas('taxe', function($query) {
                $query->whereIn('periodicite', ['jour', 'mois', 'an']);
            })
            ->get();
            
        foreach ($unites as $unite) {
            $retardInfo = $this->calculateOverdue($unite, $today);
            
            if ($retardInfo['en_retard']) {
                $unitesEnRetard->push($retardInfo);
            }
        }
        
        return $unitesEnRetard;
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
            
        // Si jamais payé, considérer comme en retard après 30 jours de recensement
        if (!$dernierPaiement) {
            $dateRecensement = $unite->date_recensement ? Carbon::parse($unite->date_recensement) : $unite->created_at;
            $joursSansPayement = $today->diffInDays($dateRecensement);
            
            $enRetard = $joursSansPayement > 30; // Grace de 30 jours après recensement
            
            return [
                'unite' => $unite,
                'en_retard' => $enRetard,
                'retard_jours' => $enRetard ? $joursSansPayement - 30 : 0,
                'periode_manquee' => $enRetard ? 'Première période' : null,
                'dernier_paiement' => null
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
            'dernier_paiement' => $dateDernierPaiement
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
                return $date->addMonth(); // Par défaut mensuel
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
                return $date->format('m/Y');
            case 'an':
                return $date->format('Y');
            default:
                return $date->format('m/Y');
        }
    }
    
    /**
     * Créer des alertes pour les gestionnaires
     */
    private function createAlerts($unitesEnRetard)
    {
        foreach ($unitesEnRetard as $data) {
            $unite = $data['unite'];
            
            Log::createLog('payment_overdue_alert', [
                'unite_id' => $unite->id,
                'unite_name' => $unite->nom_proprietaire,
                'zone_id' => $unite->zone_id,
                'zone_name' => $unite->zone->nom,
                'taxe_id' => $unite->taxe_id,
                'taxe_name' => $unite->taxe->nom,
                'periodicite' => $unite->taxe->periodicite,
                'retard_jours' => $data['retard_jours'],
                'periode_manquee' => $data['periode_manquee'],
                'dernier_paiement' => $data['dernier_paiement'] ? $data['dernier_paiement']->format('Y-m-d') : null,
                'alerte_date' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }
}
