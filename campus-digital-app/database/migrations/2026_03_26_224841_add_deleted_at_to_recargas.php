<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->softDeletesTz()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('recargas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};