<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consulta Natural</title>
</head>
<body>
  <h1>Preguntar al sistema</h1>
  <form action="/chat/consultar" method="post">
    <input type="text" name="pregunta" placeholder="¿Cuántos clientes nuevos hubo este año?" size="50" required />
    <button type="submit">Consultar</button>
  </form>
</body>
</html>
