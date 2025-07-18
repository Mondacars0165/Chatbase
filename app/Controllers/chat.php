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
        $pregunta = $this->request->getPost('pregunta');

        // Ejecutar el script de Python con la pregunta
        $output = shell_exec("python3 C:\Users\crist\OneDrive\Desktop\proyecto\python\test_llm.py \"$pregunta\"");

        // Asegúrate de sanitizar antes si expones esto
        $consultaSQL = trim($output);

        // Conectarse a Oracle y ejecutar consulta
        $conn = oci_connect('usuario', 'clave', 'localhost/XEPDB1');
        if (!$conn) {
            die("Conexión fallida.");
        }

        $stid = oci_parse($conn, $consultaSQL);
        oci_execute($stid);

        echo "<h3>Resultado:</h3><pre>";
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            print_r($row);
        }
        echo "</pre>";

        oci_free_statement($stid);
        oci_close($conn);
    }
}
