<?php namespace App\Controllers;

use App\Models\ClienteModel;

class Chatbot extends BaseController
{
    public function clientesAno()
    {
        $clienteModel = new ClienteModel();
        $ano = date('Y');

        $cantidad = $clienteModel->contarClientesPorAno($ano);

        return $this->response->setJSON([
            'respuesta' => "Este año se unieron $cantidad clientes."
        ]);
    }
}
