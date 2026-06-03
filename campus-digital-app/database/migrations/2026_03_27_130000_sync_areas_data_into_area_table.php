<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('areas') || !Schema::hasTable('area')) {
            return;
        }

        $areasHasNombre = Schema::hasColumn('areas', 'nombre');
        $areasHasNameArea = Schema::hasColumn('areas', 'name_area');

        if (!$areasHasNombre && !$areasHasNameArea) {
            return;
        }

        $now = now();
        $areas = DB::table('areas')->get();

        foreach ($areas as $row) {
            $idArea = $row->id_area ?? null;
            if (!$idArea) {
                continue;
            }

            $nombre = null;
            if ($areasHasNombre && isset($row->nombre)) {
                $nombre = (string) $row->nombre;
            } elseif ($areasHasNameArea && isset($row->name_area)) {
                $nombre = (string) $row->name_area;
            }

            if ($nombre === null) {
                $nombre = '';
            }

            DB::table('area')->updateOrInsert(
                ['id_area' => $idArea],
                [
                    'name_area' => $nombre,
                    'updated_at' => $now,
                    'created_at' => $now,
                    'deleted_at' => null,
                ]
            );
        }
    }

    public function down(): void
    {
        // No destructive rollback to avoid data loss.
    }
};
