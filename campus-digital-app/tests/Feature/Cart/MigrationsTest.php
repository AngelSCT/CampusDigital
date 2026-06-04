<?php

namespace Tests\Feature\Cart;

use Illuminate\Support\Facades\Schema;

class MigrationsTest extends CartTestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function las_nueve_tablas_del_modulo_carrito_existen_en_sqlite(): void
    {
        $tablas = [
            'cart_categorias',
            'cart_reglas_categoria',
            'cart_solicitudes_modulo',
            'cart_modulos_clientes',
            'cart_tokens_modulo',
            'cart_carritos',
            'cart_items_carrito',
            'cart_bitacora',
            'cart_conciliaciones_pendientes',
        ];

        foreach ($tablas as $tabla) {
            $this->assertTrue(
                Schema::hasTable($tabla),
                "La tabla '{$tabla}' no existe tras correr las migraciones del módulo Carrito."
            );
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cart_carritos_tiene_las_columnas_criticas(): void
    {
        $this->assertTrue(Schema::hasColumn('cart_carritos', 'uuid'));
        $this->assertTrue(Schema::hasColumn('cart_carritos', 'modulo_id'));
        $this->assertTrue(Schema::hasColumn('cart_carritos', 'usuario_ref'));
        $this->assertTrue(Schema::hasColumn('cart_carritos', 'estado'));
        $this->assertTrue(Schema::hasColumn('cart_carritos', 'total'));
        $this->assertTrue(Schema::hasColumn('cart_carritos', 'expira_at'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cart_tokens_modulo_tiene_columnas_v11(): void
    {
        // C2: entregado_at — one-time JWT display
        $this->assertTrue(Schema::hasColumn('cart_tokens_modulo', 'entregado_at'));
        // C6: pair_jti y replaces_jti reemplazan parent_jti
        $this->assertTrue(Schema::hasColumn('cart_tokens_modulo', 'pair_jti'));
        $this->assertTrue(Schema::hasColumn('cart_tokens_modulo', 'replaces_jti'));
        $this->assertFalse(Schema::hasColumn('cart_tokens_modulo', 'parent_jti'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cart_conciliaciones_pendientes_existe_con_columnas_de_escrow(): void
    {
        // C5: tabla nueva para pago diferido
        $this->assertTrue(Schema::hasColumn('cart_conciliaciones_pendientes', 'carrito_uuid'));
        $this->assertTrue(Schema::hasColumn('cart_conciliaciones_pendientes', 'monto'));
        $this->assertTrue(Schema::hasColumn('cart_conciliaciones_pendientes', 'intentos'));
        $this->assertTrue(Schema::hasColumn('cart_conciliaciones_pendientes', 'estado_conciliacion'));
        $this->assertTrue(Schema::hasColumn('cart_conciliaciones_pendientes', 'proximo_intento_at'));
    }
}
