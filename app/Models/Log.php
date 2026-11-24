<?php

namespace App\Models;

use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory, AppliesCommuneScope;

    protected $fillable = [
        'user_id',
        'commune_id',
        'action',
        'ip_address',
        'user_agent',
        'details',
        'commentaire',
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Créer un log
     */
    public static function createLog($action, $details = null, $commentaire = null, $userId = null)
    {
        // Pour les actions avant authentification (login_attempt, login_failed, etc.)
        // user_id peut être null
        $finalUserId = $userId ?? auth()->id();
        $finalCommuneId = auth()->check() ? auth()->user()->commune_id : null;
        
        return static::create([
            'user_id' => $finalUserId, // Peut être null pour les tentatives de connexion
            'commune_id' => $finalCommuneId,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
            'commentaire' => $commentaire,
        ]);
    }

    /**
     * Scope pour filtrer par action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope pour filtrer par utilisateur
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour exclure les logs du maire pour l'admin
     */
    public function scopeExcludeMaire($query)
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $query->whereHas('user', function($q) {
                $q->where('role', '!=', 'maire');
            });
        }
        return $query;
    }
} 