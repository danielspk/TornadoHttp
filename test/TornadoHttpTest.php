<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use Zend\ServiceManager\ServiceManager;

class TornadoHttpTest extends PHPUnit_Framework_TestCase
{
    public function testTornadoHttpInstance()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructMiddleware()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            $middleware
        ]);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructContainer()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp(
            [$middleware],
            new ServiceManager()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testResponseMiddleware()
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

    public function testGetMiddlewares()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $middlewares = $tornadoHttp->getMiddlewares();

        $this->assertInstanceOf('\SplQueue', $middlewares);
    }

    public function testAddMiddlewares()
    {
        $middleware1 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $next($request, $response);
        };

        $middleware2 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();

        $tornadoHttp->add($middleware1);
        $tornadoHttp->add($middleware2);

        $middlewares = $tornadoHttp->getMiddlewares();

        $this->assertCount(2, $middlewares);
    }

    public function testSetGetDI()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $container = $tornadoHttp->getDI();

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $container);
    }

    public function testResolveCallableString()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();

        $callable = $tornadoHttp->resolveCallable('Classes\TestMiddleware');

        $this->assertInstanceOf('\Classes\TestMiddleware', $callable);
    }

    public function testResolveCallableArray()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();

        $callable = $tornadoHttp->resolveCallable(['Classes\TestParamMiddleware', [1, 2]]);

        $this->assertInstanceOf('\Classes\TestParamMiddleware', $callable);
    }

    public function testResolveCallableCallable()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();

        $callable = $tornadoHttp->resolveCallable(function() {});

        $this->assertInternalType('callable', $callable);
    }
}