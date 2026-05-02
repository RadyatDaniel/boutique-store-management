<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/routes',
    ])
    ->exclude([
        'vendor',
        'storage',
        'tests',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
