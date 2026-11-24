<?php

namespace App\Models;

use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle Contribuable - Portail Public
 * Permet aux commerçants de consulter leurs taxes et payer en ligne
 */
class Contribuable extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AppliesCommuneScope;

    protected $table = 'contribuables';

    protected $fillable = [
        'commune_id',
        'unite_commerciale_id',
        'telephone',
        'email',
        'nom_complet',
        'password',
        'otp_code',
        'otp_expires_at',
        'verified_at',
        'actif',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'otp_code',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'actif' => 'boolean',
    ];

    /**
     * Relation avec la commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Relation avec l'unité commerciale
     */
    public function uniteCommerciale()
    {
        return $this->belongsTo(UniteCommerciale::class);
    }

    /**
     * Historique des paiements
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'contribuable_id');
    }

    /**
     * Vérifier si le code OTP est valide
     */
    public function isValidOtp($code)
    {
        return $this->otp_code === $code 
            && $this->otp_expires_at 
            && $this->otp_expires_at->isFuture();
    }

    /**
     * Générer un nouveau code OTP
     */
    public function generateOtp()
    {
        $this->otp_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_expires_at = now()->addMinutes(10);
        $this->save();

        return $this->otp_code;
    }

    /**
     * Marquer comme vérifié
     */
    public function markAsVerified()
    {
        $this->verified_at = now();
        $this->otp_code = null;
        $this->otp_expires_at = null;
        $this->save();
    }
}
