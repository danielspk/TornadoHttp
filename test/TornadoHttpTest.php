<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

class TornadoHttpTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([]);
        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testMiddleware()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            $middleware
        ]);

        $response = $tornadoHttp(ServerRequestFactory::fromGlobals(), new Response());

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }

    public function testMiddlewares()
    {
        $middleware1 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $next($request, $response);
        };

        $middleware2 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            $middleware1,
            $middleware2
        ]);

        $response = $tornadoHttp(ServerRequestFactory::fromGlobals(), new Response());

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }
}