<?php $u = App\Models\Usuario::where('matricula','00000001')->first(); echo $u ? $u->nombre.'|'.$u->apellido_paterno.'|'.$u->matricula : 'NO ENCONTRADO';
