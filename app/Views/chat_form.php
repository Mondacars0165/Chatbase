<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Consulta Natural - Sistema SQL AI</title>
  <style>
    /* Reset básico */
    *, *::before, *::after {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f7f9fc;
      color: #333;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 2rem;
    }
    main {
      background: #fff;
      max-width: 600px;
      width: 100%;
      padding: 2.5rem 3rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      text-align: center;
    }
    h1 {
      margin-bottom: 1.5rem;
      font-weight: 700;
      font-size: 2rem;
      color: #0052cc;
    }
    form {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      justify-content: center;
    }
    input[type="text"] {
      flex: 1 1 300px;
      padding: 0.75rem 1rem;
      font-size: 1.1rem;
      border: 2px solid #0052cc;
      border-radius: 8px;
      transition: border-color 0.3s ease;
    }
    input[type="text"]:focus {
      outline: none;
      border-color: #003d99;
      box-shadow: 0 0 5px #003d99aa;
    }
    button {
      padding: 0.75rem 1.8rem;
      font-size: 1.1rem;
      font-weight: 700;
      color: #fff;
      background: #0052cc;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      flex-shrink: 0;
    }
    button:hover,
    button:focus {
      background: #003d99;
      outline: none;
    }
    @media (max-width: 400px) {
      form {
        flex-direction: column;
      }
      button {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <main role="main" aria-label="Formulario para consultar base de datos con lenguaje natural">
    <h1>Consulta Natural al Sistema</h1>
    <form action="/chat/consultar" method="post" autocomplete="off" novalidate>
      <input 
        type="text" 
        name="pregunta" 
        placeholder="Ejemplo: ¿Cuántos clientes nuevos hubo este año?" 
        size="50" 
        required 
        aria-label="Pregunta para consultar la base de datos"
        autofocus
      />
      <button type="submit" aria-label="Enviar consulta">Consultar</button>
    </form>
  </main>
</body>
</html>
