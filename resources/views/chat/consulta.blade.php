<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatGPT Clone</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1E1E1E;
            color: white;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            background-color: #333;
            width: 260px;
            height: 100%;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            box-sizing: border-box;
            border-right: 1px solid #444;
        }
        .sidebar h2 {
            color: #ccc;
            margin-bottom: 30px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            color: #ccc;
            text-decoration: none;
            font-size: 16px;
        }
        .sidebar ul li a:hover {
            color: white;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
            height: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .search-bar {
            background-color: #333;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .search-bar input {
            width: 100%;
            background-color: transparent;
            border: none;
            color: white;
            padding: 10px;
            font-size: 16px;
            outline: none;
        }
        .search-bar button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .search-bar button:hover {
            background-color: #45a049;
        }
        .content-area {
            flex-grow: 1;
            overflow-y: auto;
        }
        .footer {
            background-color: #333;
            padding: 10px;
            text-align: center;
            color: #ccc;
        }




        .escribiendo {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            color: #ffffff;
            white-space: pre;
            border-right: 2px solid; /* Simula el cursor */
            width: fit-content;
            overflow: hidden;
        }
        @keyframes parpadeo {
            0% { border-color: transparent; }
            50% { border-color: black; }
            100% { border-color: transparent; }
        }
        .escribiendo::after {
            content: '';
            display: inline-block;
            width: 1px;
            background-color: black;
            animation: parpadeo 1s steps(2, start) infinite;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Menú</h2>
        <ul>
            <li><a href="#">Generar script DBeaver</a></li>
            <li><a href="#">Rastrear usuarios en cPanel</a></li>
            <li><a href="#">Error CORS en solicitud</a></li>
            <li><a href="#">Factorización de una expresión</a></li>
            @foreach ( $documentos as  $d)
                <li><a href="#">{{ $d->nombre }}</a></li>
            @endforeach
        </ul>
    </div>

    <div class="main-content">
        <div class="search-bar">
            <input type="text" placeholder="¿Con qué puedo ayudarte?" id="message" name="message">
            <button onclick="enviarChat()">Enviar</button>
        </div>

        <div class="content-area">
            <h1>Bienvenido al chat</h1>
            <p>Aquí irán los mensajes y otras interacciones, emulando el estilo de ChatGPT.</p>
        </div>
        <div class="content-area">
            <div class="escribiendo" id="texto">

            </div>
        </div>

        <div class="footer">
            ChatGPT Clone - Todos los derechos reservados.
        </div>
    </div>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>


    $(document).ready(function() {
        // const texto     = "Los colores de la bandera de Chile son el rojo, el blanco y el azul.";
        let   i         = 0;
        // const velocidad = 100; // Velocidad de escritura (en milisegundos)

        // function escribirTexto() {
        //     if (i < texto.length) {
        //         document.getElementById("texto").textContent += texto.charAt(i);
        //         i++;
        //         setTimeout(escribirTexto, velocidad);
        //     }
        // }

        // escribirTexto(); // Iniciar el efecto cuando cargue el DOM
    });

    // Función para escribir el texto carácter por carácter
    function metodoEscribiendo(texto, i, velocidad) {
        if (i < texto.length) {
            document.getElementById("texto").textContent += texto.charAt(i);
            i++;
            setTimeout(function() {
                metodoEscribiendo(texto, i, velocidad);
            }, velocidad);
        }
    }



    function enviarChat(){
        console.log("che");

        var message = $('#message').val();

        $.ajax({
            url: "{{ url('chat/getResponse') }}",
            type: 'POST',
            data: {
                message: message,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {

                // LIMPIAMOS LA CACHE
                $('#texto').html("")
                let mensajeDevuelto = data.response;  // Asignamos el mensaje devuelto por el servidor
                let velocidad = 100; // Velocidad de escritura (milisegundos)

                metodoEscribiendo(mensajeDevuelto, 0, velocidad);

                // $('#response').html('<p>Respuesta de ChatGPT: ' + data.response + '</p>');
            },
            error: function() {
                $('#response').html('<p>Error al obtener la respuesta de ChatGPT.</p>');
            }
        });
    }

</script>
</html>
