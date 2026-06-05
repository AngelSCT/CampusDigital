<?php

namespace App\Modules\Reservas\Services;

use App\Models\Recurso;
use App\Models\Reserva;
use Carbon\Carbon;

/**
 * Servicio para detectar conflictos de horario en reservas de recursos.
 *
 * Reglas:
 *  - Una reserva se considera en conflicto si se solapa con otra reserva
 *    del MISMO recurso que esté en estado 'pendiente' o 'confirmada'.
 *  - Dos rangos [A_inicio, A_fin) y [B_inicio, B_fin) se solapan si
 *    A_inicio < B_fin AND B_inicio < A_fin.
 */
class ConflictDetectionService
{
    /**
     * Verifica si existe conflicto para un recurso en un rango horario.
     *
     * @param int      $recursoId
     * @param Carbon   $fechaInicio
     * @param Carbon   $fechaFin
     * @param int|null $excludeReservaId  Excluir una reserva específica (para updates)
     * @return array  Lista de reservas en conflicto (vacía si no hay conflicto)
     */
    public function detectarConflictos(
        int $recursoId,
        Carbon $fechaInicio,
        Carbon $fechaFin,
        ?int $excludeReservaId = null
    ): array {
        $query = Reserva::where('id_recurso', $recursoId)
            ->whereIn('estado', [Reserva::ESTADO_PENDIENTE, Reserva::ESTADO_CONFIRMADA])
            ->where('fecha_inicio', '<', $fechaFin)
            ->where('fecha_fin', '>', $fechaInicio);

        if ($excludeReservaId !== null) {
            $query->where('id_reserva', '!=', $excludeReservaId);
        }

        return $query->get()->toArray();
    }

    /**
     * Verifica disponibilidad simple: booleano.
     */
    public function estaDisponible(
        int $recursoId,
        Carbon $fechaInicio,
        Carbon $fechaFin,
        ?int $excludeReservaId = null
    ): bool {
        return empty($this->detectarConflictos($recursoId, $fechaInicio, $fechaFin, $excludeReservaId));
    }

    /**
     * Verifica que el recurso esté activo y que el horario solicitado
     * no exceda su disponibilidad horaria definida en el campo `horarios`.
     */
    public function validarHorario(Recurso $recurso, Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $errores = [];

        if (!$recurso->estaDisponible()) {
            $errores[] = 'El recurso no está disponible para reservas.';
        }

        if ($fechaInicio->isPast()) {
            $errores[] = 'La fecha de inicio debe ser en el futuro.';
        }

        if ($fechaFin->lessThanOrEqualTo($fechaInicio)) {
            $errores[] = 'La fecha de fin debe ser posterior a la fecha de inicio.';
        }

        $horarios = $recurso->horarios;
        if (is_array($horarios) && !empty($horarios)) {
            $dias = [
                Carbon::MONDAY    => 'lunes',
                Carbon::TUESDAY   => 'martes',
                Carbon::WEDNESDAY => 'miercoles',
                Carbon::THURSDAY  => 'jueves',
                Carbon::FRIDAY    => 'viernes',
                Carbon::SATURDAY  => 'sabado',
                Carbon::SUNDAY    => 'domingo',
            ];

            $dia = $dias[$fechaInicio->dayOfWeek] ?? null;
            if ($dia && isset($horarios[$dia]) && is_array($horarios[$dia])) {
                $horaInicio = $fechaInicio->format('H:i');
                $horaFin    = $fechaFin->format('H:i');

                $enHorario = false;
                foreach ($horarios[$dia] as $rango) {
                    [$apertura, $cierre] = array_pad(explode('-', $rango, 2), 2, null);
                    if ($apertura && $cierre) {
                        $apertura = trim($apertura);
                        $cierre   = trim($cierre);
                        if ($horaInicio >= $apertura && $horaFin <= $cierre) {
                            $enHorario = true;
                            break;
                        }
                    }
                }

                if (!$enHorario) {
                    $errores[] = "El horario solicitado ({$horaInicio} - {$horaFin}) está fuera del horario disponible del recurso para {$dia}.";
                }
            }
        }

        return $errores;
    }

    /**
     * Calcula el costo de una reserva según las horas y el costo_por_hora.
     */
    public function calcularCosto(Recurso $recurso, Carbon $fechaInicio, Carbon $fechaFin): float
    {
        if (!$recurso->tieneCosto()) {
            return 0.0;
        }

        $horas = $fechaInicio->diffInMinutes($fechaFin) / 60;
        return round($horas * (float) $recurso->costo_por_hora, 2);
    }
}
