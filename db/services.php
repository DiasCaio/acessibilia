<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'tiny_acessibilia_generate_alt' => [
        'classname'   => 'tiny_acessibilia\\external\\generate_alt',
        'methodname'  => 'execute',
        'description' => 'Generate an alt text for an image using an external AI service.',
        'type'        => 'read',
        'ajax'        => true,   // << ESSENCIAL para lib/ajax/service.php
    ],
];
