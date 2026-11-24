<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesCommuneScope
{
    protected static function bootAppliesCommuneScope()
    {
        static::addGlobalScope('commune', function (Builder $builder) {
            // IMPORTANT:
            // Ne pas appeler auth()->check() ici: check() peut déclencher la résolution
            // de l'utilisateur (UserProvider) et donc relancer des requêtes sur des
            // modèles eux-mêmes soumis à ce scope → récursion / explosion mémoire.
            // hasUser() vérifie uniquement si l'utilisateur est déjà résolu.
            if (!auth()->hasUser()) {
                return;
            }

            $user = auth()->user();
            $role = strtolower((string) ($user->role ?? ''));

            // L'admin SICOFIL voit toutes les communes sauf si un scope est actif
            if ($role === 'admin_sicofil') {
                $scopedCommuneId = session('commune_scope_id');
                if (!empty($scopedCommuneId)) {
                    $builder->where($builder->getModel()->getTable() . '.commune_id', $scopedCommuneId);
                }
                return;
            }

            if (!empty($user->commune_id)) {
                $builder->where($builder->getModel()->getTable() . '.commune_id', $user->commune_id);
            }
        });

        static::creating(function ($model) {
            if (!auth()->hasUser()) {
                return;
            }

            $user = auth()->user();
            $role = strtolower((string) ($user->role ?? ''));

            if ($role !== 'admin_sicofil' && empty($model->commune_id) && !empty($user->commune_id)) {
                $model->commune_id = $user->commune_id;
            }

            if ($role === 'admin_sicofil' && empty($model->commune_id)) {
                $scopedCommuneId = session('commune_scope_id');
                if (!empty($scopedCommuneId)) {
                    $model->commune_id = $scopedCommuneId;
                }
            }
        });
    }
}
