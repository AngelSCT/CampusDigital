<?php
$uuid = 'f6568ec6-08b9-42a3-8f40-8b77c8eb8679';
$c = App\Models\Cart\Carrito::where('uuid', $uuid)->first();
echo "Carrito: " . $c?->uuid . " | estado: " . $c?->estado . "\n";
$c?->items()->get()->each(function($i) {
    echo "item_id={$i->id} nombre={$i->nombre}\n";
    echo "metadata_raw: " . $i->getRawOriginal('metadata') . "\n";
});