<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Asistente SQL Mejorado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .chat-container {
            max-width: 900px;
            width: 100%;
            height: 85vh;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .chat-header {
            background-color: #0d6efd;
            color: white;
            padding: 1rem 1.5rem;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            user-select: none;
        }

        .chat-mensajes {
            flex: 1;
            padding: 1rem 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            background-color: #f9fafb;
        }

        .mensaje {
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            max-width: 80%;
            word-break: break-word;
            white-space: pre-wrap;
            font-size: 0.95rem;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
        }

        .usuario {
            background-color: #d1e7dd;
            align-self: flex-end;
            text-align: right;
            color: #0f5132;
        }

        .bot {
            background-color: #e2e3e5;
            align-self: flex-start;
            color: #41464b;
        }

        .esperando {
            align-self: center;
            font-style: italic;
            color: #6c757d;
            user-select: none;
        }

        .chat-formulario {
            border-top: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            background-color: #fff;
            display: flex;
            gap: 0.75rem;
        }

        #pregunta {
            flex-grow: 1;
            font-size: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
            padding: 0.5rem 1rem;
            transition: border-color 0.3s ease;
        }

        #pregunta:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        button.btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            padding: 0 1rem;
            border-radius: 0.5rem;
        }

        /* Estilo para tabla resultado */
        .tabla-resultado {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .tabla-resultado th, 
        .tabla-resultado td {
            border: 1px solid #dee2e6;
            padding: 0.4rem 0.6rem;
            text-align: left;
        }

        .tabla-resultado thead {
            background-color: #0d6efd;
            color: white;
        }

        .tabla-resultado tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Scroll del chat */
        .chat-mensajes::-webkit-scrollbar {
            width: 8px;
        }

        .chat-mensajes::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .chat-container {
                height: 90vh;
                max-width: 100vw;
                border-radius: 0;
            }

            .mensaje {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="chat-container" role="main" aria-label="Asistente de Consultas SQL">
    <header class="chat-header">
        <i class="bi bi-terminal"></i> Asistente de Consultas SQL
    </header>

    <section id="chat" class="chat-mensajes" aria-live="polite" aria-relevant="additions">
        <div class="mensaje bot" role="alert">
            ¡Hola! 👋 Soy tu asistente SQL.<br />
            Escribe una pregunta en lenguaje natural, por ejemplo:<br />
            <strong>¿Cuántos empleados hay con salario mayor a 1 millón?</strong>
        </div>
    </section>

    <form id="formulario" class="chat-formulario" role="search" aria-label="Formulario para hacer preguntas">
        <input
            type="text"
            id="pregunta"
            aria-label="Pregunta en lenguaje natural"
            class="form-control"
            placeholder="Haz tu pregunta aquí..."
            autocomplete="off"
            required
            autofocus
        />
        <button class="btn btn-primary" type="submit" aria-label="Enviar pregunta">
            <i class="bi bi-send"></i>
        </button>
    </form>
</div>

<script>
    (() => {
        const chat = document.getElementById('chat');
        const formulario = document.getElementById('formulario');
        const input = document.getElementById('pregunta');

        formulario.addEventListener('submit', async e => {
            e.preventDefault();
            const texto = input.value.trim();
            if (!texto) return;

            agregarMensaje(texto, 'usuario');
            input.value = '';
            input.disabled = true;

            const espera = agregarEspera();

            try {
                const respuesta = await fetch('/chat/consultar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'pregunta=' + encodeURIComponent(texto),
                });

                if (!respuesta.ok) {
                    throw new Error(`Error HTTP: ${respuesta.status}`);
                }

                const data = await respuesta.json();

                espera.remove();
                input.disabled = false;
                input.focus();

                if (data.error) {
                    agregarMensaje('❌ ' + data.error, 'bot');
                } else {
                    agregarMensaje(`🧠 Consulta generada:\n${data.sql}`, 'bot');
                    if (data.filas.length === 0) {
                        agregarMensaje('⚠️ Sin resultados encontrados.', 'bot');
                    } else {
                        agregarMensaje(generarTabla(data.columnas, data.filas), 'bot-html');
                    }
                }
                chat.scrollTop = chat.scrollHeight;
            } catch (error) {
                espera.remove();
                input.disabled = false;
                agregarMensaje('❌ Error de conexión con el servidor.', 'bot');
            }
        });

        function agregarMensaje(texto, tipo) {
            const div = document.createElement('div');
            div.className = 'mensaje ' + tipo;
            if (tipo === 'bot-html') {
                div.innerHTML = texto;
            } else {
                div.textContent = texto;
            }
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }

        function agregarEspera() {
            const espera = document.createElement('div');
            espera.className = 'mensaje esperando';
            espera.textContent = '⏳ Generando consulta...';
            chat.appendChild(espera);
            chat.scrollTop = chat.scrollHeight;
            return espera;
        }

        function generarTabla(columnas, filas) {
            const table = document.createElement('table');
            table.className = 'tabla-resultado';

            // Crear encabezado
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            columnas.forEach(col => {
                const th = document.createElement('th');
                th.textContent = col;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Crear cuerpo
            const tbody = document.createElement('tbody');
            filas.forEach(fila => {
                const tr = document.createElement('tr');
                columnas.forEach(col => {
                    const td = document.createElement('td');
                    td.textContent = fila[col] ?? '';
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);

            return table.outerHTML;
        }
    })();
</script>

</body>
</html>
