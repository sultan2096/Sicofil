<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AppliesCommuneScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identifiant',
        'name',
        'email',
        'password',
        'role',
        'commune_id',
        'zone_id',
        'mfa_enabled',
        'mfa_secret',
        'mfa_otp_code',
        'mfa_otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
        'mfa_otp_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'mfa_enabled' => 'boolean',
        'mfa_otp_expires_at' => 'datetime',
    ];

    public function generateMfaOtp(): string
    {
        $this->mfa_otp_code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->mfa_otp_expires_at = now()->addMinutes(10);
        $this->save();

        return $this->mfa_otp_code;
    }

    public function isValidMfaOtp(string $code): bool
    {
        return $this->mfa_otp_code === $code
            && $this->mfa_otp_expires_at
            && $this->mfa_otp_expires_at->isFuture();
    }

    public function clearMfaOtp(): void
    {
        $this->mfa_otp_code = null;
        $this->mfa_otp_expires_at = null;
        $this->save();
    }

    /**
     * Commune assignée (null pour admin_sicofil)
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Get the zone fiscale associated with the user
     */
    public function zone()
    {
        return $this->belongsTo(ZoneFiscale::class, 'zone_id');
    }

    /**
     * Get the paiements created by this collecteur
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'collecteur_id');
    }

    /**
     * Alias pour les paiements collectés (pour les rapports)
     */
    public function paiementsCollectes()
    {
        return $this->hasMany(Paiement::class, 'collecteur_id');
    }

    /**
     * Relation avec la zone fiscale
     */
    public function zoneFiscale()
    {
        return $this->belongsTo(ZoneFiscale::class, 'zone_id');
    }

    /**
     * Get the logs for this user
     */
    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    /**
     * Check if user is maire
     */
    public function isMaire()
    {
        return $this->role === 'maire';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is collecteur
     */
    public function isCollecteur()
    {
        return $this->role === 'collecteur';
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers()
    {
        return in_array($this->role, ['maire', 'admin']);
    }

    /**
     * Super admin : gestion multi-communes (voit toutes les communes)
     */
    public function isAdminSicofil()
    {
        return strtolower((string) $this->role) === 'admin_sicofil';
    }
}
