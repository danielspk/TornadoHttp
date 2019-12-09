<?php

declare(strict_types=1);

namespace Test;

use DMS\TornadoHttp\TornadoHttp;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\Resolver\CustomResolver;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Tests.
 *
 * @internal
 */
final class TornadoHttpTest extends TestCase
{
    public function testRequestHandlerInterfaceInstance(): void
    {
        $tornadoHttp = new TornadoHttp();
        $this->assertInstanceOf('\Psr\Http\Server\RequestHandlerInterface', $tornadoHttp);
    }

    public function testTornadoHttpInstance(): void
    {
        $tornadoHttp = new TornadoHttp();
        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testEmptyConstruct(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->add($middleware);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructMiddleware(): void
    {
        $middleware1 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        });

        $middleware2 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
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

    public function testConstructMiddlewareExtend(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'path' => '/',
                'methods' => ['GET', 'POST'],
                'env' => ['local', 'dev'],
            ],
        ]);

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructResponse(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => $middleware],
            ],
            new EmptyResponse()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructContainer(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => $middleware],
            ],
            new EmptyResponse(),
            new ServiceManager()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructorResolver(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => $middleware],
            ],
            new EmptyResponse(),
            new ServiceManager(),
            new CustomResolver()
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testConstructorEnvironment(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return new Response();
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => $middleware],
            ],
            new EmptyResponse(),
            new ServiceManager(),
            new CustomResolver(),
            'development'
        );

        $this->assertInstanceOf('\DMS\TornadoHttp\TornadoHttp', $tornadoHttp);
    }

    public function testHandlerContext(): void
    {
        $tornadoHttp = new TornadoHttp();

        $setContext = [
            'exampleA' => 123,
            'exampleB' => 'value',
        ];

        $tornadoHttp->setContext($setContext);

        $getContext = $tornadoHttp->getContext();

        $this->assertSame(123, $getContext['exampleA']);
        $this->assertSame('value', $getContext['exampleB']);
    }

    public function testEmptyResponse(): void
    {
        $this->expectException(\DMS\TornadoHttp\Exception\MiddlewareException::class);

        $tornadoHttp = new TornadoHttp();

        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp->handle($request);
    }

    public function testDefaultResponse(): void
    {
        $response = new EmptyResponse();

        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setResponse($response);

        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testMiddlewarePath(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = new Response();

                return $response->withStatus(201);
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'path' => '/example/',
            ],
        ]);

        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()->withPath('/example');
        $request = $request->withUri($uri);

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testMiddlewareIgnorePath(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = new Response();

                return $response->withStatus(204);
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                [
                    'middleware' => $middleware,
                    'path' => '/deleteExample/',
                ],
            ],
            new TextResponse('')
        );

        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()->withPath('/otherExample');
        $request = $request->withUri($uri);

        $response = $tornadoHttp->handle($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testMiddlewareOneIgnorePath(): void
    {
        $middleware1 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $middleware2 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(500);
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                [
                    'middleware' => $middleware1,
                    'path' => '/example/',
                ],
                [
                    'middleware' => $middleware2,
                    'path' => '/error/',
                ],
            ],
            new TextResponse('')
        );

        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()->withPath('/example');
        $request = $request->withUri($uri);

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testMiddlewareMethod(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                [
                    'middleware' => $middleware,
                    'methods' => ['GET', 'POST'],
                ],
            ],
            new TextResponse('')
        );

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testEmptyMiddlewareMethod(): void
    {
        $middleware1 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(204);
            }
        });

        $middleware2 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                [
                    'middleware' => $middleware1,
                    'methods' => ['DELETE'],
                ],
                [
                    'middleware' => $middleware2,
                    'methods' => ['POST'],
                ],
            ],
            new TextResponse('')
        );

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testMiddlewareEnvironment(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware,
                'env' => ['local'],
            ],
        ]);
        $tornadoHttp->setResponse(new TextResponse(''));
        $tornadoHttp->setEnvironment('local');

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testEmptyMiddlewareEnvironment(): void
    {
        $middleware1 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $middleware2 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(500);
            }
        });

        $tornadoHttp = new TornadoHttp([
            [
                'middleware' => $middleware1,
                'env' => ['dev', 'local'],
            ],
            [
                'middleware' => $middleware2,
                'env' => ['prod'],
            ],
        ]);
        $tornadoHttp->setResponse(new TextResponse(''));
        $tornadoHttp->setEnvironment('local');

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withMethod('POST');

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testResponseMiddleware(): void
    {
        $middleware1 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        });

        $middleware2 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $tornadoHttp = new TornadoHttp([
            ['middleware' => $middleware1],
            ['middleware' => $middleware2],
        ]);
        $tornadoHttp->setResponse(new TextResponse(''));

        $response = $tornadoHttp->handle(ServerRequestFactory::fromGlobals());

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }

    public function testResponseTextAndStatus(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
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
        $tornadoHttp->setResponse(new TextResponse(''));

        $response = $tornadoHttp->handle(ServerRequestFactory::fromGlobals());

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Hello TornadoHTTP', (string) $response->getBody());
    }

    public function testAddMiddlewareExistIndex(): void
    {
        $middleware0 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->getResponse();
                $response->getBody()->write('A');

                return $handler->handle($request);
            }
        });

        $middleware1 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->getResponse();
                $response->getBody()->write('B');

                return $handler->handle($request);
            }
        });

        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setResponse(new TextResponse(''));
        $tornadoHttp->add($middleware1);
        $tornadoHttp->add($middleware0, null, null, null, 0);

        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp->handle($request);

        $this->assertSame('AB', (string) $response->getBody());
    }

    public function testGetMiddlewareIndex(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setResponse(new TextResponse(''));
        $tornadoHttp->add('\Test\Classes\TestMiddleware');
        $tornadoHttp->add('\Test\Classes\TestParamMiddleware');

        $middlewares = $tornadoHttp->getMiddlewareIndex();

        $this->assertSame(0, $middlewares);
    }

    public function testSetGetResponse(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setResponse(new TextResponse(''));

        $response = $tornadoHttp->getResponse();

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }

    public function testSetGetDI(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $container = $tornadoHttp->getDI();

        $this->assertInstanceOf('\Psr\Container\ContainerInterface', $container);
    }

    public function testSetEnvironment(): void
    {
        $middleware = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withStatus(201);
            }
        });

        $tornadoHttp = new TornadoHttp(
            [
                [
                    'middleware' => $middleware,
                    'env' => ['production'],
                ],
            ],
            new TextResponse('')
        );
        $tornadoHttp->setEnvironment('production');

        $request = ServerRequestFactory::fromGlobals();

        $response = $tornadoHttp->handle($request);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testSetResolver(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setResolver(new CustomResolver());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $middleware);
    }

    public function testResolveString(): void
    {
        $tornadoHttp = new TornadoHttp();

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestMiddleware');

        $this->assertInstanceOf('\Test\Classes\TestMiddleware', $middleware);
    }

    public function testResolveStringService(): void
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

    public function testResolveArray(): void
    {
        $tornadoHttp = new TornadoHttp();

        $middleware = $tornadoHttp->resolveMiddleware(['Test\Classes\TestParamMiddleware', [1, 2]]);

        $this->assertInstanceOf('\Test\Classes\TestParamMiddleware', $middleware);
    }

    public function testResolveContainerTrait(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestTraitMiddleware');

        $this->assertInstanceOf('\Psr\Container\ContainerInterface', $middleware->getContainer());
    }

    public function testResolveChildContainerTrait(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestChildTraitMiddleware');

        $this->assertInstanceOf('\Psr\Container\ContainerInterface', $middleware->getContainer());
    }

    public function testResolveInjectContainerInterface(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestExtendsMiddlewareMiddleware');

        $this->assertInstanceOf('\Psr\Container\ContainerInterface', $middleware->getContainer());
    }

    public function testResolveChildInjectContainerInterface(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $middleware = $tornadoHttp->resolveMiddleware('Test\Classes\TestChildExtendsMiddlewareMiddleware');

        $this->assertInstanceOf('\Psr\Container\ContainerInterface', $middleware->getContainer());
    }

    public function testMiddlewareException(): void
    {
        $this->expectException(\DMS\TornadoHttp\Exception\MiddlewareException::class);

        $tornadoHttp = new TornadoHttp();

        $tornadoHttp->resolveMiddleware('\Test\Classes\TestNotMiddlewareInterface');
    }

    public function testAllTestMiddlewares(): void
    {
        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => 'Test\Classes\TestChildExtendsMiddlewareMiddleware'],
                ['middleware' => 'Test\Classes\TestChildTraitMiddleware'],
                ['middleware' => 'Test\Classes\TestExtendsMiddlewareMiddleware'],
                ['middleware' => 'Test\Classes\TestExtendsMiddlewareNotOverrideProcessMiddleware'],
                ['middleware' => 'Test\Classes\TestMiddleware'],
                ['middleware' => ['Test\Classes\TestParamMiddleware', [1, 2]]],
                ['middleware' => 'Test\Classes\TestTraitMiddleware'],
                ['middleware' => 'Test\Classes\TestStatus200Middleware'],
            ],
            new TextResponse('')
        );

        $response = $tornadoHttp->handle(ServerRequestFactory::fromGlobals());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);
    }
}
