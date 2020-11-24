<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CalificacionModel;
use App\Controllers\PuntoEDController;

class CalificacionController extends BaseController
{

    public function __construct()
    {
        $this->calificacion = new CalificacionModel();
        $this->cPuntoED = new PuntoEDController();
    }

    public function calificar($idPunto = 1) //PASAR EL ID DEL PUNTO DE ENTREGA Y EL ID USUARIO
    {
        if ($this->request->getMethod() == "post") {

            if ($this->request->getPost('comentario') != null) {
                $this->calificacion->altaCalificacion($idPunto, 1, $this->request->getPost('estrellas'), $this->request->getPost('comentario'));
            } else {
                $this->calificacion->altaCalificacion($idPunto, 1, $this->request->getPost('estrellas'), "");
            }

            $promedio = $this->cPuntoED->puntoED->promediarCalificacion($this->calificacion->buscarCalificacionTotal($idPunto));
            $this->cPuntoED->puntoED->actualizarCalificacion($idPunto, $promedio);
            $puntaje = ['puntuacion' => $this->request->getPost('estrellas'), 'comentario' => $this->request->getPost('comentario')];
            echo view('index-cliente', $puntaje);
        }
    }
}