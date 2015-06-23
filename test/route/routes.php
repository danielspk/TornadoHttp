<?php

return [
    ['GET', '/tornadoHttp'           , ['handler1', 'middlewareAuth', 'middlewareMailer']],
    ['GET', '/user/{id:\d+}/{name}'  , ['handler2', 'middlewareAuth', 'middlewareMailer']],
    ['GET', '/user/{id:\d+}[/{name}]', ['handler3', 'middlewareAuth', 'middlewareMailer']]
];