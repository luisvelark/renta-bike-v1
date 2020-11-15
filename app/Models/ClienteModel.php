<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\MultaModel;

class ClienteModel extends Model
{

    protected $table      = 'cliente';
    protected $primaryKey = 'idUsuario';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['puntajeTotal', 'credito', 'suspendido', 'fechaInicioSuspencion', 'fechaFinSuspencion'];
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;


    public function obtenerMultas($id)
    {
        $this->multas = new MultaModel();
        return $this->multas->buscarMultasCliente($id);
    }

    public function obtenerCredito($id)
    {
        $credito = $this->where('idUsuario', $id)->first();
        return $credito['credito'];
    }

    public function obtenerCliente($dni)
    {
        $builder = $bd->table('usuario');
        $builder->select('idUsuario','nombre','apellido')->getCompiledSelect();
        $builder->getWhere(['dni'=>$dni]);
        $query = $builder->get();
        $resultados = $this->db->query($query);
        return $resultados->result();
    }
}
