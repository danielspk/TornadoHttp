<?php
namespace Test;

use Test\Resolver\CustomResolver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

class TornadoHttpTest extends \PHPUnit\Framework\TestCase
{
    public function testTornadoHttpInstance()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testEmptyConstruct()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
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

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
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

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware,
                'path'       => '/',
                'methods'    => ['GET', 'POST'],
                'env'        => ['local', 'dev']
            ]
        ]);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructContainer()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp(
            [
                ['middleware' => $middleware]
            ],
            new ServiceManager()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructorResolver()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp(
            [
                ['middleware' => $middleware]
            ],
            new ServiceManager(),
            new CustomResolver()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructorEnvironment()
    {
        $middleware = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp(
            [
                ['middleware' => $middleware]
            ],
            new ServiceManager(),
            new CustomResolver(),
            'development'
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testMiddlewarePath()
    {
        $middleware = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
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

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testEmptyMiddlewarePath()
    {
        $middleware1 = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $middleware2 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            $response = $response->withStatus(500);
            return $next($request, $response);
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware1,
                'path'       => '/example/'
            ],
            [
                'middleware' => $middleware2,
                'path'       => '/error/'
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

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
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

    public function testEmptyMiddlewareMethod()
    {
        $middleware1 = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $middleware2 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            $response = $response->withStatus(500);
            return $next($request, $response);
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware1,
                'methods'    => ['GET', 'POST']
            ],
            [
                'middleware' => $middleware2,
                'methods'    => ['GET']
            ]
        ]);

        /* @var $response ResponseInterface */
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp($request, new Response());

        $this->assertEquals(201, $response->getStatusCode());;
    }

    public function testMiddlewareEnvironment()
    {
        $middleware = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware,
                'env'        => ['local']
            ]
        ]);
        $tornadoHttp->setEnvironment('local');

        /* @var $response ResponseInterface */
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp($request, new Response());

        $this->assertEquals(201, $response->getStatusCode());;
    }

    public function testEmptyMiddlewareEnvironment()
    {
        $middleware1 = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $middleware2 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            $response = $response->withStatus(500);
            return $next($request, $response);
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware1,
                'env'        => ['dev', 'local']
            ],
            [
                'middleware' => $middleware2,
                'env'        => ['prod']
            ]
        ]);
        $tornadoHttp->setEnvironment('local');

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

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
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

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            ['middleware' => $middleware]
        ]);

        /* @var $response ResponseInterface */
        $response = $tornadoHttp(ServerRequestFactory::fromGlobals(), new Response());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Hello TornadoHTTP', $response->getBody());
    }

    public function testAddMiddlewareExistIndex()
    {
        $middleware0 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            $response->getBody()->write('A');

            return $next($request, $response);
        };

        $middleware1 = function(RequestInterface $request, ResponseInterface $response, callable $next) {
            $response->getBody()->write('B');

            return $next($request, $response);
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->add($middleware1);
        $tornadoHttp->add($middleware0, null, null, null, 0);

        /* @var $response ResponseInterface */
        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp($request, new Response());

        $this->assertEquals('AB', (string) $response->getBody());
    }

    public function testGetMiddlewareIndex()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->add('\Test\Classes\TestMiddleware');
        $tornadoHttp->add('\Test\Classes\TestParamMiddleware');

        $middlewares = $tornadoHttp->getMiddlewareIndex();

        $this->assertEquals(0, $middlewares);
    }

    public function testSetGetDI()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $container = $tornadoHttp->getDI();

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $container);
    }

    public function testSetEnvironment()
    {
        $middleware = function(RequestInterface $request,  ResponseInterface $response, callable $next) {
            $response = $response->withStatus(201);
            return $next($request, $response);
        };

        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            [
                'middleware' => $middleware,
                'env'        => ['production']
            ]
        ]);
        $tornadoHttp->setEnvironment('production');

        /* @var $response ResponseInterface */
        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp($request, new Response());

        $this->assertEquals(201, $response->getStatusCode());;
    }

    public function testSetResolver()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setResolver(new CustomResolver());

        $callable = $tornadoHttp->resolveMiddleware('Test\Classes\TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $callable);
    }

    public function testResolveCallableString()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();

        $callable = $tornadoHttp->resolveMiddleware('Test\Classes\TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $callable);
    }

    public function testResolveCallableStringService()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager(
            new Config([
                'invokables' => [
                    'TestMiddleware' => '\Test\Classes\TestMiddleware'
                ]
            ])
        ));

        $callable = $tornadoHttp->resolveMiddleware('TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $callable);
    }

    public function testResolveCallableArray()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();

        $callable = $tornadoHttp->resolveMiddleware(['Test\Classes\TestParamMiddleware', [1, 2]]);

        $this->assertInstanceOf('\Test\Classes\TestParamMiddleware', $callable);
    }

    public function testResolveCallableCallable()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();

        $callable = $tornadoHttp->resolveMiddleware(function() {});

        $this->assertInternalType('callable', $callable);
    }

    public function testResolveContainerTrait()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $callable = $tornadoHttp->resolveMiddleware('Test\Classes\TestTraitMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $callable->getContainer());
    }

    public function testResolveChildContainerTrait()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $callable = $tornadoHttp->resolveMiddleware('Test\Classes\TestChildTraitMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $callable->getContainer());
    }

    public function testResolveInjectContainerInterface()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $callable = $tornadoHttp->resolveMiddleware('Test\Classes\TestExtendsMiddlewareMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $callable->getContainer());
    }

    public function testResolveChildInjectContainerInterface()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $callable = $tornadoHttp->resolveMiddleware('Test\Classes\TestChildExtendsMiddlewareMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $callable->getContainer());
    }

    /**
     * @expectedException \DMS\TornadoHttp\Exception\MiddlewareException
     */
    public function testMiddlewareException()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp();

        $tornadoHttp->resolveMiddleware('\Test\Classes\TestNotCallableMiddleware');
    }

    public function testAllTestMiddlewares()
    {
        $tornadoHttp = new \DMS\TornadoHttp\TornadoHttp([
            ['middleware' => 'Test\Classes\TestChildExtendsMiddlewareMiddleware'],
            ['middleware' => 'Test\Classes\TestChildTraitMiddleware'],
            ['middleware' => 'Test\Classes\TestExtendsMiddlewareMiddleware'],
            ['middleware' => 'Test\Classes\TestMiddleware'],
            ['middleware' => ['Test\Classes\TestParamMiddleware', [1, 2]]],
            ['middleware' => 'Test\Classes\TestTraitMiddleware']
        ]);

        $response = $tornadoHttp(ServerRequestFactory::fromGlobals(), new Response());

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }
}
