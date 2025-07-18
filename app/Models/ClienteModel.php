<?php namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table = 'SYSTEM.CLIENTES';
    protected $primaryKey = 'ID';
    protected $allowedFields = ['NOMBRE', 'FECHA_INGRESO'];

    public function contarClientesPorAno($ano)
    {
        $builder = $this->builder();
        $builder->selectCount('ID');
        $builder->where("EXTRACT(YEAR FROM FECHA_INGRESO) = $ano");
        $result = $builder->get()->getRowArray();
        return $result['COUNT(ID)'] ?? 0;
    }
}
