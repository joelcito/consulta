<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Documento;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\PdfToText\Pdf;
use Smalot\PdfParser\Parser; // Esto es correcto

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function consulta(Request $request){

        $documentos = Documento::all();

        return view('chat.consulta')->with(compact('documentos'));
    }

    public function getResponse(Request $request)
    {

        // Ruta donde se encuentran tus archivos PDF
        $directoryPath = public_path('assets/docs'); // Ajusta la ruta según tu estructura

        // Verificar que la carpeta existe
        if (!is_dir($directoryPath)) {
            return response()->json(['error' => 'El directorio no existe.'], 404);
        }

        // Obtener todos los archivos PDF del directorio
        $files = glob($directoryPath . '/*.pdf');
        $responses = []; // Almacenar las respuestas de cada archivo PDF

        // Crear un nuevo objeto Parser
       $parser = new Parser();

       // Recorrer los archivos y extraer el texto
       foreach ($files as $filePath) {
           // Extraer el texto del PDF
            try {
                $pdf     = $parser->parseFile($filePath);  // Asegúrate de que esto se llame correctamente
                $pdfText = $pdf->getText();

                 // Convertir el texto extraído a UTF-8
                $pdfText = mb_convert_encoding($pdfText, 'UTF-8', 'auto');

                // // Eliminar caracteres no válidos
                // $pdfText = iconv('UTF-8', 'UTF-8//IGNORE', $pdfText);

            } catch (\Exception $e) {
                return response()->json(['error' => 'Error al procesar el PDF: ' . $e->getMessage()], 500);
            }

           // Aquí iría la lógica para enviar el texto a OpenAI
           $response = $this->sendToOpenAI($request->input('message'), $pdfText);

           // Guardar la respuesta
           $responses[] = [
               'file_name' => basename($filePath), // Obtiene el nombre del archivo
               'response' => $response,
           ];
       }

       // Retornar todas las respuestas en formato JSON
       return response()->json(['responses' => $responses]);




        // DE AQUI PARA ABAJO ESTA FUNCIONANDO
        // // Clave API de OpenAI
        // $apiKey = env('OPENAI_API_KEY');  // Añadir la clave en el archivo .env

        // // Crear cliente Guzzle para realizar solicitudes HTTP
        // $client = new Client();

        // // Configurar la solicitud a la API de OpenAI
        // $response = $client->post('https://api.openai.com/v1/chat/completions', [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . $apiKey,
        //         'Content-Type'  => 'application/json',
        //     ],
        //     'json' => [
        //         'model' => 'gpt-3.5-turbo',  // Puedes usar 'gpt-4' si tienes acceso a ese modelo
        //         'messages' => [
        //             [
        //                 'role' => 'system',
        //                 'content' => 'Que dia es hoy'
        //             ],
        //             [
        //                 'role' => 'user',
        //                 'content' => $request->input('message')  // Mensaje del usuario
        //             ]
        //         ]
        //     ]
        // ]);

        // // Obtener la respuesta
        // $body = json_decode($response->getBody(), true);

        // // Retornar la respuesta en formato JSON
        // return response()->json([
        //     'response' => $body['choices'][0]['message']['content']
        // ]);
    }

    private function sendToOpenAI($consulta , $pdfText)
    {

        // Clave API de OpenAI desde el archivo .env
        $apiKey = env('OPENAI_API_KEY');
        $client = new Client();

        // Dividir el texto del PDF en partes manejables (ejemplo: fragmentos de 2000 palabras)
        $parts = str_split($pdfText, 2000); // Ajusta según el límite de tokens y palabras

        // Crear el array de mensajes
        $messages = [
            [
                'role' => 'system',
                'content' => $consulta
            ]
        ];

         // Añadir cada parte como un mensaje
        foreach ($parts as $part) {

            // echo "<br><br><br><br>-------------------------------------";
            // var_dump($part);

            // Filtra caracteres no válidos
            $part = preg_replace('/[^\x20-\x7E]/', '', $part); // Remueve caracteres no imprimibles

            // Asegúrate de que el texto sea UTF-8
            $part = mb_convert_encoding($part, 'UTF-8', 'UTF-8');


            $messages[] = [
                'role' => 'user',
                'content' => $part
            ];
        }

        // dd($messages);
        var_dump($messages);

        // Configurar la solicitud a la API de OpenAI
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                // 'model'    => 'gpt-3.5-turbo',   // O 'gpt-4' si tienes acceso
                'model'    => 'gpt-4',   // O 'gpt-4' si tienes acceso
                // 'model'    => 'gpt-4-32k',   // O 'gpt-4' si tienes acceso
                'messages' => $messages
            ]
        ]);

        // Obtener la respuesta
        $body = json_decode($response->getBody(), true);

        // Retornar el contenido del resumen
        return $body['choices'][0]['message']['content'];


        // // Clave API de OpenAI desde el archivo .env
        // $apiKey = env('OPENAI_API_KEY');
        // $client = new Client();

        // // Configurar la solicitud a la API de OpenAI
        // $response = $client->post('https://api.openai.com/v1/chat/completions', [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . $apiKey,
        //         'Content-Type' => 'application/json',
        //     ],
        //     'json' => [
        //         'model' => 'gpt-3.5-turbo',  // O 'gpt-4' si tienes acceso
        //         'messages' => [
        //             [
        //                 'role' => 'system',
        //                 // 'content' => 'Resumen del siguiente texto del PDF.'
        //                 'content' => $consulta
        //             ],
        //             [
        //                 'role' => 'user',
        //                 'content' => $pdfText  // Texto del PDF a resumir
        //             ]
        //         ]
        //     ]
        // ]);

        // // Obtener la respuesta
        // $body = json_decode($response->getBody(), true);

        // // Retornar el contenido del resumen
        // return $body['choices'][0]['message']['content'];
    }

    // // ESTO ES PARA EL CHAT PDF
    // private function subirPDF($pdfPath) {
    //     $response = Http::attach(
    //         'file', file_get_contents($pdfPath), 'archivo.pdf'
    //     )->withHeaders([
    //         'X-API-Key' => env('CHATPDF_API_KEY'),  // Coloca tu API key
    //     ])->post('https://api.chatpdf.com/chats/upload');

    //     if ($response->successful()) {
    //         return $response->json()['docId'];  // Guarda el docId para consultas
    //     }

    //     return null;
    // }

    // private function consultarPDF($docId, $message) {
    //     $response = Http::withHeaders([
    //         'X-API-Key' => env('CHATPDF_API_KEY'),  // Coloca tu API key
    //     ])->post('https://api.chatpdf.com/chats/message', [
    //         'docId' => $docId,
    //         'message' => $message,
    //         'save_chat' => true,
    //         'use_gpt4' => true,
    //     ]);

    //     if ($response->successful()) {
    //         return $response->json();  // Aquí recibes la respuesta de la consulta
    //     }

    //     return null;
    // }

    // public function handlePDFRequest(Request $request)
    // {
    //     // Sube el archivo PDF
    //     // $docId = $this->subirPDF(storage_path('app/pdfs/mi_archivo.pdf'));  // Ruta del PDF
    //     $docId = $this->subirPDF(public_path('assets/docs/1.-LEY_N°_843-08-24_unlocked.pdf'));  // Ruta del PDF

    //     dd($docId);

    //     // Realiza una consulta sobre el PDF
    //     if ($docId) {
    //         $respuesta = $this->consultarPDF($docId, '¿Qué dice el primer párrafo?');
    //         return response()->json($respuesta);
    //     }

    //     return response()->json(['error' => 'Error al subir o consultar el PDF'], 500);
    // }


    public function subirArchivo(Request $request)
    {
        // Ruta del archivo PDF en tu proyecto
        $rutaArchivo = public_path('assets/docs/1.-LEY_N°_843-08-24_unlocked.pdf'); // Ajusta esta ruta según sea necesario

        // Crear un cliente Guzzle
        $client = new Client();

        // Configurar los encabezados
        $headers = [
            // 'clave-x-api' => env('CHATPDF_API_KEY'), // Reemplaza con tu clave API
            'x-api-key' => "sec_C9VlPVIwkXzkuEfOxBsCbKZnQgZC8sqt", // Reemplaza con tu clave API
            "Content-Type"=> "application/json",
        ];

        try {
            // Realizar la solicitud POST
            $response = $client->post('https://api.chatpdf.com/v1/sources/add-file', [
                'headers' => $headers,
                'multipart' => [
                    [
                        'name' => 'archivo',
                        'contents' => fopen($rutaArchivo, 'r'), // Abrir el archivo PDF
                        'filename' => basename($rutaArchivo), // Nombre del archivo
                    ],
                ],
            ]);

            // Obtener el cuerpo de la respuesta
            $data = json_decode($response->getBody()->getContents(), true);
            return response()->json(['sourceId' => $data['sourceId']]);
        } catch (RequestException $e) {
            // Manejo de errores
            if ($e->hasResponse()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'response' => json_decode($e->getResponse()->getBody()->getContents(), true),
                ], $e->getResponse()->getStatusCode());
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function enviarConsulta()
    {
        // Configurar el encabezado de la solicitud
        $headers = [
            'x-api-key'    => 'sec_C9VlPVIwkXzkuEfOxBsCbKZnQgZC8sqt',   // Reemplaza con tu clave API
            'Content-Type' => 'application/json',
        ];

        // Preparar los datos del cuerpo de la solicitud
        $data = [
            'sourceId' => 'src_R7WPDJFiRwzGsXoLn8lpL', // Reemplaza con el ID correcto
            'messages' => [
                [
                    'role' => 'user',
                    'content' => '¿Cual es el ARTÍCULO 41?',
                ],
            ],
        ];

        try {
            // Realizar la solicitud POST
            $response = Http::withHeaders($headers)
                ->post('https://api.chatpdf.com/v1/chats/message', $data);

            // Comprobar si la respuesta fue exitosa
            if ($response->successful()) {
                // Obtener el contenido de la respuesta
                $result = $response->json();
                return response()->json([
                    'content' => $result['content']
                ]);
            } else {
                // Manejar errores si la respuesta no fue exitosa
                return response()->json([
                    'error' => 'Error en la consulta a la API',
                    'details' => $response->json(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Manejo de excepciones si ocurre algún error en la solicitud
            return response()->json([
                'error' => 'Ocurrió un error al hacer la solicitud',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
