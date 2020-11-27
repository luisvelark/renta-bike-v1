<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\BicicletaController;
use App\Controllers\ClienteController;
use App\Controllers\MultaController;
use App\Controllers\PuntajeController;
use App\Controllers\PuntoEDController;
use App\Models\AlquilerModel;
use CodeIgniter\HTTP\Request;

class AlquilerController extends BaseController
{

    /* protected $alquilerModel; *///cambiar a protected
    protected $request;
    // protected $controlPED;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->alquilerModel = new AlquilerModel();
        $this->controlPED = new PuntoEDController();
        $this->cMulta = new MultaController();
        $this->cBicicleta = new BicicletaController();
        $this->cPuntaje = new PuntajeController();
        $this->cCliente = new ClienteController();

    }

    public function solicitarAlquiler()
    {

        $puntoE = $this->request->getPost('punto-entrega');
        $horaInicio = $this->request->getPost('hora-inicio');
        $cantHoras = $this->request->getPost('cant-hora');
        $dniAlternativo = $this->request->getPost('dni-optativo');

        if ($puntoE === '---' || empty($horaInicio) || $cantHoras === '---' || empty($puntoE) || empty($cantHoras)) {

            $arr = ["msg" => "error"];
        } else {

            $puntoYBici = $this->controlPED->biciDisponibles(intval($puntoE));

            $sesion = session();
            $sesion->set($puntoYBici);
            $idUsuario = $sesion->get('idUsuario');
            $nombreUser = $sesion->get('nombre');
            $apellidoUser = $sesion->get('apellido');

            $alquiler = [
                'idUsuarioCliente' => $idUsuario,
                'idBicicleta' => $puntoYBici['idBici'],
                'idPuntoE' => intval($puntoE),
                'idPuntoD' => 2,
                'fechaAlquiler' => date("Y-m-d"),
                'horaInicioAlquiler' => date("H:i:s", strtotime($horaInicio)),
                'HoraFinAlquiler' => calcularSumaHoras($horaInicio, $cantHoras),
                'HoraEntregaAlquiler' => " ",
                'clienteAlternativo' => intval($dniAlternativo),
                'estadoAlquiler' => 'Activo',
                'daño' => $puntoYBici['dañoBici'],
                'ruta' => 'la ruta',

            ];

            if ($this->alquilerModel->buscarAlquilerActivo($idUsuario) == null) {

                $this->alquilerModel->crearAlquiler($alquiler);
                $sesion->set('activo', '1');
                $this->cBicicleta->cambiarEstadoBicicleta($puntoYBici['idBici'], 'EnAlquiler');
            } else {
                $elId = $this->alquilerModel->buscarIdAlquilerDelEstado($idUsuario, 'Activo');
                $this->alquilerModel->actualizarAlquiler($elId, $alquiler);
                $sesion->set('activo', '1');

            }

            $arr = [
                "msg" => 'Su reserva se realizo con éxito!',
                "detalle" => $alquiler,
                "usuario" => [
                    "nombre" => $nombreUser,
                    "apellido" => $apellidoUser,
                ],
                "puntoYBici" => $puntoYBici,
            ];
        }

        echo json_encode($arr);
        die();
    }
    public function mostrarFecha()
    {
        //$dt = new DateTime($_POST['fechaInicio']);
        //$fechaInicio=$dt->format('Y-m-d');
        //$fechaInicio=date_create_from_format("Y-m-d", $_POST['fechaInicio']);
        //$date = new DateTime($_POST['fechaFinal']);
        //$fechaFinal=$date->format('Y-m-d');
        //$fechaFinal=date_create_from_format("Y-m-d", $_POST['fechaFinal']);
        $fechaInicio = $_POST['fechaInicio'];
        $fechaFinal = $_POST['fechaFinal'];
        if($fechaInicio>$fechaFinal){
            $datos = 'errorFecha';
            echo json_encode($datos);
            die();
        }else{
            $datos = ['horasMayorDemanda' => $this->alquilerModel->obtenerHoraInicio($fechaInicio, $fechaFinal)];
            //$datos= ['fechaInicio'=>$fechaInicio,'fechaFinal'=>$fechaFinal];
            if (isset($datos['horasMayorDemanda']) && $datos==false) {
                echo json_encode($datos);
                die();
    
            } else {
                $datos = 'error';
                echo json_encode($datos);
                die();
            }
        }
    }
    public function mostrarTiempoAlquiler()
    {
        $fechaInicio = $_POST['fechaInicio'];
        $fechaFinal = $_POST['fechaFinal'];
        if($fechaInicio>$fechaFinal){
            $datos = 'errorFecha';
            echo json_encode($datos);
            die();
        }else{
            $datos = ['tiempoAlquiler' => $this->alquilerModel->obtenerTiempoAlquiler($fechaInicio, $fechaFinal)];
            if (isset($datos['tiempoAlquiler']) && $datos==false) 
            {
                echo json_encode($datos);
                die();
            } else {
                $datos = 'error';
                echo json_encode($datos);
                die();
            }
        }
    }
    public function mostrarPuntoRetorno()
    {
        $fechaInicio = $_POST['fechaInicio'];
        $fechaFinal = $_POST['fechaFinal'];
        if($fechaInicio>$fechaFinal){
            $datos = 'errorFecha';
            echo json_encode($datos);
            die();
        }else{
            $datos = ['puntosRetorno' => $this->alquilerModel->obtenerPuntosRetorno($fechaInicio, $fechaFinal)];
            if (isset($datos['puntosRetorno']) && $datos==false) {
                echo json_encode($datos);
                die();
            } else {
                $datos = 'error';
                echo json_encode($datos);
                die();
            }
        }
    }

    public function soliticaReportarDaños()
    {
        if ($this->request->getMethod() == "post") {
            $idUsuarioActual = $this->request->getPost('idUsuarioOculto');
            $alquilerActivo = $this->alquilerModel->buscarAlquilerActivo($idUsuarioActual);
            $idBicicleta = $alquilerActivo['idBicicleta'];

            if ($this->alquilerModel->buscarUltimoAlquilerPorBicicleta($idBicicleta) == null) {
                $mensaje = ['msjReportar' => '¡No existe un alquiler anterior a éste!'];
                echo view('index-cliente', $mensaje);
            } else {
                $alquilerUltimo = $this->alquilerModel->buscarUltimoAlquilerPorBicicleta($idBicicleta);
                $idUsuarioUltimo = $alquilerUltimo['idUsuarioCliente'];
                $precio = 25000;
                $this->cMulta->multa->crearMulta($idUsuarioUltimo, $this->request->getPost('comboDaño'), $precio);
                $this->cBicicleta->bicicleta->cambiarEstado($idBicicleta, 'EnReparacion');
                $this->cBicicleta->bicicleta->aplicarDaño($idBicicleta, $this->request->getPost('comboDaño'));
                if ($this->controlPED->biciDisponibles($alquilerActivo['idPuntoE']) != null) {

                    $puntoYBici = $this->controlPED->biciDisponibles($alquilerActivo['idPuntoE']);
                    $idBicicletaNueva = $puntoYBici['idBici'];
                    $this->alquilerModel->reemplazarBicicleta($alquilerActivo['idAlquiler'], $idBicicletaNueva);
                    $this->cBicicleta->bicicleta->cambiarEstado($idBicicletaNueva, 'EnAlquiler');
                    $mensaje = ['msjReportar' => '¡Has reportado con éxito, se te asignó una nueva bicicleta!'];
                    echo view('index-cliente', $mensaje);
                } else {

                    $this->cPuntaje->puntaje->crearPuntaje($idUsuarioActual, 50, 'No hay otra bicicleta disponible');
                    $this->alquilerModel->cambiarEstado($alquilerActivo['idAlquiler'], 'Finalizado');
                    $mensaje = ['msjReportar' => '¡No hay otra bicicleta disponible, se dará por finalizado el alquiler!'];
                    $puntajeTotal = $this->cPuntaje->puntaje->buscarPuntos($idUsuarioActual);
                    $this->cCliente->cliente->actualizarPuntaje($idUsuarioActual, $puntajeTotal);
                    echo view('index-cliente', $mensaje);
                }
            }
        }
    }
    public function soliticaAnularAlquiler()
    {
        if ($this->request->getMethod() == "post") {

            $sesion = session();
            $idUsuario = $this->request->getPost('idUsuarioOculto');
            $alquilerActivo = $this->alquilerModel->buscarAlquilerActivo($idUsuario);
            $horaInicio = $alquilerActivo['horaInicioAlquiler'];

            $idBicicleta = $alquilerActivo['idBicicleta'];
            $this->cBicicleta->bicicleta->cambiarEstado($idBicicleta, 'Disponible');
            $this->alquilerModel->cambiarEstado($alquilerActivo['idAlquiler'], 'Anulado'); //Cambiar a finalizado
            $mensaje = ['msjAnular' => '¡Has anulado con éxito!'];
            $sesion->set('activo', '0');
            echo view('index-cliente', $mensaje);

        }
    }
    public function soliticaConfirmarAlquiler()
    {
        $sesion = session();
        $idUsuario = $sesion->get('idUsuario');
        $elId = $this->alquilerModel->buscarIdAlquilerDelEstado($idUsuario, 'Activo');
        $this->alquilerModel->cambiarEstado($elId, 'EnProceso');
        $sesion->set('activo', '2');
        $noti = ["msj" => "alquiler confirmado"];
        echo json_encode($noti);
        die();

    }

    public function cargarDatosConfirmarAlquiler()
    {
        $sesion = session();
        $idUsuario = $sesion->get('idUsuario');
        $nombreUser = $sesion->get('nombre');
        $apellidoUser = $sesion->get('apellido');
        $miAlquiler = $this->alquilerModel->buscarAlquilerActivo($idUsuario);
        $datos = ["alquiler" => $miAlquiler,
            "usuario" => ["nombre" => $nombreUser,
                "apellido" => $apellidoUser]];
        echo json_encode($datos);
        die();
    }
    public function mostrarPDF(){
        echo view('layouts/ver_horasDemanda');
    }
    
    public function generaPuntosPDF(){
        $pdf= new \FPDF('P','mm','letter');
        $pdf->AddPage();
        $pdf->SetMargins(10,10,10);
        $pdf->SetTitle("Reporte Punto de alquiler mas frecuente");
        $pdf->SetFont("Arial",'B',10);
        $this->response->setHeader('Content-Type','application/pdf');
        $pdf->Output('Prueba.pdf','I');    
    }
}
