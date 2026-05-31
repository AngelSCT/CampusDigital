<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo student ──────────────────────────────────────────
        $user = User::create([
            'name'       => 'Ana García López',
            'email'      => 'ana@universidad.mx',
            'student_id' => 'U2024001',
            'career'     => 'Ingeniería en Sistemas',
            'semester'   => '6to',
            'password'   => Hash::make('password'),
        ]);

        $wallet = Wallet::create([
            'user_id'       => $user->id,
            'balance'       => 1250.50,
            'card_number'   => '4000123456789012',
            'monthly_limit' => 5000.00,
            'is_active'     => true,
        ]);

        // ── Demo transactions ──────────────────────────────────────
        $transactions = [
            ['type' => 'cargo',  'category' => 'Cafetería',   'amount' => 45.50,   'days' => 0,  'hours' => 2],
            ['type' => 'abono',  'category' => 'Recarga',     'amount' => 500.00,  'days' => 0,  'hours' => 6],
            ['type' => 'cargo',  'category' => 'Librería',    'amount' => 125.00,  'days' => 1,  'hours' => 5],
            ['type' => 'cargo',  'category' => 'Impresiones', 'amount' => 35.00,   'days' => 1,  'hours' => 9],
            ['type' => 'cargo',  'category' => 'Cafetería',   'amount' => 68.00,   'days' => 5,  'hours' => 4],
            ['type' => 'abono',  'category' => 'Recarga',     'amount' => 1000.00, 'days' => 6,  'hours' => 13],
            ['type' => 'cargo',  'category' => 'Laboratorio', 'amount' => 200.00,  'days' => 8,  'hours' => 10],
            ['type' => 'cargo',  'category' => 'Deportes',    'amount' => 80.00,   'days' => 10, 'hours' => 16],
            ['type' => 'cargo',  'category' => 'Cafetería',   'amount' => 42.00,   'days' => 12, 'hours' => 1],
            ['type' => 'abono',  'category' => 'Recarga',     'amount' => 500.00,  'days' => 14, 'hours' => 9],
            ['type' => 'cargo',  'category' => 'Impresiones', 'amount' => 55.00,   'days' => 15, 'hours' => 14],
            ['type' => 'cargo',  'category' => 'Librería',    'amount' => 310.00,  'days' => 18, 'hours' => 11],
        ];

        foreach ($transactions as $i => $t) {
            $date = now()->subDays($t['days'])->subHours($t['hours']);
            Transaction::create([
                'wallet_id'   => $wallet->id,
                'type'        => $t['type'],
                'category'    => $t['category'],
                'amount'      => $t['amount'],
                'reference'   => 'REF-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'description' => $t['category'] . ' - ' . ($t['type'] === 'abono' ? 'Recarga de saldo' : 'Consumo'),
                'status'      => 'completado',
                'created_at'  => $date,
                'updated_at'  => $date,
            ]);
        }

        $this->command->info('✅ Seeder completado. Usuario demo: ana@universidad.mx / password');
    }
}
