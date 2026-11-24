<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSicofilSeeder extends Seeder
{
    public function run(): void
    {
        $communeId = Commune::query()->value('id');

        User::updateOrCreate(
            ['identifiant' => 'admin_sicofil'],
            [
                'name' => 'Admin SICOFIL',
                'email' => 'admin@sicofil.local',
                'password' => Hash::make('admin123'),
                'role' => 'admin_sicofil',
                'commune_id' => $communeId,
                'zone_id' => null,
            ]
        );

        $this->command->info('Admin SICOFIL créé : admin_sicofil / admin123');
    }
}
