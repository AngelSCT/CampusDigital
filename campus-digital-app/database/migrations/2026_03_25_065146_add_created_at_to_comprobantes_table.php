<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('comprobantes', function (Blueprint $table) {
        $table->timestamp('created_at')->nullable();
    });
}

public function down()
{
    Schema::table('comprobantes', function (Blueprint $table) {
        $table->dropColumn('created_at');
    });
}
};
