<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->count() < 5) {
            $this->command->info('Não há usuários suficientes para criar grupos de teste. Pulando o GroupSeeder.');

            return;
        }

        $owners = $users->take(5);

        foreach ($owners as $owner) {
            $group = Group::factory()->create([
                'owner_id' => $owner->id,
            ]);

            $group->members()->attach($owner->id, ['role' => 'admin']);

            $members = User::where('id', '!=', $owner->id)->inRandomOrder()->limit(rand(2, 5))->get();

            $group->members()->attach($members);
        }
    }
}
