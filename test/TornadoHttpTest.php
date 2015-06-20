<?php

class TornadoHttpTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }
}