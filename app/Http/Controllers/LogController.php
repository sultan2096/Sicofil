<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display a listing of the logs.
     */
    public function index(Request $request)
    {
        $query = Log::with('user')->latest();

        // Pour les gestionnaires, exclure les logs du maire
        if (auth()->user()->role === 'gestionnaire') {
            $query->whereHas('user', function($q) {
                $q->where('role', '!=', 'maire');
            });
        }

        // Filtres
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $logs = $query->paginate(100);

        // Actions disponibles pour le filtre
        $actions = Log::distinct('action')->pluck('action')->sort();

        // Utilisateurs pour le filtre (excluant le maire pour les gestionnaires)
        $usersQuery = User::orderBy('name');
        if (auth()->user()->role === 'gestionnaire') {
            $usersQuery->where('role', '!=', 'maire');
        }
        $users = $usersQuery->get();

        return view('logs.index', compact('logs', 'actions', 'users'));
    }

    /**
     * Display the specified log.
     */
    public function show(Log $log)
    {
        // Vérifier l'accès pour les gestionnaires
        if (auth()->user()->role === 'gestionnaire' && $log->user->role === 'maire') {
            abort(403, 'Accès non autorisé.');
        }

        return view('logs.show', compact('log'));
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = Log::with('user');

        // Appliquer les mêmes filtres
        if (auth()->user()->role === 'gestionnaire') {
            $query->whereHas('user', function($q) {
                $q->where('role', '!=', 'maire');
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="logs_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // En-têtes
            fputcsv($file, ['Date/Heure', 'Utilisateur', 'Rôle', 'Action', 'IP', 'Détails']);
            
            // Données
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->user->name,
                    $log->user->role,
                    $log->action,
                    $log->ip_address,
                    json_encode($log->details, JSON_UNESCAPED_UNICODE)
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 