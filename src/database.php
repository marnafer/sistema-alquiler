<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// Creamos la instancia del Capsule Manager
$capsule = new Capsule;

// Configuramos los datos de conexi�n
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'sistema_alquiler_db', // <-- Pon� el nombre exacto de tu DB aqu�
    'username'  => 'root',                // Usuario de XAMPP
    'password'  => '',                    // Contrase�a de XAMPP (suele estar vac�a)
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// SetAsGlobal permite que Capsule funcione como un "Singleton"
// para que puedas usarlo en cualquier parte del c�digo
$capsule->setAsGlobal();

// Inicializamos Eloquent
$capsule->bootEloquent();