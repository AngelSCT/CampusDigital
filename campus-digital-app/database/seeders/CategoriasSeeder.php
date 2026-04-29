<?php

namespace Database\Seeders;

use App\Models\Cart\Categoria;
use App\Models\Cart\ReglaCategoria;
use Illuminate\Database\Seeder;

/**
 * Inserta las 5 categorías base del módulo Carrito y sus reglas de negocio.
 *
 * CRITERIO PARA permite_pago_diferido
 * ------------------------------------
 * true  → Categorías de monto típicamente bajo donde la universidad asume
 *         el riesgo de conciliar luego. Si Saldo falla, el servicio se
 *         entrega igual y se cobra después (préstamos gratuitos, café,
 *         copias, souvenirs pequeños). Topes de exposición en config/cart.php.
 *
 * false → Categorías donde el monto puede ser alto o el recurso escaso
 *         (salas, equipos audiovisuales, eventos). Si Saldo falla, el
 *         checkout debe rechazarse para no comprometer recursos sin
 *         garantía de pago. Cambiar este criterio requiere autorización
 *         del responsable del módulo Carrito.
 */
class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'slug'        => 'producto',
                'nombre'      => 'Producto Físico',
                'descripcion' => 'Artículo de inventario con stock (souvenirs, papelería, cafetería).',
                'reglas'      => [
                    [ReglaCategoria::CLAVE_CANTIDAD_MAXIMA,       '10',    ReglaCategoria::TIPO_INT],
                    [ReglaCategoria::CLAVE_REQUIERE_SALDO,        'true',  ReglaCategoria::TIPO_BOOL],
                    [ReglaCategoria::CLAVE_PERMITE_DEVOLUCION,    'false', ReglaCategoria::TIPO_BOOL],
                    // true: montos bajos (café, copias) → diferir pago si Saldo no responde.
                    [ReglaCategoria::CLAVE_PERMITE_PAGO_DIFERIDO, 'true',  ReglaCategoria::TIPO_BOOL],
                ],
            ],
            [
                'slug'        => 'servicio',
                'nombre'      => 'Servicio',
                'descripcion' => 'Servicio prestado por la institución (impresiones de tesis, trámites, asesorías premium).',
                'reglas'      => [
                    [ReglaCategoria::CLAVE_CANTIDAD_MAXIMA,       '1',     ReglaCategoria::TIPO_INT],
                    [ReglaCategoria::CLAVE_REQUIERE_SALDO,        'true',  ReglaCategoria::TIPO_BOOL],
                    [ReglaCategoria::CLAVE_PERMITE_DEVOLUCION,    'false', ReglaCategoria::TIPO_BOOL],
                    // false: servicios pueden tener costo alto; no diferir sin garantía de cobro.
                    [ReglaCategoria::CLAVE_PERMITE_PAGO_DIFERIDO, 'false', ReglaCategoria::TIPO_BOOL],
                ],
            ],
            [
                'slug'        => 'reserva',
                'nombre'      => 'Reserva Temporal',
                'descripcion' => 'Reserva de espacio, equipo o sala (laboratorios, salas de estudio, equipos audiovisuales).',
                'reglas'      => [
                    [ReglaCategoria::CLAVE_CANTIDAD_MAXIMA,       '1',     ReglaCategoria::TIPO_INT],
                    [ReglaCategoria::CLAVE_REQUIERE_SALDO,        'false', ReglaCategoria::TIPO_BOOL],
                    [ReglaCategoria::CLAVE_PERMITE_DEVOLUCION,    'true',  ReglaCategoria::TIPO_BOOL],
                    ['duracion_maxima_horas',                      '4',     ReglaCategoria::TIPO_INT],
                    // false: recursos escasos y potencialmente costosos; checkout debe garantizarse.
                    [ReglaCategoria::CLAVE_PERMITE_PAGO_DIFERIDO, 'false', ReglaCategoria::TIPO_BOOL],
                ],
            ],
            [
                'slug'        => 'prestamo',
                'nombre'      => 'Préstamo Bibliográfico',
                'descripcion' => 'Préstamo de material de la biblioteca (libros, revistas, equipo de cómputo).',
                'reglas'      => [
                    [ReglaCategoria::CLAVE_CANTIDAD_MAXIMA,       '5',     ReglaCategoria::TIPO_INT],
                    [ReglaCategoria::CLAVE_REQUIERE_SALDO,        'false', ReglaCategoria::TIPO_BOOL],
                    [ReglaCategoria::CLAVE_PERMITE_DEVOLUCION,    'true',  ReglaCategoria::TIPO_BOOL],
                    ['duracion_maxima_horas',                      '168',   ReglaCategoria::TIPO_INT], // 7 días
                    // true: préstamos son generalmente gratuitos → no hay monto en riesgo.
                    [ReglaCategoria::CLAVE_PERMITE_PAGO_DIFERIDO, 'true',  ReglaCategoria::TIPO_BOOL],
                ],
            ],
            [
                'slug'        => 'ticket',
                'nombre'      => 'Ticket de Evento',
                'descripcion' => 'Acceso a eventos universitarios (conferencias, torneos, presentaciones culturales).',
                'reglas'      => [
                    [ReglaCategoria::CLAVE_CANTIDAD_MAXIMA,       '3',     ReglaCategoria::TIPO_INT],
                    [ReglaCategoria::CLAVE_REQUIERE_SALDO,        'true',  ReglaCategoria::TIPO_BOOL],
                    [ReglaCategoria::CLAVE_PERMITE_DEVOLUCION,    'false', ReglaCategoria::TIPO_BOOL],
                    // false: precio variable; un evento con aforo limitado no puede arriesgar plazas sin cobro garantizado.
                    [ReglaCategoria::CLAVE_PERMITE_PAGO_DIFERIDO, 'false', ReglaCategoria::TIPO_BOOL],
                ],
            ],
        ];

        foreach ($categorias as $data) {
            $reglas = $data['reglas'];
            unset($data['reglas']);

            $categoria = Categoria::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            foreach ($reglas as [$clave, $valor, $tipoDato]) {
                ReglaCategoria::updateOrCreate(
                    ['categoria_id' => $categoria->id, 'clave' => $clave],
                    ['valor' => $valor, 'tipo_dato' => $tipoDato]
                );
            }
        }
    }
}
