<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig && $ADMIN->fulltree) {
    // Liga/desliga.
    $settings->add(new admin_setting_configcheckbox(
        'tiny_acessibilia/enabled',
        get_string('setting:enabled', 'tiny_acessibilia'),
        get_string('setting:enabled_desc', 'tiny_acessibilia'),
        1
    ));

    // URL da API.
    $settings->add(new admin_setting_configtext(
        'tiny_acessibilia/apiurl',
        get_string('setting:apiurl', 'tiny_acessibilia'),
        get_string('setting:apiurl_desc', 'tiny_acessibilia'),
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
        PARAM_URL
    ));

    // Modelo (permite hífen e ponto → use PARAM_TEXT).
    $settings->add(new admin_setting_configtext(
        'tiny_acessibilia/model',
        get_string('setting:model', 'tiny_acessibilia'),
        get_string('setting:model_desc', 'tiny_acessibilia'),
        'gemini-2.0-flash',
        PARAM_TEXT
    ));

    // Chave (oculta).
    $settings->add(new admin_setting_configpasswordunmask(
        'tiny_acessibilia/apikey',
        get_string('setting:apikey', 'tiny_acessibilia'),
        get_string('setting:apikey_desc', 'tiny_acessibilia'),
        ''
    ));

    // Prompt.
    $settings->add(new admin_setting_configtextarea(
        'tiny_acessibilia/prompt',
        get_string('setting:prompt', 'tiny_acessibilia'),
        get_string('setting:prompt_desc', 'tiny_acessibilia'),
        'Gere uma descrição objetiva e útil para leitores de tela, em português.',
        PARAM_TEXT
    ));

    // Timeout.
    $settings->add(new admin_setting_configtext(
        'tiny_acessibilia/timeout',
        get_string('setting:timeout', 'tiny_acessibilia'),
        get_string('setting:timeout_desc', 'tiny_acessibilia'),
        15000,
        PARAM_INT
    ));

    // Regras de UX.
    $settings->add(new admin_setting_configcheckbox(
        'tiny_acessibilia/forcealt',
        get_string('setting:forcealt', 'tiny_acessibilia'),
        get_string('setting:forcealt_desc', 'tiny_acessibilia'),
        1
    ));
    $settings->add(new admin_setting_configcheckbox(
        'tiny_acessibilia/allowdecorative',
        get_string('setting:allowdecorative', 'tiny_acessibilia'),
        get_string('setting:allowdecorative_desc', 'tiny_acessibilia'),
        1
    ));

    // Logs.
    $settings->add(new admin_setting_configcheckbox(
        'tiny_acessibilia/logs',
        get_string('setting:logs', 'tiny_acessibilia'),
        get_string('setting:logs_desc', 'tiny_acessibilia'),
        1
    ));
}
