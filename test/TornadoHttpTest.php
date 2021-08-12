<?php

declare(strict_types=1);

namespace Test;

use DMS\TornadoHttp\Container\InjectContainerInterface;
use DMS\TornadoHttp\Exception\MiddlewareException;
use DMS\TornadoHttp\TornadoHttp;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\Resolver\CustomResolver;
use Test\Classes\TestMiddleware;
use Test\Classes\TestParamMiddleware;
use Psr\Container\ContainerInterface;
use Test\Classes\TestTraitMiddleware;
use Test\Classes\TestChildTraitMiddleware;
use Test\Classes\TestExtendsMiddlewareMiddleware;
use Test\Classes\TestChildExtendsMiddlewareMiddleware;
use Test\Classes\TestNotMiddlewareInterface;
use Test\Classes\TestExtendsMiddlewareNotOverrideProcessMiddleware;
use Test\Classes\TestStatus200Middleware;

/**
 * Tests.
 *
 * @internal
 * @coversNothing
 */
final class TornadoHttpTest extends TestCase
{
    public function testRequestHandlerInterfaceInstance(): void
    {
        $tornadoHttp = new TornadoHttp();
        $this->assertInstanceOf(RequestHandlerInterface::class, $tornadoHttp);
    }

    public function testTornadoHttpInstance(): void
    {
        $tornadoHttp = new TornadoHttp();
        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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

        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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

        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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

        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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

        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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

        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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

        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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

        $this->assertInstanceOf(TornadoHttp::class, $tornadoHttp);
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
        $this->expectException(MiddlewareException::class);

        $tornadoHttp = new TornadoHttp();

        $request = ServerRequestFactory::fromGlobals();

        $tornadoHttp->handle($request);
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

        $this->assertInstanceOf(ResponseInterface::class, $response);
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
                /** @var TornadoHttp $handler */
                $response = $handler->getResponse();
                $response?->getBody()->write('A');

                return $handler->handle($request);
            }
        });

        $middleware1 = (new class() implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                /** @var TornadoHttp $handler */
                $response = $handler->getResponse();
                $response?->getBody()->write('B');

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
        $tornadoHttp->add(TestMiddleware::class);
        $tornadoHttp->add(TestParamMiddleware::class);

        $middlewares = $tornadoHttp->getMiddlewareIndex();

        $this->assertSame(0, $middlewares);
    }

    public function testSetGetResponse(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setResponse(new TextResponse(''));

        $response = $tornadoHttp->getResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testSetGetDI(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        $container = $tornadoHttp->getDI();

        $this->assertInstanceOf(ContainerInterface::class, $container);
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

        $middleware = $tornadoHttp->resolveMiddleware(TestMiddleware::class);

        $this->assertInstanceOf(TestMiddleware::class, $middleware);
    }

    public function testResolveString(): void
    {
        $tornadoHttp = new TornadoHttp();

        $middleware = $tornadoHttp->resolveMiddleware(TestMiddleware::class);

        $this->assertInstanceOf(TestMiddleware::class, $middleware);
    }

    public function testResolveStringService(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(
            new ServiceManager(
                [
                    'invokables' => [
                        'TestMiddleware' => TestMiddleware::class,
                    ],
                ]
            )
        );

        $middleware = $tornadoHttp->resolveMiddleware('TestMiddleware');

        $this->assertInstanceOf(TestMiddleware::class, $middleware);
    }

    public function testResolveArray(): void
    {
        $tornadoHttp = new TornadoHttp();

        $middleware = $tornadoHttp->resolveMiddleware([TestParamMiddleware::class, [1]]);

        $this->assertInstanceOf(TestParamMiddleware::class, $middleware);
    }

    public function testResolveContainerTrait(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        /** @var InjectContainerInterface $middleware */
        $middleware = $tornadoHttp->resolveMiddleware(TestTraitMiddleware::class);

        $this->assertInstanceOf(ContainerInterface::class, $middleware->getContainer());
    }

    public function testResolveChildContainerTrait(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        /** @var InjectContainerInterface $middleware */
        $middleware = $tornadoHttp->resolveMiddleware(TestChildTraitMiddleware::class);

        $this->assertInstanceOf(ContainerInterface::class, $middleware->getContainer());
    }

    public function testResolveInjectContainerInterface(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        /** @var InjectContainerInterface $middleware */
        $middleware = $tornadoHttp->resolveMiddleware(TestExtendsMiddlewareMiddleware::class);

        $this->assertInstanceOf(ContainerInterface::class, $middleware->getContainer());
    }

    public function testResolveChildInjectContainerInterface(): void
    {
        $tornadoHttp = new TornadoHttp();
        $tornadoHttp->setDI(new ServiceManager());

        /** @var InjectContainerInterface $middleware */
        $middleware = $tornadoHttp->resolveMiddleware(TestChildExtendsMiddlewareMiddleware::class);

        $this->assertInstanceOf(ContainerInterface::class, $middleware->getContainer());
    }

    public function testMiddlewareException(): void
    {
        $this->expectException(MiddlewareException::class);

        $tornadoHttp = new TornadoHttp();

        $tornadoHttp->resolveMiddleware(TestNotMiddlewareInterface::class);
    }

    public function testAllTestMiddlewares(): void
    {
        $tornadoHttp = new TornadoHttp(
            [
                ['middleware' => TestChildExtendsMiddlewareMiddleware::class],
                ['middleware' => TestChildTraitMiddleware::class],
                ['middleware' => TestExtendsMiddlewareMiddleware::class],
                ['middleware' => TestExtendsMiddlewareNotOverrideProcessMiddleware::class],
                ['middleware' => TestMiddleware::class],
                ['middleware' => [TestParamMiddleware::class, [1]]],
                ['middleware' => TestTraitMiddleware::class],
                ['middleware' => TestStatus200Middleware::class],
            ],
            new TextResponse('')
        );

        $response = $tornadoHttp->handle(ServerRequestFactory::fromGlobals());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
