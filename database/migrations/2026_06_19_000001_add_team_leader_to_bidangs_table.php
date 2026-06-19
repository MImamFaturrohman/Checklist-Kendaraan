<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            $table->string('team_leader_nama', 200)->nullable()->after('manager_email');
            $table->string('team_leader_email', 255)->nullable()->after('team_leader_nama');
        });
    }

    public function down(): void
    {
        Schema::table('bidangs', function (Blueprint $table) {
            $table->dropColumn(['team_leader_nama', 'team_leader_email']);
        });
    }
};
