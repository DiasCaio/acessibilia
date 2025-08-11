<?php
// This file is part of Moodle - http://moodle.org/
//
// GNU GPL v3 or later.

namespace tiny_acessibilia\external;

defined('MOODLE_INTERNAL') || die();

use context_system;
use core\session\manager;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use moodle_exception;

/**
 * Lightweight server-side logger for Acessibilia usage metadata.
 *
 * Writes newline-delimited JSON into $CFG->dataroot . '/temp/tiny_acessibilia.log'
 * when logs are enabled in plugin settings.
 *
 * No personal content (image or full alt text) should be sent here.
 *
 * @package    tiny_acessibilia
 */
final class log_event extends external_api {

    /**
     * Parameters for the external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'event'     => new external_value(PARAM_TEXT, 'Event name, e.g. acessibilia:insert:success'),
            'component' => new external_value(PARAM_COMPONENT, 'Caller component', VALUE_DEFAULT, 'tiny_acessibilia'),
            'ts'        => new external_value(PARAM_INT, 'Client timestamp (ms)', VALUE_DEFAULT, 0),
            // JSON string with extra metadata (already sanitized on client).
            'payload'   => new external_value(PARAM_RAW, 'Optional JSON payload', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Execute: append a single JSON line to the log file if logging is enabled.
     *
     * @param string $event
     * @param string $component
     * @param int $ts
     * @param string $payload
     * @return array
     * @throws moodle_exception
     */
    public static function execute(string $event, string $component = 'tiny_acessibilia', int $ts = 0, string $payload = ''): array {
        global $CFG, $USER;

        self::validate_parameters(self::execute_parameters(), [
            'event'     => $event,
            'component' => $component,
            'ts'        => $ts,
            'payload'   => $payload,
        ]);

        require_login();
        $context = context_system::instance();

        // (Opcional) Se existe a capability e quiser reforçar:
        if (get_capability_info('tiny/acessibilia:use')) {
            require_capability('tiny/acessibilia:use', $context);
        }

        // Respeita configuração do admin.
        $cfg = get_config('tiny_acessibilia');
        $enabled = isset($cfg->logs) ? (int)$cfg->logs === 1 : true;

        $logged = false;
        if ($enabled) {
            // Monta o registro.
            $entry = [
                'time'      => time(),
                'event'     => (string)$event,
                'component' => (string)$component,
                'userid'    => (int)$USER->id,
                'ip'        => (string)(manager::get_remote_address() ?? ''),
                'ua'        => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
                'clientts'  => (int)$ts,
            ];

            // Tenta decodificar o payload JSON (opcional).
            if ($payload !== '') {
                $decoded = json_decode($payload, true);
                if (is_array($decoded)) {
                    // Mescla apenas campos simples para evitar conteúdo sensível.
                    foreach ($decoded as $k => $v) {
                        if (is_scalar($v)) {
                            // Evita campos potencialmente sensíveis/longos.
                            $lk = strtolower((string)$k);
                            if (strpos($lk, 'alt') !== false || strpos($lk, 'image') !== false || strpos($lk, 'data') !== false) {
                                continue;
                            }
                            $entry[$k] = (string)$v;
                        }
                    }
                }
            }

            // Caminho do arquivo de log.
            $logfile = $CFG->dataroot . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'tiny_acessibilia.log';

            // Gravação em modo append.
            $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            $ok = @file_put_contents($logfile, $line, FILE_APPEND | LOCK_EX);

            if ($ok !== false) {
                $logged = true;
            }
        }

        return ['logged' => $logged];
    }

    /**
     * Return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'logged' => new external_value(PARAM_BOOL, 'Whether the entry was logged'),
        ]);
    }
}
