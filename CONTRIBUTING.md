# Como Contribuir

## Pull Requests

1. Realice un fork del repositorio de Tornado Http
2. Cree una nueva rama para cada nueva característica o mejora
3. Envíe un pull request de cada rama creada hacia la rama **develop**

Es muy importante separar las nuevas características o mejoras en distintas ramas, y enviar un pull request para cada 
una de ellas. Esto permite revisar y liberar nuevas mejoras o características de forma individual.

## Guia de Codificación

Todos los pull request deben cumplir con el estándar
[PSR-12](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide-meta.md).

## Test Unitarios

Todos los pull request deben estar acompañados de sus test unitarios y los mismos deben abarcar, por lo menos, el 80%
del código. Tornado Http utiliza la librería PHPUnit para sus test.

[Aquí puede encontrar mayor información sobre PHPUnit](https://phpunit.de/)
