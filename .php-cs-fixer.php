<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('build')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP70Migration' => true,
        '@PHP70Migration:risky' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHP73Migration' => true,
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PSR12' => true,
        '@PSR12:risky' => true,

        'native_function_invocation' => [
            'exclude' => ['in_array', 'is_array', 'is_string', 'preg_match'],
        ],
    ])
    ->setFinder($finder);
