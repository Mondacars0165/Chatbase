<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Chat extends Controller
{
    public function index()
    {
        session(); // inicia sesión
        return view('chat_form');  // muestra el chat al usuario
    }

    public function consultar()
    {
        helper('text');
        $session = session();
        set_time_limit(300);

        $pregunta = $this->request->getPost('pregunta');
        if (empty($pregunta)) {
            return $this->responder(['error' => 'No se recibió ninguna pregunta.']);
        }

        // Llamar a API externa que genera SQL
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
            return $this->responder(['error' => "Error cURL al llamar a la API: $error_msg"]);
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            return $this->responder(['error' => "Error HTTP: código $httpCode. Respuesta: $response"]);
        }

        $json = json_decode($response, true);
        if (!isset($json['sql'])) {
            return $this->responder(['error' => 'No se recibió SQL válido de la API.']);
        }

        $consultaSQL = trim($json['sql']);
        $consultaSQL = rtrim($consultaSQL, " \t\n\r\0\x0B;");

        if (!preg_match('/^\s*SELECT\s+/i', $consultaSQL)) {
            return $this->responder(['error' => 'Consulta SQL no válida o no permitida. Solo se permiten SELECT.']);
        }

        // Ejecutar SQL en Oracle
        $conn = oci_connect('system', 'oracle123', '//localhost:1523/freepdb1', 'AL32UTF8');
        if (!$conn) {
            $e = oci_error();
            return $this->responder(['error' => "Conexión fallida a Oracle: {$e['message']}"]);
        }

        $stid = oci_parse($conn, $consultaSQL);
        if (!$stid) {
            $e = oci_error($conn);
            return $this->responder(['error' => "Error al preparar la consulta: {$e['message']}"]);
        }

        $r = oci_execute($stid);
        if (!$r) {
            $e = oci_error($stid);
            return $this->responder(['error' => "Error al ejecutar la consulta: {$e['message']}"]);
        }

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

        // Guardar historial en sesión
        $nuevoRegistro = [
            'pregunta' => $pregunta,
            'sql' => $consultaSQL,
            'columnas' => $columnas,
            'filas' => $filas,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $historial = $session->get('chat_historial') ?? [];
        $historial[] = $nuevoRegistro;
        $session->set('chat_historial', $historial);

        return $this->responder([
            'pregunta' => $pregunta,
            'sql' => $consultaSQL,
            'columnas' => $columnas,
            'filas' => $filas,
        ]);
    }

    private function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
        }
        return $mixed;
    }

    private function responder(array $data)
    {
        // Forzar UTF-8 a todo el arreglo antes de enviar JSON
        $data = $this->utf8ize($data);

        return $this->response->setJSON($data);
    }

    public function historial()
    {
        $session = session();
        $historial = $session->get('chat_historial') ?? [];
        return $this->response->setJSON($historial);
    }
}
