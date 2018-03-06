#!/usr/bin/env php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                              => true,
        'array_syntax'                          => ['syntax' => 'short'],
        'binary_operator_spaces'                => ['align_double_arrow' => false],
        'linebreak_after_opening_tag'           => true,
        'list_syntax'                           => ['syntax' => 'short'],
        'no_null_property_initialization'       => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else'                       => true,
        'no_useless_return'                     => true,
        'not_operator_with_space'               => false,
        'not_operator_with_successor_space'     => false,
        'ordered_class_elements'                => true,
        'ordered_imports'                       => true,
        'php_unit_strict'                       => true,
        'phpdoc_order'                          => true,
        'phpdoc_types_order'                    => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'simplified_null_return'                => false,
        'strict_comparison'                     => true,
        'strict_param'                          => true,
        'ternary_to_null_coalescing'            => true,
        'void_return'                           => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php_cs.cache');
