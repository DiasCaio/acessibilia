<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Webservice: gera descrição (alt) via Google AI (Gemini).
 *
 * @package   tiny_acessibilia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_acessibilia\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/filelib.php'); // curl

use context_system;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use moodle_exception;

/**
 * Gera uma descrição alternativa para imagem usando o endpoint generateContent do Gemini.
 */
final class generate_alt extends external_api {

    /**
     * Parâmetros esperados.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'prompt' => new external_value(PARAM_RAW,  'Prompt base (PT-BR) a ser enviado ao modelo', VALUE_REQUIRED),
            'model'  => new external_value(PARAM_TEXT, 'Nome do modelo (informativo)', VALUE_DEFAULT, 'gemini-2.0-flash'),
            'image'  => new external_single_structure([
                'mime' => new external_value(PARAM_TEXT, 'MIME type (ex.: image/png, image/jpeg)', VALUE_REQUIRED),
                'data' => new external_value(PARAM_RAW,  'Conteúdo base64 (sem prefixo data:)',   VALUE_REQUIRED),
            ], 'Estrutura da imagem', VALUE_REQUIRED),
        ]);
    }

    /**
     * Executa a chamada à API e retorna { alt: string }.
     *
     * @param string $prompt
     * @param string $model
     * @param array  $image ['mime' => string, 'data' => base64 string]
     * @return array{alt:string}
     * @throws moodle_exception
     */
    public static function execute(string $prompt, string $model, array $image): array {
        global $CFG;

        require_login();
        self::validate_context(context_system::instance());

        $mime = trim($image['mime'] ?? '');
        $data = trim($image['data'] ?? '');

        if ($mime === '' || $data === '') {
            throw new invalid_parameter_exception('Missing image data');
        }

        // Config do plugin.
        $cfg      = get_config('tiny_acessibilia');
        $apikey   = trim((string)($cfg->apikey ?? ''));
        $apiurl   = trim((string)($cfg->apiurl ?? 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent'));
        $timeout  = (int)($cfg->timeout ?? 40000); // ms (será convertido para s).
        $timeoutS = max(10, min((int)ceil($timeout / 1000), 120)); // 10..120s

        if ($apikey === '') {
            throw new moodle_exception('missingapikey', 'tiny_acessibilia');
        }

        // Garante que a API key esteja na querystring (sem duplicar).
        if (stripos($apiurl, 'key=') === false) {
            $apiurl .= (strpos($apiurl, '?') === false ? '?' : '&') . 'key=' . urlencode($apikey);
        }

        // Monta payload conforme Gemini generateContent.
        $payload = [
            'contents' => [[
                'role'  => 'user',
                'parts' => [
                    ['text' => (string)$prompt],
                    [
                        'inline_data' => [
                            'mime_type' => $mime,
                            'data'      => $data, // base64 puro (sem "data:...;base64,")
                        ],
                    ],
                ],
            ]],
            'generationConfig' => [
                'temperature'     => 0.2,
                'maxOutputTokens' => 256,
            ],
        ];

        // Chamada HTTP.
        $curl = new \curl();
        $curl->setHeader('Content-Type: application/json');
        $curl->setHeader('Accept: application/json');
        $curl->setopt([
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_TIMEOUT'        => $timeoutS,
        ]);

        $resp = $curl->post($apiurl, json_encode($payload));
        $info = $curl->get_info();
        $http = (int)($info['http_code'] ?? 0);

        if ($resp === false || $http < 200 || $http >= 300) {
            // Tenta extrair erro detalhado do Google.
            $errjson = json_decode((string)$resp, true);
            $gstatus = $errjson['error']['status']  ?? '';
            $gmsg    = $errjson['error']['message'] ?? '';
            // Log servidor (limitado) para diagnóstico.
            error_log('[tiny_acessibilia] Gemini HTTP ' . $http . ' ' . $gstatus . ' ' . $gmsg .
                ' body=' . substr((string)$resp, 0, 500));
            // Propaga informação útil ao front.
            throw new moodle_exception('remoteerror', 'tiny_acessibilia', '', $http . ' ' . $gstatus . ' ' . $gmsg);
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            throw new moodle_exception('invalidresponse', 'tiny_acessibilia');
        }

        // Extrai o primeiro trecho de texto disponível.
        $alt = self::extract_first_text($data);
        $alt = trim((string)$alt);

        return ['alt' => $alt];
    }

    /**
     * Estrutura de retorno.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'alt' => new external_value(PARAM_TEXT, 'Generated alt text'),
        ]);
    }

    /**
     * Extrai o primeiro trecho de texto do payload de resposta do Gemini.
     *
     * @param array $json
     * @return string
     */
    private static function extract_first_text(array $json): string {
        if (isset($json['candidates'][0]['content']['parts']) && is_array($json['candidates'][0]['content']['parts'])) {
            foreach ($json['candidates'][0]['content']['parts'] as $part) {
                if (isset($part['text']) && is_string($part['text'])) {
                    return $part['text'];
                }
            }
        }
        // Fallbacks comuns em algumas respostas.
        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            return (string)$json['candidates'][0]['content']['parts'][0]['text'];
        }
        if (isset($json['candidates'][0]['content']['parts'][0]['inline_data']['data'])) {
            // Não faz sentido devolver isso como alt, mas evita notice.
            return '';
        }
        return '';
    }
}
