<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? 'Sistema de Alquileres' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons (Agregado para que funcionen los iconos de Favoritos, Usuarios y Logs) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* Estilos globales y reseteo */
        * { box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 1000px; /* Un poquito más ancho para las tablas */
            margin: 0 auto; 
            line-height: 1.6; 
            color: #333; 
            background-color: #f9f9f9; 
            padding-bottom: 50px;
        }
        
        .container-main { padding: 0 20px; }

        h1 { font-size: 24px; color: #1e293b; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        
        /* Estilos para formularios y errores (compatibles con Propiedades y Usuarios) */
        .errores { background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 15px; margin-bottom: 25px; }
        .errores p { font-weight: bold; margin: 0 0 8px; color: #b91c1c; }
        .errores ul { margin: 0; padding-left: 20px; color: #b91c1c; font-size: 14px; }
        
        .seccion { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; margin: 30px 0 15px; display: flex; align-items: center; }
        .seccion::after { content: ""; flex: 1; margin-left: 10px; height: 1px; background: #e2e8f0; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .campo { display: flex; flex-direction: column; gap: 6px; }
        .campo.full { grid-column: 1 / -1; }
        
        label { font-size: 14px; font-weight: 600; color: #334155; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #fff; transition: border-color 0.2s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #3b82f6; }

        .campo-error input, .campo-error select, .campo-error textarea { border-color: #ef4444; background-color: #fffafb; }
        .msg-error { font-size: 12px; color: #ef4444; font-weight: 500; display: none; margin-top: 4px; }
        .campo-error .msg-error { display: block; }
        
        button[type="submit"] { background: #059669; color: #fff; border: none; border-radius: 6px; padding: 12px 30px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        button[type="submit"]:hover { background: #047857; }
        
        /* Estilo para la barra de navegación */
        .navbar { border-radius: 0 0 10px 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-link i { margin-right: 5px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <i class="bi bi-house-door-fill me-2"></i>AlquilerApp
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/propiedades"><i class="bi bi-building"></i> Propiedades</a>
                <a class="nav-link" href="/usuarios"><i class="bi bi-people"></i> Usuarios</a>
                <a class="nav-link" href="/favoritos"><i class="bi bi-heart"></i> Favoritos</a>
                <a class="nav-link" href="/logs-actividad"><i class="bi bi-journal-text"></i> Logs</a>
            </div>
        </div>
    </div>
</nav>

<main class="container-main">
<!-- El contenido de cada vista empieza aquí -->