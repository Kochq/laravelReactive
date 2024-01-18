<?php

namespace App\Http\Controllers;

function diferenciaHoraReporte(string $hora, string $dia) : bool {
    [$h,$m,$s] = explode(":", $hora);
    [$d,$mo,$y] = explode("/", $dia);

    $hora = mktime(intval($h+3), intval($m), intval($s), intval($mo), intval($d), intval($y));
    $timeDifference = (time() - $hora) / 60; // Diferencia en minutos
    return $timeDifference > 60; // Paso mas de una hora sin reportar
}

function procesar_pos(string $signo, string $lat) : float {
    if($signo == "S" || $signo = "W") {
        $signo = -1;
    } else {
        $signo = 1;
    }

    $grados = floatVal(substr($lat,strpos($lat,".") - 4 , 2));
    $minutos = floatVal(substr($lat,strpos($lat,".") - 2 , 8));
    $decimas_de_grado = $minutos / 60;

    $pos_procesada = $signo * ($grados + $decimas_de_grado);
    $pos_procesada = substr($pos_procesada, 0, 8);

    return $pos_procesada;
}

class TxtController extends Controller {
    public function ultimoReporteCliente(string $carpeta) {
        $setupTxt = file("http://relevar.com.ar/datos/riego/$carpeta/setup.txt");
        $nrosTxt = file("http://relevar.com.ar/datos/riego/$carpeta/NROS_SMS.txt");
        $generalTxt = file("http://relevar.com.ar/datos/riego/$carpeta/reportes/general.txt");
        $generalExtTxt = file("http://relevar.com.ar/datos/riego/$carpeta/reportes/general_extremo.txt");

        $fullObj = (object) array();


        for($i=0; $i<count($generalTxt)-2; $i++) { // count($generalTxt)-2 porque hay 1 equipo de más
            // Txt como arrays
            $data = explode(",", $generalTxt[$i]);
            $dataExt = explode(",", $generalExtTxt[$i]);
            $nombreEq = explode(",", $nrosTxt[$i])[0];

            [$nroEq, $latCen] = explode(":", $data[0]);
            $latCenNS = $data[1];
            $latExt = explode(":", $dataExt[0])[1];
            $latExtNS = $dataExt[1];
            $lngExt = $dataExt[2];
            $lngExtEO = $dataExt[3];
            $lngCen = $data[2];
            $lngCenEO = $data[3];
            $tipoEq = explode(",", $setupTxt[41])[$i];
            $numTele = explode(",", $setupTxt[29])[$i];
            $porcentajeAvance = (float) explode(".", $data[10])[2];
            $fw = $data[12][0]; $rv = $data[12][1]; $ut = $data[12][2];
            $dia = $data[6];
            $hora = $data[7];
            $id = "eq".$nroEq;

            $tipoTelemetria = match($numTele) {
                "0" => "bomba",
                "2", "3" => "standard",
                "4" => "full",
                "5" => "alarma",
            };

            if($fw == "1") {
                $direccion = "FW";
                $texto = "En forward al " . $porcentajeAvance . "%";
            } else if($rv == "1") {
                $direccion = "RV";
                $texto = "En reversa al " . $porcentajeAvance . "%";
            } else {
                $direccion = null;
                $texto = "Detenido";
            }

            if($tipoEq == "B") {
                if($ut == "1") {
                    $texto = "Bomba encendida";
                } else {
                    $texto = "Bomba apagada";
                }
            }

            $diferenciaReporte = diferenciaHoraReporte($hora, $dia);

            $obj = (object) array();

            $obj->nombre = $nombreEq;
            $obj->texto = $texto;
            $obj->latCen = procesar_pos($latCenNS, $latCen);
            $obj->latExt = procesar_pos($latExtNS, $latExt);
            $obj->lngCen = procesar_pos($lngCenEO, $lngCen);
            $obj->lngExt = procesar_pos($lngExtEO, $lngExt);
            $obj->tipoEq = $tipoEq;
            $obj->tipoTele = $tipoTelemetria;
            $obj->direccion = $direccion;
            $obj->rumbo = (float) $data[5];
            $obj->nroEq = (float) $nroEq;
            $obj->dia = $dia;
            $obj->hora = $hora;
            $obj->reportando = !$diferenciaReporte;
            $obj->regando = true ? $data[8] == "00" : false;
            $obj->porcentajeAvance = $porcentajeAvance;
            $obj->presion = (float) $data[15];
            $obj->tension = (float) $data[16];
            $obj->horimetro = (float) $data[20];

            $fullObj->$id = $obj;
        }

        echo json_encode($fullObj);
    }

    public function resumenUltimoReporteCliente(string $carpeta) {
        $generalTxt = file("http://relevar.com.ar/datos/riego/$carpeta/reportes/general.txt");

        $estaRegando = 0;
        $noEstaRegando = 0;
        $estaReportando = 0;
        $noEstaReportando = 0;
        $estaEncendido = 0;
        $noEstaEncendido = 0;
        foreach($generalTxt as &$data) {
            $data = explode(",", $data);
            if(!isset($data[1])) {
                continue;
            }

            $regando = ($data[8] == "00");
            if($regando) {
                $estaRegando++;
            } else {
                $noEstaRegando++;
            }

            $ultimoReporteHora = $data[7];
            $ultimoReporteDia = $data[6];
            if(diferenciaHoraReporte($ultimoReporteHora, $ultimoReporteDia)) {
                $noEstaReportando++;
            } else {
                $estaReportando++;
            }

            $direction = $data[4];
            if($direction == "--" && !$regando) {
                $noEstaEncendido++;
            } else {
                $estaEncendido++;
            }
        }

        $fullObj = (object) array();
        $fullObj->estaReportando = $estaReportando;
        $fullObj->noEstaReportando = $noEstaReportando;
        $fullObj->estaRegando = $estaRegando;
        $fullObj->noEstaRegando = $noEstaRegando;
        $fullObj->estaEncendido = $estaEncendido;
        $fullObj->noEstaEncendido = $noEstaEncendido;

        echo json_encode($fullObj);
    }

    public function resumenUltimoReporteClientePedido(string $carpeta, string $pedido) {
        $generalTxt = file("http://relevar.com.ar/datos/riego/$carpeta/reportes/general.txt");
        $nombresTxt = file("http://relevar.com.ar/datos/riego/$carpeta/NROS_SMS.txt");
        //$generalTxt = file("../generalTxt.txt");
        $nombres = array();


        foreach($nombresTxt as &$line) {
            $nombres[] = explode(",", $line)[0];
        }

        $fullObj = array();

        foreach($generalTxt as &$data) {
            $data = explode(",", $data);
            if(!isset($data[1])) {
                continue;
            }

            [$id, $lat] = explode(":", $data[0]);

            $obj = (object) array();

            $obj->id = $id;
            $obj->nombreEq = $nombres[$id-1];
            $obj->lat = $lat;
            $obj->lng = $data[2];
            $obj->direction = $data[4];
            $obj->rumbo = $data[5];
            $obj->dia = $data[6];
            $obj->hora = $data[7];
            $obj->regando = $data[8] == "00";
            $obj->porcentajeAvance = explode(".", $data[10])[2];
            $obj->direccion2 = $data[12]; // FW, RV, UT
            $obj->presion = $data[15];
            $obj->tension = $data[16];
            $obj->horimetro = $data[20];


            switch($pedido) {
                case "regando": {
                    if($obj->regando) {
                        $obj->stat = true;
                    } else {
                        $obj->stat = false;
                    }
                    break;
                }
                case "reportando": {
                    if(diferenciaHoraReporte($obj->hora, $obj->dia)) {
                        $obj->stat = false;
                    } else {
                        $obj->stat = true;
                    }
                    break;
                }
                case "encendidos": {
                    if($obj->direction == "--" && !$obj->regando) {
                        $obj->stat = false;
                    } else {
                        $obj->stat = true;
                    }
                    break;
                }
            }
            $fullObj[] = $obj;
        }

        echo json_encode($fullObj);
    }
}

