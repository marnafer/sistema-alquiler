<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Propiedad</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 20px auto; line-height: 1.6; }
        div { margin-bottom: 10px; }
        label { display: block; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; }
        button { background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Cargar Nueva Propiedad</h1>
    <form action="/sistema-alquiler/propiedades" method="POST">
        <div>
            <label>Título:</label>
            <input type="text" name="titulo" required minlength="5" maxlength="30">
        </div>
        <div>
            <label>Precio:</label>
            <input type="number" name="precio" required min="1">
        </div>
        <div>
            <label>Ubicación:</label>
            <input type="text" name="ubicacion" required minlength="10">
        </div>
        <button type="submit">Guardar Propiedad</button>
    </form>
</body>
</html>