<?php

/** @var \DMS\TornadoHttp\TornadoHttp $pNext */

/**
 * @param $c \Pimple\Container
 * @return \DateTime
 */
$pNext->getDI()['fecha'] = function($c) {
    return new \DateTime();
};