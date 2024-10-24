<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Documento;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
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

        // Configurar la solicitud a la API de OpenAI
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',  // O 'gpt-4' si tienes acceso
                'messages' => [
                    [
                        'role' => 'system',
                        // 'content' => 'Resumen del siguiente texto del PDF.'
                        'content' => $consulta
                    ],
                    [
                        'role' => 'user',
                        'content' => $pdfText  // Texto del PDF a resumir
                    ]
                ]
            ]
        ]);

        // Obtener la respuesta
        $body = json_decode($response->getBody(), true);

        // Retornar el contenido del resumen
        return $body['choices'][0]['message']['content'];
    }
}
