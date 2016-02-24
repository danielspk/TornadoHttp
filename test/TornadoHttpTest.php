<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\ServiceManager\ServiceManager;

class TornadoHttpTest extends PHPUnit_Framework_TestCase
{
    public function testTornadoHttpInstance()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testEmptyConstruct()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->add($middleware);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructMiddleware()
    {
        $middleware1 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };
        $middleware2 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            ['middleware' => $middleware1],
            ['middleware' => $middleware2]
        ]);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructMiddlewareExtend()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware,
                'path'       => '/',
                '$methods'   => ['GET', 'POST']
            ]
        ]);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructContainer()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp(
            [
                ['middleware' => $middleware]
            ],
            new ServiceManager()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testMiddlewarePath()
    {
        $middleware = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware,
                'path'       => '/example/'
            ]
        ]);

        /* @var $response ResponseInterface */
        $request = ServerRequestFactory::fromGlobals();
        $uri     = $request->getUri()->withPath('/example');
        $request = $request->withUri($uri);

        $response = $tornadoHttp($request, new Response());

        $this->assertEquals(201, $response->getStatusCode());;
    }

    public function testMiddlewareMethod()
    {
        $middleware = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware,
                'methods'    => ['GET', 'POST']
            ]
        ]);

        /* @var $response ResponseInterface */
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp($request, new Response());

        $this->assertEquals(201, $response->getStatusCode());;
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
            ['middleware' => $middleware1],
            ['middleware' => $middleware2]
        ]);

        $response = $tornadoHttp(ServerRequestFactory::fromGlobals(), new Response());

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }

    public function testResponseTextAndStatus()
    {
        $middleware = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            $response->getBody()->write('Hello TornadoHTTP');
            return $next($request, $response);
        };

        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp([
            ['middleware' => $middleware]
        ]);

        /* @var $response ResponseInterface */
        $response = $tornadoHttp(ServerRequestFactory::fromGlobals(), new Response());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Hello TornadoHTTP', $response->getBody());
    }

    public function testGetMiddlewareIndex()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->add('Classes\TestMiddleware');
        $tornadoHttp->add('Classes\TestParamMiddleware');
        
        $middlewares = $tornadoHttp->getMiddlewareIndex();

        $this->assertEquals(0, $middlewares);
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

    public function testResolveCallableTrait()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $callable = $tornadoHttp->resolveCallable('Classes\TestTraitMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $callable->getContainer());
    }

    public function testResolveCallableChildTrait()
    {
        $tornadoHttp = new DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $callable = $tornadoHttp->resolveCallable('Classes\TestChildTraitMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $callable->getContainer());
    }
}
