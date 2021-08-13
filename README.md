TORNADO HTTP
============

[![Build Status](https://travis-ci.com/danielspk/TornadoHttp.svg)](https://travis-ci.com/danielspk/TornadoHttp)
[![Coverage Status](https://coveralls.io/repos/github/danielspk/TornadoHttp/badge.svg?branch=master)](https://coveralls.io/github/danielspk/TornadoHttp?branch=master)
[![Latest Stable Version](https://poser.pugx.org/danielspk/TornadoHttp/v/stable.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![Total Downloads](https://poser.pugx.org/danielspk/TornadoHttp/downloads.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![License](https://poser.pugx.org/danielspk/TornadoHttp/license.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![SensioLabsInsight](https://insight.symfony.com/projects/3d14197b-406f-4a2d-acae-8372104870a0/mini.svg)](https://insight.symfony.com/projects/3d14197b-406f-4a2d-acae-8372104870a0)

![ScreenShot](http://daniel-spiridione.com.ar/images/proyectos/tornado-php.png)

TORNADO HTTP es un handler de middlewares [PSR-15](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md)

## Documentación:

La siguiente documentación le enseñará el uso de Tornado Http.

### Instalación:

Se recomienda instalar esta librería mediante el uso de Composer de la siguiente forma:

```
    php composer.phar require danielspk/tornadohttp:~4.0
```

Esto instalará Tornado HTTP y creará o actualizará el archivo `composer.json` con la siguiente dependencia:

```
{
    "require": {
        "danielspk/tornadohttp": "~4.0"
    }
}
```

### Crear el contenedor de aplicación:

Tornado Http puede construirse de varias formas:

#### Sin parámetros:

```php
    $app = new \DMS\TornadoHttp\TornadoHttp();
```

#### Con una cola de Middlewares

```php
    $app = new \DMS\TornadoHttp\TornadoHttp(
        [
            ['middleware' => (new MiddlewareClass)],
            ['middleware' => $middlewareOne],
            ['middleware' => 'ServiceMiddlewareTwo'],
            ['middleware' => 'App\MiddlewareThree', 'path' => '/admin'],
            ['middleware' => ['App\MiddlewareFour', [$paramOne, $paramTwo]]],
        ]
    );
```

Nota: Puede ver que hay cuatro formas de registrar un Middleware: a) instancia de MiddlewareInterface, b) string con referencia a un servicio del container, 
c) string con referencia de namespace de clase y d) string con referencia de clase y array con parámetros de constructor. 
Más adelante se explicará en detalle cada una de estas formas.

#### Con una Respuesta por defecto

```php
    $app = new DMS\TornadoHttp\TornadoHttp(
        [],
        new DefaultResponse()
    );
```

#### Con un Contenedor de Servicios

```php
    $app = new DMS\TornadoHttp\TornadoHttp(
        [],
        new DefaultResponse(),
        new Container()
    );
```

Nota: El contenedor de servicios a utilizar debe respetar el estándar [PSR-11](https://www.php-fig.org/psr/psr-11/).

#### Con un Resolver de Middleware personalizado

```php
    $resolver = class () implements ResolverInterface {
        public function solve($middleware) : MiddlewareInterface {
            // ...
        };
    };

    $app = new DMS\TornadoHttp\TornadoHttp(
        [],
        new DefaultResponse(),
        new Container(),
        new $resolver()
    );
```

#### Con un Entorno de Ejecución

```php
    $app = new DMS\TornadoHttp\TornadoHttp(
        [],
        new DefaultResponse(),
        new Container(),
        null,
        'development'
    );
```

### Cola de Middlewares:

Tornado Http permite registrar middlewares de 3 formas distintas:
* mediante su `constructor`
* mediante el método `add()`
* mediante el método `addList()`

**Ejemplos:**

```php
    // mediante el constructor
    $app = new DMS\TornadoHttp\TornadoHttp(
        [
            ['middleware' => (new MiddlewareClass)],
        ]
    );
```

```php
    // mediante el método add()
    $app->add('ServiceMiddleware', '/', ['GET', 'POST'], ['dev', 'prod'], 0);
```

```php
    // mediante el método addList()
    $app->addList([
        ['middleware' => (new MiddlewareClass)],
        ['middleware' => 'ServiceMiddleware'],
    ]);
```

Tornado Http dispone de una clase propia que resuelve automáticamente como ejecutar un middleware registrado.

Existen cuatro formas por defecto de registrar middlewares en Tornado Http utilizando su Resolver:
* mediante una `instancia de clase`
* mediante un `string` que hace referencia a un `servicio` contenido en el Contenedor de Servicios
* mediante un `string` que hace referencia un namespace de una `clase`
* mediante un `array`

Todos los middlewares deben implementar `\Psr\Http\Server\MiddlewareInterface`.

**Ejemplos:**

```php
    // mediante una instancia de clase
    $app->add(new MiddlewareClass);

    // mediante un string de referencia a un servicio del contenedor
    $app->add('ServiceMiddleware');

    // mediante un string de referencia a un namespace de clase
    $app->add(App\MiddlewareClass::class);

    // mediante un string de referencia a un namespace de clase y parámetros de constructor
    $app->add([App\MiddlewareClass::class, [$param1, $param2]]);
```

Cada middleware puede ser registrado con los siguientes filtros de ejecución opcionales:
* Métodos HTTP permitidos
* Path URL
* Entornos de ejecución permitidos

**Ejemplos:**

```php
    // mediante el constructor
    $app = new DMS\TornadoHttp\TornadoHttp(
        [
            [
                'middleware' => (new MiddlewareTimeExecutionClass),
                'path'       => '/admin',
                'env'        => ['develop'],
            ],
            [
                'middleware' => (new MiddlewareLogClass),
                'methods'    => ['POST', 'PUT'],
                'env'        => ['production', 'develop'],
            ],
        ]
    );
```

### Container Trait:

Tornado Http facilita un trait que puede ser utilizado dentro de los middlewares.

Cuando Tornado Http detecta que un middleware utiliza `Container\ContainerTrait` inyecta automáticamente el contenedor
de servicios registrado en Tornado Http.

Se podrá acceder al contenedor de servicios, dentro del middleware, de la siguiente forma:

```php
    class ExampleMiddleware implements \Psr\Http\Server\MiddlewareInterface
    {
        use \DMS\TornadoHttp\Container\ContainerTrait;

        public function getViewEngine()
        {
            return $this->getContainer()->get('view_engine');
        }

        // ...
    }
```

### Custom Resolver:

Tornado Http permite registrar un resolver de middlewares personalizado. De esta forma puede reemplazar o extender el uso de las cuatro formas mencionadas 
para registrar middlewares.

**Ejemplo:**

```php
    class CustomResolver implements ResolverInterface {
        public function solve(string $middlewareClass) : MiddlewareInterface {
            return new $middlewareClass();
        }
    }

    $app = new DMS\TornadoHttp\TornadoHttp();
    $app->setResolver(new CustomResolver());
```

### Resumen de Interfaces/Traits/Clases y Métodos:

**DMS\TornadoHttp\TornadoHttp**

| Método | Detalle |
| ------ | ------- |
| __construct(array = [], ResponseInterface = null, ContainerInterface = null, ResolverInterface = null, string = 'dev') | Crea una instancia de Tornado Http |
| handle(ServerRequestInterface) : ResponseInterface | Ejecución de handlers |
| add(mixed, string = null, array = null, array = null, int = null) | Agrega un Middleware a la cola |
| addList(array) | Agrega una lista de Middlewares a la cola |
| getMiddlewareIndex() : int | Devuelve el índice actual de la cola de Middlewares |
| setDI(ContainerInterface) : TornadoHttp | Asigna un contenedor de servicios |
| getDI() : ContainerInterface | Recupera el contenedor de servicios asignado |
| setResponse(ResponseInterface) : TornadoHttp | Asigna una respuesta por defecto |
| getResponse() : ResponseInterface | Recupera la respuesta del último middleware ejecutado |
| setContext(mixed) : TornadoHttp | Asigna un contexto compartido |
| getContext() : mixed | Recupera un contexto compartido |
| setResolver(ResolverInterface) : TornadoHttp | Asigna un resolver de middlewares |
| setEnvironment(string) : TornadoHttp | Asigna el entorno de ejecución |
| resolveMiddleware(mixed) : MiddlewareInterface | Resuelve y ejecuta un Middleware |

**DMS\TornadoHttp\Container\ContainerTrait**

| Método | Detalle |
| ------ | ------- |
| setContainer(ContainerInterface) : self | Asigna un contenedor de servicios |
| getContainer() : ContainerInterface | Recupera el contenedor de servicios asignado |

**DMS\TornadoHttp\Container\InjectContainerInterface**

| Método | Detalle |
| ------ | ------- |
| setContainer(ContainerInterface) | Asigna un contenedor de servicios |
| getContainer() : ContainerInterface | Recupera el contenedor de servicios asignado |

**DMS\TornadoHttp\Middleware\Middleware**

| Método | Detalle |
| ------ | ------- |
| setContainer(ContainerInterface) : Middleware | Asigna un contenedor de servicios |
| getContainer() : ContainerInterface | Recupera el contenedor de servicios asignado |

**DMS\TornadoHttp\Resolver\Resolver**

| Método | Detalle |
| ------ | ------- |
| __construct(ContainerInterface = null) | Crea una instancia del resolver |
| solve(MiddlewareInterface&#124;string&#124;array) : MiddlewareInterface | Resuelve un middleware |

**DMS\TornadoHttp\Resolver\ResolverInterface**

| Método | Detalle |
| ------ | ------- |
| solve(MiddlewareInterface&#124;string&#124;array) : MiddlewareInterface | Resuelve un middleware |

## Inspiración:

- [Relay](http://relayphp.com/)
- [Zend Stratigility](https://github.com/zendframework/zend-stratigility)

## Licencia:

El proyecto se distribuye bajo la licencia MIT.

## Sugerencias y colaboración:

Daniel Spiridione - <http://daniel-spiridione.com.ar>
