<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crea usuarios de demostración con roles específicos:
     * - Administrador
     * - Agente
     * - Cliente
     */
    public function run(): void
    {
        $demo = [
            [
                'name' => 'Jose Estuardo',
                'email' => 'jose@email.com',
                'password' => '12345',
                'role' => 'admin'
            ],
            [
                'name' => 'Carla Estrada',
                'email' => 'carla@email.com',
                'password' => '12345',
                'role' => 'agent'
            ],
            [
                'name' => 'Maravin Lopez',
                'email' => 'maravin@email.com',
                'password' => '12345',
                'role' => 'client'
            ]
        ];
        // Crea usuarios de demostración recorro el array $demo mando insertar los datos a la DB
        foreach ($demo as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
                'role' => $user['role'],
                'department_id' => 1, // Asignamos un departamento por defecto
            ]);
        }
    }
}
