<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// Creamos la instancia del Capsule Manager
$capsule = new Capsule;

// Configuramos los datos de conexión
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'sistema_alquiler_db', // <-- Poné el nombre exacto de tu DB aquí
    'username'  => 'root',                // Usuario de XAMPP
    'password'  => '',                    // Contraseña de XAMPP (suele estar vacía)
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// SetAsGlobal permite que Capsule funcione como un "Singleton"
// para que puedas usarlo en cualquier parte del código
$capsule->setAsGlobal();

// Inicializamos Eloquent
$capsule->bootEloquent();