TORNADO HTTP
============

[![Build Status](https://travis-ci.org/danielspk/TornadoHttp.svg)](https://travis-ci.org/danielspk/TornadoHttp)
[![Coverage Status](https://coveralls.io/repos/github/danielspk/TornadoHttp/badge.svg?branch=master)](https://coveralls.io/github/danielspk/TornadoHttp?branch=master)
[![Latest Stable Version](https://poser.pugx.org/danielspk/TornadoHttp/v/stable.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![Total Downloads](https://poser.pugx.org/danielspk/TornadoHttp/downloads.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![License](https://poser.pugx.org/danielspk/TornadoHttp/license.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d14197b-406f-4a2d-acae-8372104870a0/mini.png)](https://insight.sensiolabs.com/projects/3d14197b-406f-4a2d-acae-8372104870a0)

![ScreenShot](http://daniel-spiridione.com.ar/images/proyectos/tornado-php.png)

TORNADO HTTP es un contenedor middleware PSR-7

## Documentación:

La siguiente documentación le enseñará el uso de Tornado Http. Si lo desea puede descargar una aplicación esqueleto de
ejemplo que le mostrará como puede crear sus propios middlewares y utilizar paquetes de terceros como Doctrine y Twig:

https://github.com/danielspk/TornadoHttpSkeletonApplication

### Instalación:

Se recomienda instalar esta librería mediante el uso de Composer de la siguiente forma:

```
    php composer.phar require danielspk/tornadohttp:~1.0
```

Esto instalará Tornado HTTP y creará o actualizará el archivo `composer.json` con la siguiente dependencia:

```
{
    "require": {
        "danielspk/tornadohttp": "~1.0"
    }
}
```

### Crear el contenedor de aplicación:

Tornado Http puede construirse de varias formas:

#### Sin parámetros:

```php
    $app = new \DMS\TornadoHttp\TornadoHttp();
```

#### Con una lista de Middlewares

```php
    $app = new \DMS\TornadoHttp\TornadoHttp([
        ['middleware' => (new MiddlewareClass)],
        ['middleware' => $middlewareOne],
        ['middleware' => 'ServiceMiddlewareTwo'],
        ['middleware' => 'App\MiddlewareThree', 'path' => '/admin'],
        ['middleware' => ['App\MiddlewareFour', [$paramOne, $paramTwo]]]
    ]);
```

Nota: Puede ver que hay cinco formas de registrar un Middleware: object, callable, string referencia a servicio, string
con namespace de clase y array. Más adelante se explicará en detalle cada una de estas formas.

#### Con un Contenedor de Servicios

```php
    $app = new DMS\TornadoHttp\TornadoHttp(
        [],
        new Container()
    );
```

Nota: El contenedor de servicios a utilizar debe implementar la interface `Container Interop`. Puede obtener más
información al respecto en el siguiente [link](https://github.com/container-interop/container-interop).

#### Con un Resolver de Middleware

Pendiente de documentar...

#### Con un Entorno de Ejecución

Pendiente de documentar...

### Cola de Middlewares:

Tornado Http permite registrar middlewares de 3 formas distintas:
* mediante su `constructor`
* mediante el método `add()`
* mediante el método `addList()`

**Ejemplos:**

Pendiente de documentar...

Tornado Http dispone de una clase propia que resuelve automáticamente como ejecutar un middleware registrado.

Existen cinco formas de registrar middlewares en Tornado Http:
* mediante una `instancia de clase`
* mediante un `callable`
* mediante un `string` que hace referencia a un `servicio` contenido en el Contenedor de Servicios
* mediante un `string` que hace referencia a una `clase`
* mediante un `array`

**Ejemplos:**

Pendiente de documentar...

Cada middleware puede ser registrado con los siguientes filtros de ejecución:
* Métodos HTTP permitidos
* Path URL
* Entornos de ejecución permitidos

**Ejemplos:**

Pendiente de documentar...

### Container Trait:

Tornado Http facilita un trait que puede ser utilizado dentro de sus propios middlewares.

Cuando Tornado Http detecta que un middleware utiliza `Container\ContainerTrait` inyecta automáticamente el contenedor
de servicios registrado en Tornado Http.

Se podrá acceder al contenedor de servicios, dentro del middleware, de la siguiente forma:

```php
    class ExampleMiddleware
    {
        use \DMS\TornadoHttp\Container\ContainerTrait;

        public function getViewEngine()
        {
            return $this->container->get('view_engine');
        }
    }
```

### Inject Container Interface:

Pendiente de documentar...

### Middleware Abstracto:

Pendiente de documentar...

### Middleware Resolver:

Pendiente de documentar...

### Resumen de Interfaces/Traits/Clases y Métodos:

**DMS\TornadoHttp\TornadoHttp**

| Método | Detalle |
| ------ | ------- |
| __construct(array = [], ContainerInterface = null, ResolverInterface = null, string = 'dev') | Crea una instancia de Tornado Http |
| __invoke(RequestInterface, ResponseInterface) | Invocación |
| add(callable&#124;object&#124;string&#124;array, string = null, array = null, array = null, int = null) | Agrega un Middleware a la cola |
| addList(array) | Agrega una lista de Middlewares a la cola |
| getMiddlewareIndex() | Devuelve el índice actual de la cola de Middlewares |
| setDI(ContainerInterface) | Asigna un contenedor de servicios |
| getDI() | Recupera el contenedor de servicios asignado |
| setResolver(ResolverInterface) | Asigna un resolver de middlewares |
| setEnvironment(string) | Asigna el entorno de ejecución |
| resolveMiddleware(callable&#124;string&#124;array) | Resuelve y ejecuta un Middleware |

**DMS\TornadoHttp\Container\ContainerTrait**

| Método | Detalle |
| ------ | ------- |
| setContainer(ContainerInterface) | Asigna un contenedor de servicios |
| getContainer() | Recupera el contenedor de servicios asignado |

**DMS\TornadoHttp\Container\InjectContainerInterface**

| Método | Detalle |
| ------ | ------- |
| setContainer(ContainerInterface) | Asigna un contenedor de servicios |
| getContainer() | Recupera el contenedor de servicios asignado |

**DMS\TornadoHttp\Middleware\Middleware**

| Método | Detalle |
| ------ | ------- |
| setContainer(ContainerInterface) | Asigna un contenedor de servicios |
| getContainer() | Recupera el contenedor de servicios asignado |

**DMS\TornadoHttp\Middleware\MiddlewareInterface**

| Método | Detalle |
| ------ | ------- |
| __invoke(RequestInterface, ResponseInterface) | Invocación |

**DMS\TornadoHttp\Resolver\Resolver**

| Método | Detalle |
| ------ | ------- |
| __construct(ContainerInterface = null) | Crea una instancia del resolver |
| solve(callable&#124;object&#124;string&#124;array) | Resuelve un middleware |

**DMS\TornadoHttp\Resolver\ResolverInterface**

| Método | Detalle |
| ------ | ------- |
| solve(callable&#124;object&#124;string&#124;array) | Resuelve un middleware |

## Inspiracion:

- [Relay](http://relayphp.com/)
- [Zend Stratigility](https://github.com/zendframework/zend-stratigility)

## Licencia:

El proyecto se distribuye bajo la licencia MIT.

## Sugerencias y colaboración:

Email: info@daniel.spiridione.com.ar
