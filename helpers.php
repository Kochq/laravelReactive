<?php

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

?>
