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
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugininfo (ponte Moodle → Tiny) do tiny_acessibilia.
 *
 * @package   tiny_acessibilia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_acessibilia;

defined('MOODLE_INTERNAL') || die();

final class plugininfo extends \editor_tiny\plugin implements
    \editor_tiny\plugin_with_buttons,
    \editor_tiny\plugin_with_menuitems,
    \editor_tiny\plugin_with_configuration {

    /**
     * Declara os botões disponíveis (devem bater com o JS).
     *
     * @return array
     */
    public static function get_available_buttons(): array {
        // Deve ser idêntico ao buttonName do JS (ex.: 'tiny_acessibilia/acessibilia').
        return ['tiny_acessibilia/acessibilia'];
    }

    /**
     * Declara os itens de menu disponíveis (devem bater com o JS).
     *
     * @return array
     */
    public static function get_available_menuitems(): array {
        // Deve ser idêntico ao menuItemName do JS (ex.: 'tiny_acessibilia/acessibilia').
        return ['tiny_acessibilia/acessibilia'];
    }

    /**
     * O plugin está habilitado para este contexto/editor?
     *
     * @param \context $context
     * @param array $options
     * @param array $fpoptions
     * @param \editor_tiny\editor|null $editor
     * @return bool
     */
    public static function is_enabled(
        \context $context,
        array $options,
        array $fpoptions,
        ?\editor_tiny\editor $editor = null
    ): bool {
        $cfg = get_config('tiny_acessibilia');

        // Desligado globalmente?
        if (!empty($cfg->enabled) && (int)$cfg->enabled === 0) {
            return false;
        }

        // (Opcional) Respeita capability se existir.
        if (function_exists('get_capability_info') && get_capability_info('tiny/acessibilia:use')) {
            return has_capability('tiny/acessibilia:use', $context);
        }

        return true;
    }

    /**
     * Config repassada ao Tiny (nomes em snake_case).
     * NUNCA envia dados sensíveis (ex.: apikey).
     *
     * @param \context $context
     * @param array $options
     * @param array $fpoptions
     * @param \editor_tiny\editor|null $editor
     * @return array
     */
    public static function get_plugin_configuration_for_context(
        \context $context,
        array $options,
        array $fpoptions,
        ?\editor_tiny\editor $editor = null
    ): array {
        $cfg = get_config('tiny_acessibilia');
        $lenkey = isset($cfg->apikey) ? strlen((string)$cfg->apikey) : 0;

        return [
            'apiurl'           => (string)($cfg->apiurl ?? ''),
            'model'            => (string)($cfg->model ?? ''),
            'prompt'           => (string)($cfg->prompt ?? ''),
            'timeout'          => (int)   ($cfg->timeout ?? 15000),
            'logs'             => !empty($cfg->logs),
            'forcealt'         => !empty($cfg->forcealt),
            'allowdecorative'  => !empty($cfg->allowdecorative),
            // Apenas sinalização: true se há chave configurada (sem expor o valor).
            'apikeyprovided'   => ($lenkey > 0),
        ];
    }
}
