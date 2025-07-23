<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Chat extends Controller
{
    public function index()
    {
        return view('chat_form');
    }

    public function consultar()
    {
        set_time_limit(300);  // 5 minutos

        $pregunta = $this->request->getPost('pregunta');

        if (empty($pregunta)) {
            return view('resultado', ['error' => 'No se recibió ninguna pregunta.']);
        }

        $url = "http://192.168.1.30:5000/generar_sql";
        $data = json_encode(['pregunta' => $pregunta]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return view('resultado', ['error' => "Error cURL al llamar a la API: $error_msg"]);
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            return view('resultado', ['error' => "Error HTTP: código $httpCode. Respuesta: $response"]);
        }

        $json = json_decode($response, true);

        if (!isset($json['sql'])) {
            return view('resultado', ['error' => 'No se recibió SQL válido de la API.']);
        }

        $consultaSQL = trim($json['sql']);
        $consultaSQL = rtrim($consultaSQL, " \t\n\r\0\x0B;");

        if (!preg_match('/^\s*SELECT\s+/i', $consultaSQL)) {
            return view('resultado', ['error' => 'Consulta SQL no válida o no permitida. Solo se permiten SELECT.']);
        }

        $conn = oci_connect('system', 'oracle123', '//localhost:1523/freepdb1');
        if (!$conn) {
            $e = oci_error();
            return view('resultado', ['error' => "Conexión fallida a Oracle: {$e['message']}"]);
        }

        $stid = oci_parse($conn, $consultaSQL);
        if (!$stid) {
            $e = oci_error($conn);
            return view('resultado', ['error' => "Error al preparar la consulta: {$e['message']}"]);
        }

        $r = oci_execute($stid);
        if (!$r) {
            $e = oci_error($stid);
            return view('resultado', ['error' => "Error al ejecutar la consulta: {$e['message']}"]);
        }

        // Construir datos resultado para la vista
        $columnas = [];
        $filas = [];

        $numCols = oci_num_fields($stid);
        for ($i = 1; $i <= $numCols; $i++) {
            $columnas[] = oci_field_name($stid, $i);
        }

        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $filas[] = $row;
        }

        oci_free_statement($stid);
        oci_close($conn);

        // Pasar todo a la vista
        return view('resultado', [
            'consultaSQL' => $consultaSQL,
            'columnas' => $columnas,
            'filas' => $filas,
            'pregunta' => $pregunta,
            'error' => null
        ]);
    }
}
