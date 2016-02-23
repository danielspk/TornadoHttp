TORNADO HTTP
============

[![Build Status](https://travis-ci.org/danielspk/TornadoHttp.svg)](https://travis-ci.org/danielspk/TornadoHttp)
[![Coverage Status](https://coveralls.io/repos/github/danielspk/TornadoHttp/badge.svg?branch=master)](https://coveralls.io/github/danielspk/TornadoHttp?branch=master)
[![Latest Stable Version](https://poser.pugx.org/danielspk/TornadoHttp/v/stable.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![Total Downloads](https://poser.pugx.org/danielspk/TornadoHttp/downloads.svg)](https://packagist.org/packages/danielspk/TornadoHttp)
[![License](https://poser.pugx.org/danielspk/TornadoHttp/license.svg)](https://packagist.org/packages/danielspk/TornadoHttp)

![ScreenShot](http://daniel-spiridione.com.ar/images/proyectos/tornado-php.png)

TORNADO HTTP es un contenedor middleware PSR-7

## Documentación:

La siguiente documentación le enseñará el uso de Tornado HTTP. Si lo desea puede descargar una aplicación esqueleto de 
ejemplo que le mostrará como puede crear sus propios middlewares y utilizar paquetes de terceros:

https://github.com/danielspk/TornadoHttpSkeletonApplication

### Instalación:

Se recomienda instalar esta librería mediante el uso de Composer de la siguiente forma:

```
    php composer.phar require danielspk/tornadohttp:~1.0
```

Esto instalará Tornado HTTP y creará o actualizará el archivo composer.json con la siguiente dependencia:

```
{
    "require": {
        "danielspk/tornadohttp": "~1.0"
    }
}
```

### Crear el contenedor de aplicación:

Tornado HTTP puede construirse de varias formas:

#### Sin parámetros:

```php
    $app = new DMS\TornadoHttp\TornadoHttp();
```

#### Con Middlewares

```php
    $app = new DMS\TornadoHttp\TornadoHttp([
        ['middleware' => $middlewareOne],
        ['middleware' => 'App\MiddlewareTwo', 'path' => '/admin'],
        ['middleware' => ['App\MiddlewareThree', [$paramOne, $paramTwo]]]
    ]);
```

Nota: Puede ver que hay tres formas de registrar un Middleware: callables, strings y arrays. Más adelante se explicará 
en detalle cada una de estas formas. @todo: falta mostrar objeto instanciado y servicio string.

#### Con Contenedor de Dependencias

```php
    $app = new DMS\TornadoHttp\TornadoHttp(
        [],
        new Container()
    );
```

Nota: El contenedor de dependencias a utilizar debe implementar la interface "Container Interop". Puede obtener más 
información al respecto en https://github.com/container-interop/container-interop

## Inspiracion:

- Relay - http://relayphp.com/
- Zend Stratigility - https://github.com/zendframework/zend-stratigility

## Licencia:

El proyecto se distribuye bajo la licencia MIT.

## Sugerencias y colaboración:

Email: info@daniel.spiridione.com.ar
