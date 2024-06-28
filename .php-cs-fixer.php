<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect()) // @TODO 4.0 no need to call this manually
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']], // one should use PHPUnit built-in method instead
        'modernize_strpos' => true, // needs PHP 8+ or polyfill
        'no_useless_concat_operator' => false, // TODO switch back on when the `src/Console/Application.php` no longer needs the concat
        'numeric_literal_separator' => true,
        'protected_to_private' => false,
        'native_constant_invocation' => ['strict' => false],
    ])
    ->setFinder(
        (new Finder())
            ->ignoreDotFiles(false)
            ->ignoreVCSIgnored(true)
            ->exclude(['dev-tools/phpstan', 'tests/Fixtures'])
            ->in(__DIR__.'/src')
    )
    ;