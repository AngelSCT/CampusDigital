<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('modulo', 50); // cafeteria, copias, souvenirs, etc.
            $table->boolean('activo')->default(true);
            $table->text('imagen_url')->nullable();
            
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->softDeletes();
            
            $table->index('modulo', 'idx_producto__modulo');
            $table->index('activo', 'idx_producto__activo');
        });

        DB::unprepared('
            CREATE TRIGGER trg_producto__set_updated_at
            BEFORE UPDATE ON producto
            FOR EACH ROW EXECUTE FUNCTION set_updated_at();
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_producto__set_updated_at ON producto');
        Schema::dropIfExists('producto');
    }
};
