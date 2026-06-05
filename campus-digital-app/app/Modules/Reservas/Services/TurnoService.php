<?php

namespace App\Modules\Reservas\Services;

use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para gestión del sistema de turnos / cola virtual.
 *
 * Genera números de turno secuenciales por tipo y por día, mantiene
 * la posición en cola, y gestiona las transiciones de estado.
 */
class TurnoService
{
    /**
     * Crea un nuevo turno para un usuario.
     */
    public function crearTurno(
        int $usuarioId,
        string $tipoTurno,
        ?int $recursoId = null,
        ?string $pedidoReferencia = null,
        ?string $notas = null
    ): Turno {
        return DB::transaction(function () use ($usuarioId, $tipoTurno, $recursoId, $pedidoReferencia, $notas) {
            $prefijo = Turno::PREFIJOS[$tipoTurno] ?? 'G';
            $numero  = $this->generarNumeroTurno($tipoTurno, $prefijo);
            $posicion = $this->calcularPosicion($tipoTurno);

            return Turno::create([
                'id_usuario'         => $usuarioId,
                'tipo_turno'         => $tipoTurno,
                'numero_turno'       => $numero,
                'estado'             => Turno::ESTADO_ESPERANDO,
                'posicion'           => $posicion,
                'id_recurso'         => $recursoId,
                'pedido_referencia'  => $pedidoReferencia,
                'notas'              => $notas,
            ]);
        });
    }

    /**
     * Genera un número de turno secuencial: prefijo + día + 3 dígitos
     * Formato: A040601-001 (prefijo + YYMMDD + secuencia)
     */
    public function generarNumeroTurno(string $tipoTurno, string $prefijo): string
    {
        $hoy = Carbon::today();

        $ultimoDelDia = Turno::where('tipo_turno', $tipoTurno)
            ->whereDate('created_at', $hoy)
            ->orderBy('id_turno', 'desc')
            ->lockForUpdate()
            ->first();

        $siguiente = 1;
        if ($ultimoDelDia) {
            $partes = explode('-', $ultimoDelDia->numero_turno);
            $ultimoNum = (int) end($partes);
            $siguiente = $ultimoNum + 1;
        }

        $fecha = $hoy->format('ymd');
        $secuencia = str_pad((string) $siguiente, 3, '0', STR_PAD_LEFT);

        return "{$prefijo}{$fecha}-{$secuencia}";
    }

    /**
     * Calcula la posición actual en cola (1 = primero).
     */
    public function calcularPosicion(string $tipoTurno): int
    {
        $enCola = Turno::where('tipo_turno', $tipoTurno)
            ->where('estado', Turno::ESTADO_ESPERANDO)
            ->count();

        return $enCola + 1;
    }

    /**
     * Marca un turno como llamado.
     */
    public function llamar(Turno $turno): Turno
    {
        $turno->update([
            'estado'     => Turno::ESTADO_LLAMADO,
            'llamado_at' => now(),
        ]);

        return $turno;
    }

    /**
     * Marca un turno como atendido.
     */
    public function atender(Turno $turno): Turno
    {
        $turno->update([
            'estado'      => Turno::ESTADO_ATENDIDO,
            'atendido_at' => now(),
        ]);

        return $turno;
    }

    /**
     * Marca un turno como no-show.
     */
    public function marcarNoShow(Turno $turno): Turno
    {
        $turno->update([
            'estado' => Turno::ESTADO_NO_SHOW,
        ]);

        return $turno;
    }

    /**
     * Cancela un turno.
     */
    public function cancelar(Turno $turno): Turno
    {
        $turno->update([
            'estado'       => Turno::ESTADO_CANCELADO,
            'cancelado_at' => now(),
        ]);

        return $turno;
    }

    /**
     * Recalcula posiciones de la cola cuando un turno sale.
     */
    public function recalcularPosiciones(string $tipoTurno): void
    {
        $turnos = Turno::where('tipo_turno', $tipoTurno)
            ->where('estado', Turno::ESTADO_ESPERANDO)
            ->orderBy('id_turno')
            ->get();

        $pos = 1;
        foreach ($turnos as $t) {
            if ($t->posicion !== $pos) {
                $t->posicion = $pos;
                $t->save();
            }
            $pos++;
        }
    }

    /**
     * Obtiene estadísticas rápidas del día para un tipo de turno.
     */
    public function estadisticasHoy(string $tipoTurno = null): array
    {
        $query = Turno::whereDate('created_at', Carbon::today());
        if ($tipoTurno) {
            $query->where('tipo_turno', $tipoTurno);
        }

        $total     = (clone $query)->count();
        $esperando = (clone $query)->where('estado', Turno::ESTADO_ESPERANDO)->count();
        $llamados  = (clone $query)->where('estado', Turno::ESTADO_LLAMADO)->count();
        $atendidos = (clone $query)->where('estado', Turno::ESTADO_ATENDIDO)->count();
        $noShow    = (clone $query)->where('estado', Turno::ESTADO_NO_SHOW)->count();
        $cancelados= (clone $query)->where('estado', Turno::ESTADO_CANCELADO)->count();

        return [
            'total'      => $total,
            'esperando'  => $esperando,
            'llamados'   => $llamados,
            'atendidos'  => $atendidos,
            'no_show'    => $noShow,
            'cancelados' => $cancelados,
        ];
    }
}
