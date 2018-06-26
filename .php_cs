<?php
$finder = PhpCsFixer\Finder::create();

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'space_after_semicolon' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'ordered_imports' => true,
        'no_blank_lines_after_phpdoc' => true,
        'align_multiline_comment' => true,
        'binary_operator_spaces' => true,
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => true,
        'braces' => true,
        'class_definition' => true,
        'declare_equal_normalize' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_unused_imports' => true,
        'phpdoc_types' => true,
    ])
    ->setFinder($finder)
;