<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('members_can_add_transactions')->default(true)->after('description');
            $table->boolean('members_can_invite')->default(true)->after('members_can_add_transactions');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['members_can_add_transactions', 'members_can_invite']);
        });
    }
};
