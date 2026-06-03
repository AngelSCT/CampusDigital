<?php
$carrito = App\Models\Cart\Carrito::latest()->first();
echo "UUID: " . $carrito?->uuid . " | estado: " . $carrito?->estado . "\n";
echo "Items total: " . ($carrito?->items()->count() ?? 0) . "\n";
$carrito?->items()->get()->each(function($i) {
    echo "- " . $i->nombre . " | metadata: " . json_encode($i->metadata) . "\n";
});