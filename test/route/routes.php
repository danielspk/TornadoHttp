<?php

$r->addRoute('GET', '/tornadoHttp/'          , 'handler1');
$r->addRoute('GET', '/user/{id:\d+}/{name}'  , 'handler2');
$r->addRoute('GET', '/user/{id:\d+}[/{name}]', 'common_handler');