<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('area')) {
            Schema::create('area', function (Blueprint $table) {
                $table->increments('id_area');
                $table->string('name_area', 120)->default('');
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();
                $table->timestampTz('deleted_at')->nullable();

                $table->index('name_area', 'idx_area__name_area');
                $table->index('deleted_at', 'idx_area__deleted_at');
            });

            if (Schema::hasTable('areas')) {
                $now = now();
                $rows = DB::table('areas')->select('id_area', 'nombre')->get();

                foreach ($rows as $row) {
                    DB::table('area')->updateOrInsert(
                        ['id_area' => $row->id_area],
                        [
                            'name_area' => (string) ($row->nombre ?? ''),
                            'created_at' => $now,
                            'updated_at' => $now,
                            'deleted_at' => null,
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally left empty to avoid destructive rollback on production data.
    }
};
