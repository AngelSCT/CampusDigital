<?php

namespace App\Observers;

use App\Models\Recarga;

class RecargaObserver
{
    public function created(Recarga $recarga): void
    {
        // La recarga se procesa desde RecargaController.
        // No crear movimientos aquí para evitar duplicados y conflictos con la tabla movimientos.
    }
}