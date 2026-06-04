<?php
$cats = [
    ['slug'=>'cafeteria',  'nombre'=>'Cafetería',            'activa'=>true, 'permite_regalo'=>true,  'permite_guardar_para_despues'=>true],
    ['slug'=>'copias',     'nombre'=>'Copias e Impresiones', 'activa'=>true, 'permite_regalo'=>false, 'permite_guardar_para_despues'=>false],
    ['slug'=>'tramites',   'nombre'=>'Trámites',             'activa'=>true, 'permite_regalo'=>false, 'permite_guardar_para_despues'=>false],
    ['slug'=>'souvenirs',  'nombre'=>'Souvenirs',            'activa'=>true, 'permite_regalo'=>true,  'permite_guardar_para_despues'=>true],
    ['slug'=>'servicios',  'nombre'=>'Servicios Internos',   'activa'=>true, 'permite_regalo'=>false, 'permite_guardar_para_despues'=>false],
];
foreach($cats as $cat) {
    App\Models\Cart\Categoria::updateOrCreate(['slug'=>$cat['slug']], $cat);
    echo "OK: " . $cat['slug'] . "\n";
}