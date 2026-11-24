<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:maire,gestionnaire']);
    }

    /**
     * Afficher le tableau de bord de sécurité
     */
    public function dashboard()
    {
        $securityStats = $this->getSecurityStats();
        $recentThreats = $this->getRecentThreats();
        $blockedIPs = $this->getBlockedIPs();
        
        return view('security.dashboard', compact('securityStats', 'recentThreats', 'blockedIPs'));
    }

    /**
     * Obtenir les statistiques de sécurité
     */
    private function getSecurityStats()
    {
        $now = now();
        $last24h = $now->copy()->subHours(24);
        $last7days = $now->copy()->subDays(7);

        return [
            'total_violations_24h' => Log::where('action', 'security_violation')
                ->where('created_at', '>=', $last24h)
                ->count(),
            
            'total_violations_7d' => Log::where('action', 'security_violation')
                ->where('created_at', '>=', $last7days)
                ->count(),
            
            'failed_logins_24h' => Log::where('action', 'login_failed')
                ->where('created_at', '>=', $last24h)
                ->count(),
            
            'successful_logins_24h' => Log::where('action', 'login_success')
                ->where('created_at', '>=', $last24h)
                ->count(),
            
            'unique_ips_24h' => Log::where('created_at', '>=', $last24h)
                ->whereNotNull('ip_address')
                ->distinct('ip_address')
                ->count(),
            
            'blocked_ips_count' => count(Cache::get('blocked_ips', [])),
        ];
    }

    /**
     * Obtenir les menaces récentes
     */
    private function getRecentThreats()
    {
        return Log::whereIn('action', ['security_violation', 'login_failed'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Obtenir les IPs bloquées
     */
    private function getBlockedIPs()
    {
        return Cache::get('blocked_ips', []);
    }

    /**
     * Débloquer une IP
     */
    public function unblockIP(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip'
        ]);

        $ip = $request->input('ip');
        $blockedIPs = Cache::get('blocked_ips', []);
        
        if (($key = array_search($ip, $blockedIPs)) !== false) {
            unset($blockedIPs[$key]);
            Cache::put('blocked_ips', array_values($blockedIPs), now()->addHours(24));
            
            Log::createLog('security_action', [
                'type' => 'ip_unblocked',
                'ip' => $ip,
                'admin_user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'IP débloquée avec succès']);
        }

        return response()->json(['success' => false, 'message' => 'IP non trouvée dans la liste des IPs bloquées']);
    }

    /**
     * Bloquer manuellement une IP
     */
    public function blockIP(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'reason' => 'required|string|max:255'
        ]);

        $ip = $request->input('ip');
        $reason = $request->input('reason');
        
        $blockedIPs = Cache::get('blocked_ips', []);
        
        if (!in_array($ip, $blockedIPs)) {
            $blockedIPs[] = $ip;
            Cache::put('blocked_ips', $blockedIPs, now()->addHours(24));
            
            Log::createLog('security_action', [
                'type' => 'ip_blocked_manually',
                'ip' => $ip,
                'reason' => $reason,
                'admin_user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'IP bloquée avec succès']);
        }

        return response()->json(['success' => false, 'message' => 'Cette IP est déjà bloquée']);
    }

    /**
     * Obtenir les statistiques détaillées
     */
    public function getDetailedStats(Request $request)
    {
        $period = $request->input('period', '24h');
        
        switch ($period) {
            case '24h':
                $since = now()->subHours(24);
                break;
            case '7d':
                $since = now()->subDays(7);
                break;
            case '30d':
                $since = now()->subDays(30);
                break;
            default:
                $since = now()->subHours(24);
        }

        $stats = [
            'violations_by_type' => Log::where('action', 'security_violation')
                ->where('created_at', '>=', $since)
                ->get()
                ->groupBy(function ($log) {
                    $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
                    return $details['type'] ?? 'unknown';
                })
                ->map->count(),
            
            'violations_by_hour' => Log::where('action', 'security_violation')
                ->where('created_at', '>=', $since)
                ->get()
                ->groupBy(function ($log) {
                    return $log->created_at->format('H:00');
                })
                ->map->count(),
            
            'top_attacking_ips' => Log::where('action', 'security_violation')
                ->where('created_at', '>=', $since)
                ->get()
                ->groupBy(function ($log) {
                    $details = is_string($log->details) ? json_decode($log->details, true) : $log->details;
                    return $details['ip'] ?? 'unknown';
                })
                ->map->count()
                ->sortDesc()
                ->take(10),
        ];

        return response()->json($stats);
    }

    /**
     * Nettoyer les anciens logs de sécurité
     */
    public function cleanupLogs()
    {
        $deleted = Log::where('created_at', '<', now()->subDays(30))->delete();
        
        Log::createLog('maintenance', [
            'type' => 'logs_cleanup',
            'deleted_count' => $deleted,
            'admin_user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        return response()->json([
            'success' => true, 
            'message' => "$deleted anciens logs supprimés"
        ]);
    }
} 