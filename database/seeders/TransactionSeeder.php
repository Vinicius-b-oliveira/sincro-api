<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('Nenhum usuário encontrado. Pulando o TransactionSeeder.');

            return;
        }

        foreach ($users as $user) {
            Transaction::factory()->count(10)->create([
                'user_id' => $user->id,
                'group_id' => null,
            ]);

            $groups = $user->groups;

            if ($groups->isNotEmpty()) {
                $randomGroup = $groups->random();

                Transaction::factory()->count(10)->create([
                    'user_id' => $user->id,
                    'group_id' => $randomGroup->id,
                ]);
            }
        }
    }
}
