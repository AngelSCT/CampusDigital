<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {

        $roles = [
            ['nombre' => 'estudiante',     'descripcion' => 'Usuario final que consume servicios digitales'],
            ['nombre' => 'proveedor_area', 'descripcion' => 'Proveedor o área interna que atiende solicitudes'],
            ['nombre' => 'administrador',  'descripcion' => 'Admin con acceso total'],
        ];

        foreach ($roles as $rol) {
            DB::table('rol')->insertOrIgnore($rol);
        }

        $permisos = [

            // ── 4.1 
            ['clave' => 'user.read',            'descripcion' => 'Consultar usuarios'],
            ['clave' => 'user.write',           'descripcion' => 'Crear/editar usuarios'],
            ['clave' => 'role.read',            'descripcion' => 'Consultar roles'],
            ['clave' => 'role.write',           'descripcion' => 'Administrar roles'],
            ['clave' => 'permission.read',      'descripcion' => 'Consultar permisos'],
            ['clave' => 'permission.write',     'descripcion' => 'Administrar permisos'],
            ['clave' => 'audit.read',           'descripcion' => 'Consultar bitácoras'],

            ['clave' => 'user.delete',          'descripcion' => 'Eliminar/desactivar usuarios'],
            ['clave' => 'user.block',           'descripcion' => 'Bloquear/desbloquear usuarios'],
            ['clave' => 'audit.write',          'descripcion' => 'Registrar eventos en bitácora'],
            ['clave' => 'report.users',         'descripcion' => 'Ver reportes de usuarios y accesos'],

            // ── 4.2 
            ['clave' => 'wallet.read',          'descripcion' => 'Consultar saldo propio'],
            ['clave' => 'wallet.read.any',      'descripcion' => 'Consultar saldo de cualquier usuario'],
            ['clave' => 'wallet.charge',        'descripcion' => 'Registrar cargos al monedero'],
            ['clave' => 'wallet.credit',        'descripcion' => 'Registrar abonos al monedero'],
            ['clave' => 'wallet.rules.write',   'descripcion' => 'Configurar reglas de límites de saldo'],
            ['clave' => 'wallet.history.read',  'descripcion' => 'Consultar historial de movimientos propio'],
            ['clave' => 'wallet.history.any',   'descripcion' => 'Consultar historial de movimientos de cualquier usuario'],
            ['clave' => 'report.wallet',        'descripcion' => 'Ver reportes de saldo y movimientos'],

            // ── 4.3 
            ['clave' => 'catalog.read',         'descripcion' => 'Consultar catálogo de servicios y productos'],
            ['clave' => 'catalog.write',        'descripcion' => 'Crear/editar servicios y productos'],
            ['clave' => 'catalog.delete',       'descripcion' => 'Eliminar/desactivar productos del catálogo'],
            ['clave' => 'catalog.price.write',  'descripcion' => 'Modificar precios del catálogo'],
            ['clave' => 'report.catalog',       'descripcion' => 'Ver reportes del catálogo'],

            // ── 4.4 
            ['clave' => 'cart.read',            'descripcion' => 'Consultar carrito propio'],
            ['clave' => 'cart.write',           'descripcion' => 'Agregar/quitar items del carrito'],
            ['clave' => 'checkout.execute',     'descripcion' => 'Confirmar checkout/compra'],
            ['clave' => 'checkout.read.any',    'descripcion' => 'Consultar checkouts de todos los usuarios'],
            ['clave' => 'report.checkout',      'descripcion' => 'Ver reportes de consumos y checkouts'],

            // ── 4.5 
            ['clave' => 'order.read',           'descripcion' => 'Consultar pedidos propios'],
            ['clave' => 'order.read.any',       'descripcion' => 'Consultar pedidos de cualquier usuario o área'],
            ['clave' => 'order.write',          'descripcion' => 'Crear pedidos'],
            ['clave' => 'order.status.write',   'descripcion' => 'Actualizar estado de pedidos'],
            ['clave' => 'order.cancel',         'descripcion' => 'Cancelar pedidos'],
            ['clave' => 'order.evidence.write', 'descripcion' => 'Registrar evidencia de entrega (folio/QR)'],
            ['clave' => 'report.orders',        'descripcion' => 'Ver reportes de pedidos'],

            // ── 4.6 
            ['clave' => 'ticket.read',          'descripcion' => 'Consultar tickets propios'],
            ['clave' => 'ticket.read.any',      'descripcion' => 'Consultar todos los tickets del sistema'],
            ['clave' => 'ticket.write',         'descripcion' => 'Crear tickets de servicio interno'],
            ['clave' => 'ticket.assign',        'descripcion' => 'Asignar tickets a áreas o usuarios'],
            ['clave' => 'ticket.status.write',  'descripcion' => 'Actualizar estado y prioridad de tickets'],
            ['clave' => 'ticket.close',         'descripcion' => 'Cerrar tickets con evidencia'],
            ['clave' => 'report.tickets',       'descripcion' => 'Ver reportes de tickets y tiempos de atención'],

            // ── 4.7 
            ['clave' => 'reservation.read',     'descripcion' => 'Consultar reservas propias'],
            ['clave' => 'reservation.read.any', 'descripcion' => 'Consultar todas las reservas del sistema'],
            ['clave' => 'reservation.write',    'descripcion' => 'Crear reservas de recursos o turnos'],
            ['clave' => 'reservation.cancel',   'descripcion' => 'Cancelar reservas'],
            ['clave' => 'resource.write',       'descripcion' => 'Administrar recursos reservables (salas, labs, equipos)'],
            ['clave' => 'report.reservations',  'descripcion' => 'Ver reportes de reservas y ocupación'],

            // ── 4.8 
            ['clave' => 'topup.execute',        'descripcion' => 'Realizar recarga de saldo'],
            ['clave' => 'topup.read',           'descripcion' => 'Consultar historial de recargas propias'],
            ['clave' => 'topup.read.any',       'descripcion' => 'Consultar historial de recargas de todos los usuarios'],
            ['clave' => 'payment.read',         'descripcion' => 'Consultar pagos y su estado'],
            ['clave' => 'voucher.read',         'descripcion' => 'Consultar y descargar comprobantes propios'],
            ['clave' => 'voucher.read.any',     'descripcion' => 'Consultar comprobantes de cualquier usuario'],
            ['clave' => 'conciliation.read',    'descripcion' => 'Consultar conciliación de recargas'],
            ['clave' => 'report.payments',      'descripcion' => 'Ver reportes de recargas y pagos'],

            // ── 4.9 
            ['clave' => 'provider.orders.read',   'descripcion' => 'Ver pedidos entrantes del área propia'],
            ['clave' => 'provider.orders.manage', 'descripcion' => 'Aceptar, rechazar y actualizar pedidos del área'],
            ['clave' => 'provider.delivery',      'descripcion' => 'Confirmar entrega/consumo de pedidos'],
            ['clave' => 'provider.wallet.read',   'descripcion' => 'Consultar saldo del usuario (solo lectura, en contexto de entrega)'],
            ['clave' => 'report.provider',        'descripcion' => 'Ver reportes operativos del área proveedora'],

            // ── 4.10 
            ['clave' => 'card.read',            'descripcion' => 'Consultar datos de tarjeta propia'],
            ['clave' => 'card.read.any',        'descripcion' => 'Consultar tarjetas de cualquier usuario'],
            ['clave' => 'card.write',           'descripcion' => 'Registrar y asociar tarjetas a usuarios'],
            ['clave' => 'card.block',           'descripcion' => 'Bloquear/desbloquear tarjetas universitarias'],
            ['clave' => 'card.auth',            'descripcion' => 'Autenticar usuarios por proximidad (RFID/NFC)'],
            ['clave' => 'card.import',          'descripcion' => 'Importar tarjetas de forma masiva por CSV'],
            ['clave' => 'report.cards',         'descripcion' => 'Ver reportes de uso, incidentes y auditoría de tarjetas'],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permiso')->insertOrIgnore($permiso);
        }

        $adminRol      = DB::table('rol')->where('nombre', 'administrador')->first();
        $todosPermisos = DB::table('permiso')->get();

        foreach ($todosPermisos as $permiso) {
            DB::table('rol_permiso')->insertOrIgnore([
                'rol_id'     => $adminRol->id,
                'permiso_id' => $permiso->id,
            ]);
        }

        $proveedorRol = DB::table('rol')->where('nombre', 'proveedor_area')->first();

        $permisosProveedor = [
            'user.read',
            'audit.read',
            'provider.orders.read',
            'provider.orders.manage',
            'provider.delivery',
            'provider.wallet.read',
            'report.provider',
            'catalog.read',
            'ticket.read',
            'ticket.write',
            'ticket.status.write',
            'ticket.close',
        ];

        $permisosProveedorRows = DB::table('permiso')
            ->whereIn('clave', $permisosProveedor)
            ->get();

        foreach ($permisosProveedorRows as $permiso) {
            DB::table('rol_permiso')->insertOrIgnore([
                'rol_id'     => $proveedorRol->id,
                'permiso_id' => $permiso->id,
            ]);
        }

        $estudianteRol = DB::table('rol')->where('nombre', 'estudiante')->first();

        $permisosEstudiante = [
            'user.read',
            'wallet.read',
            'wallet.history.read',
            'catalog.read',
            'cart.read',
            'cart.write',
            'checkout.execute',
            'order.read',
            'order.write',
            'order.cancel',
            'ticket.read',
            'ticket.write',
            'reservation.read',
            'reservation.write',
            'reservation.cancel',
            'topup.execute',
            'topup.read',
            'voucher.read',
            'card.read',
        ];

        $permisosEstudianteRows = DB::table('permiso')
            ->whereIn('clave', $permisosEstudiante)
            ->get();

        foreach ($permisosEstudianteRows as $permiso) {
            DB::table('rol_permiso')->insertOrIgnore([
                'rol_id'     => $estudianteRol->id,
                'permiso_id' => $permiso->id,
            ]);
        }
    }
}