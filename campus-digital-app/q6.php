<?php
echo "PedidoTienda count: " . App\Models\PedidoTienda::count() . "\n";
App\Models\PedidoTienda::latest()->take(3)->get()->each(function($p) {
    echo "ID:{$p->id} dest:{$p->destinatario_id} estado:{$p->estado} total:{$p->total}\n";
});