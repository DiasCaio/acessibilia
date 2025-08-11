<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'tiny/acessibilia:use' => [
        'riskbitmask' => 0,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,       // Usuários autenticados
            'guest' => CAP_PREVENT,    // Convidados não
        ],
    ],
];
