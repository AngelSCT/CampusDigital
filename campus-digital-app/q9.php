<?php
// Busca sin usar la relacion items()
$items = App\Models\Cart\ItemCarrito::whereHas('carrito', fn($q) => 
    $q->where('uuid', '7fe28afc-375c-4ba3-bcde-d3598ef6a596')
)->get();
echo "Total items directos: " . $items->count() . "\n";
$items->each(function($i) {
    echo "id={$i->id} estado_item={$i->estado_item} metadata=" . json_encode($i->metadata) . "\n";
});