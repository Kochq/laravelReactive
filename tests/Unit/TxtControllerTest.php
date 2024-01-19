<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TxtControllerTest extends TestCase
{
    public function testDiferenciaHoraReporte()
    {
        // Test the diferenciaHoraReporte function with various inputs
        $this->assertTrue(diferenciaHoraReporte('12:00:00', '01/01/2022'));
        $this->assertFalse(diferenciaHoraReporte(date('H:i:s'), date('d/m/Y')));
    }

    public function testProcesarPos()
    {
        // Test the procesar_pos function with various inputs
        $this->assertEquals(-34.0728, procesar_pos('S', '3404.3714'));
        $this->assertEquals(-60.3232, procesar_pos('N', '06019.3936'));
    }
}
