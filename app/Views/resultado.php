<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Resultado Consulta Natural</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-4">Respuesta a tu pregunta</h1>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= esc($error) ?></div>
      <a href="<?= base_url('chat') ?>" class="btn btn-secondary">Volver</a>
    <?php else: ?>

      <div class="mb-3">
        <strong>Pregunta:</strong> <?= esc($pregunta) ?>
      </div>

      <div class="mb-4">
        <strong>Consulta SQL generada:</strong>
        <pre class="bg-dark text-white p-3 rounded"><?= esc($consultaSQL) ?></pre>
      </div>

      <h3>Resultados:</h3>

      <?php if (count($filas) === 0): ?>
        <p>No se encontraron resultados.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead class="table-dark">
              <tr>
                <?php foreach ($columnas as $col): ?>
                  <th><?= esc($col) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($filas as $fila): ?>
                <tr>
                  <?php foreach ($columnas as $col): ?>
                    <td><?= esc($fila[$col]) ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <a href="<?= base_url('chat') ?>" class="btn btn-primary mt-4">Realizar otra consulta</a>
    <?php endif; ?>
  </div>
</body>
</html>
