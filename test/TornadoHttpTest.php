<?php

namespace Test;

use DMS\TornadoHttp\TornadoHttp;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\Resolver\CustomResolver;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\ServiceManager\ServiceManager;

class TornadoHttpTest extends \PHPUnit\Framework\TestCase
{
    public function testRequestHandlerInterfaceInstance()
    {
        $tornadoHttp = new TornadoHttp();
        $this->assertInstanceOf('\Psr\Http\Server\RequestHandlerInterface', $tornadoHttp);
    }

    public function testTornadoHttpInstance()
    {
        $tornadoHttp = new TornadoHttp();
        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testEmptyConstruct()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->add($middleware);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructMiddleware()
    {
        $middleware1 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new Response();
            }
        });

        $middleware2 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp([
            ['middleware' => $middleware1],
            ['middleware' => $middleware2],
        ]);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructMiddlewareExtend()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'path'       => '/',
                'methods'    => ['GET', 'POST'],
                'env'        => ['local', 'dev'],
            ],
        ]);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructContainer()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => $middleware],
            ],
            new ServiceManager()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructorResolver()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => $middleware],
            ],
            new ServiceManager(),
            new CustomResolver()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructorEnvironment()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => $middleware],
            ],
            new ServiceManager(),
            new CustomResolver(),
            'development'
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testDefaultResponse()
    {
        $tornadoHttp = new TornadoHttp();

        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp->handle($request);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testMiddlewarePath()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = new Response();
                $response = $response->withStatus(201);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'path'       => '/example/',
            ],
        ]);

        $request = ServerRequestFactory::fromGlobals();
        $uri     = $request->getUri()->withPath('/example');
        $request = $request->withUri($uri);

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testMiddlewareIgnorePath()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = new Response();
                $response = $response->withStatus(204);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'path'       => '/deleteExample/',
            ],
        ]);

        $request = ServerRequestFactory::fromGlobals();
        $uri     = $request->getUri()->withPath('/otherExample');
        $request = $request->withUri($uri);

        $response = $tornadoHttp->handle($request);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testMiddlewareOneIgnorePath()
    {
        $middleware1 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(201);

                return $response;
            }
        });

        $middleware2 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(500);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware1,
                'path'       => '/example/',
            ],
            [
                'middleware' => $middleware2,
                'path'       => '/error/',
            ],
        ]);

        $request = ServerRequestFactory::fromGlobals();
        $uri     = $request->getUri()->withPath('/example');
        $request = $request->withUri($uri);

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testMiddlewareMethod()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(201);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'methods'    => ['GET', 'POST'],
            ],
        ]);

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testEmptyMiddlewareMethod()
    {
        $middleware1 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(204);

                return $response;
            }
        });

        $middleware2 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(201);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware1,
                'methods'    => ['DELETE'],
            ],
            [
                'middleware' => $middleware2,
                'methods'    => ['POST'],
            ],
        ]);

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testMiddlewareEnvironment()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(201);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'env'        => ['local'],
            ],
        ]);
        $tornadoHttp->setEnvironment('local');

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testEmptyMiddlewareEnvironment()
    {
        $middleware1 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(201);

                return $response;
            }
        });

        $middleware2 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(500);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware1,
                'env'        => ['dev', 'local'],
            ],
            [
                'middleware' => $middleware2,
                'env'        => ['prod'],
            ],
        ]);
        $tornadoHttp->setEnvironment('local');

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testResponseMiddleware()
    {
        $middleware1 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                return $handler->handle($request);
            }
        });

        $middleware2 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $tornadoHttp = new TornadoHttp([
            ['middleware' => $middleware1],
            ['middleware' => $middleware2],
        ]);

        $response = $tornadoHttp->handle(ServerRequestFactory::fromGlobals());

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }

    public function testResponseTextAndStatus()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(201);
                $response->getBody()->write('Hello TornadoHTTP');

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            ['middleware' => $middleware],
        ]);

        $response = $tornadoHttp->handle(ServerRequestFactory::fromGlobals());

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Hello TornadoHTTP', (string) $response->getBody());
    }

    public function testAddMiddlewareExistIndex()
    {
        $middleware0 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->getResponse();
                $response->getBody()->write('A');

                return $handler->handle($request);
            }
        });

        $middleware1 = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->getResponse();
                $response->getBody()->write('B');

                return $handler->handle($request);
            }
        });

        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->add($middleware1);
        $tornadoHttp->add($middleware0, null, null, null, 0);

        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp->handle($request);

        $this->assertSame('AB', (string) $response->getBody());
    }

    public function testGetMiddlewareIndex()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->add('\Test\Classes\TestMiddleware');
        $tornadoHttp->add('\Test\Classes\TestParamMiddleware');

        $middlewares = $tornadoHttp->getMiddlewareIndex();

        $this->assertSame(0, $middlewares);
    }

    public function testSetGetDI()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $container = $tornadoHttp->getDI();

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $container);
    }

    public function testSetEnvironment()
    {
        $middleware = (new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
            {
                $response = $handler->handle($request);
                $response = $response->withStatus(201);

                return $response;
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'env'        => ['production'],
            ],
        ]);
        $tornadoHttp->setEnvironment('production');

        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testSetResolver()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setResolver(new CustomResolver());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $middleware);
    }

    public function testResolveString()
    {
        $tornadoHttp = new TornadoHttp();

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $middleware);
    }

    public function testResolveStringService()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(
            new ServiceManager(
                [
                    'invokables' => [
                        'TestMiddleware' => '\Test\Classes\TestMiddleware',
                    ],
                ]
            )
        );

        $middleware = $tornadoHttp->resolveMiddleware('TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $middleware);
    }

    public function testResolveArray()
    {
        $tornadoHttp = new TornadoHttp();

        $middleware = $tornadoHttp->resolveMiddleware(['Test\Classes\TestParamMiddleware', [1, 2]]);

        $this->assertInstanceOf('\Test\Classes\TestParamMiddleware', $middleware);
    }

    public function testResolveContainerTrait()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestTraitMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $middleware->getContainer());
    }

    public function testResolveChildContainerTrait()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestChildTraitMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $middleware->getContainer());
    }

    public function testResolveInjectContainerInterface()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestExtendsMiddlewareMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $middleware->getContainer());
    }

    public function testResolveChildInjectContainerInterface()
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestChildExtendsMiddlewareMiddleware');

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $middleware->getContainer());
    }

    /**
     * @expectedException \DMS\TornadoHttp\Exception\MiddlewareException
     */
    public function testMiddlewareException()
    {
        $tornadoHttp = new TornadoHttp();

        $tornadoHttp->resolveMiddleware('\Test\Classes\TestNotMiddlewareInterface');
    }

    public function testAllTestMiddlewares()
    {
        $tornadoHttp = new TornadoHttp([
            ['middleware' => 'Test\Classes\TestChildExtendsMiddlewareMiddleware'],
            ['middleware' => 'Test\Classes\TestChildTraitMiddleware'],
            ['middleware' => 'Test\Classes\TestExtendsMiddlewareMiddleware'],
            ['middleware' => 'Test\Classes\TestMiddleware'],
            ['middleware' => ['Test\Classes\TestParamMiddleware', [1, 2]]],
            ['middleware' => 'Test\Classes\TestTraitMiddleware'],
            ['middleware' => 'Test\Classes\TestStatus200Middleware'],
        ]);

        $response = $tornadoHttp->handle(ServerRequestFactory::fromGlobals());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }
}
