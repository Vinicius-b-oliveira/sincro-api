<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\PersonalAccessToken;

class ScribeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! App::environment('local')) {
            return;
        }

        $token = env('SCRIBE_AUTH_TOKEN');

        if (! $token) {
            $this->command->error('A variável SCRIBE_AUTH_TOKEN não está definida no seu .env. Abortando ScribeUserSeeder.');

            return;
        }

        $user = User::factory()->create([
            'name' => 'Scribe Doc User',
            'email' => 'scribe@example.com',
        ]);

        $group = Group::first();
        if ($group) {
            $group->members()->attach($user->id);
        }

        PersonalAccessToken::forceCreate([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'api-docs-token',
            'token' => hash('sha256', $token),
            'abilities' => ['*'],
        ]);

        $this->command->info('Usuário do Scribe criado com sucesso.');
    }
}
