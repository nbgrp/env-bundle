<?php declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__)
            ->append([__FILE__])
            ->notPath('src/DependencyInjection/Configuration.php')
    )
    ->setRiskyAllowed(true)
    ->setRules([
        // base presets
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        '@Symfony' => true,

        // risky presets
        '@PhpCsFixer:risky' => true,
        '@Symfony:risky' => true,

        // presets tuning
        'binary_operator_spaces' => [
            'operators' => [
                '|' => null,
            ],
        ],
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => [
            'statements' => ['case', 'default', 'declare', 'return', 'throw', 'try'],
        ],
        'comment_to_phpdoc' => [
            'ignored_tags' => [
                'see',
                'todo',
            ],
        ],
        'linebreak_after_opening_tag' => false,
        'method_argument_space' => [
            'on_multiline' => 'ignore',
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'new_line_for_chained_calls',
        ],
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use_trait',
            ],
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => true,
        ],
        'ordered_class_elements' => false,
        'ordered_imports' => [
            'imports_order' => [
                'const',
                'class',
                'function',
            ],
        ],
        'phpdoc_separation' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'single_line_throw' => false,
        'yoda_style' => false,

        // no-preset rules
        'date_time_immutable' => true,
        'final_class' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'self_static_accessor' => true,
        'simplified_null_return' => true,
        'static_lambda' => true,
    ])
    ;
