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
 * Tiny Acessibilia dialog (modal).
 *
 * @module      tiny_acessibilia/dialog
 * @copyright   2025
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import {component} from './common';
import * as Options from './options';
import * as Insert from './insert';
import * as AI from './ai_client';

// Tipos aceitos no MVP (SVG fica para depois).
const ACCEPT_MIME = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
const ACCEPT_ATTR = '.png,.jpg,.jpeg,.webp,.gif';

// Chaves de string usadas aqui (mantemos local para não inflar o common.js).
const STR = {
    dialogtitle: 'dialogtitle',
    selectfile: 'selectfile',
    generate: 'generate',
    regenerate: 'regenerate',
    insert: 'insert',
    cancel: 'cancel',
    altlabel: 'altlabel',
    decorative: 'decorative',
    error_nofile: 'error_nofile',
    error_unsupported: 'error_unsupported',
    error_ia: 'error_ia',
    notice_privacy: 'notice_privacy',
};

// Util: cria elemento com classes
const el = (tag, className, attrs = {}) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    Object.entries(attrs).forEach(([k, v]) => node.setAttribute(k, v));
    return node;
};

// Gera UI HTML da dropzone + preview.
const renderDropzoneHtml = (strings) => {
  const acceptText = ACCEPT_ATTR.replace(/\./g, '').toUpperCase().replace(/,/g, ', ');
  return `
    <style>
      .acessibilia-dz{border:2px dashed #c7c7c7;border-radius:6px;padding:16px;text-align:center;margin-bottom:12px}
      .acessibilia-dz-inner{display:flex;flex-direction:column;gap:8px;align-items:center;justify-content:center}
      .acessibilia-dz .mime{font-size:12px;color:#666;margin-top:-4px}
      .acessibilia-preview{margin-top:8px;text-align:center}
      .acessibilia-preview img{max-width:100%;max-height:220px;border-radius:4px}
      .acessibilia-privacy{font-size:12px;color:#666;margin-top:6px}
    </style>
    <div class="acessibilia dz" data-acessibilia-root>
      <div class="acessibilia-dz" data-dropzone>
        <div class="acessibilia-dz-inner">
          <p>${strings.selectfile}</p>
          <p class="mime">${acceptText}</p>
          <button type="button" class="tox-button" data-file-button>${strings.selectfile}</button>
          <input type="file" accept="${ACCEPT_ATTR}" data-file-input hidden />
        </div>
      </div>
      <div class="acessibilia-preview" data-preview hidden>
        <img alt="" data-preview-img />
      </div>
      <p class="acessibilia-privacy" data-privacy>${strings.notice_privacy}</p>
    </div>
  `;
};


// Carrega as strings usadas no diálogo.
const loadStrings = async () => {
    const keys = Object.values(STR);
    const vals = await Promise.all(keys.map((k) => getString(k, component)));
    const map = {};
    keys.forEach((k, i) => { map[k] = vals[i]; });
    return map;
};

// Fallback para IA enquanto o ai_client não estiver implementado.
const fallbackGenerate = async () => ({ alt: 'Descrição automática gerada (exemplo).' });


// Valida tipo de arquivo.
const isSupportedFile = (file) => file && ACCEPT_MIME.includes(file.type);

// Atualiza habilitação dos botões conforme estado.
const refreshButtons = (api, state) => {
    const canGenerate = !!state.file;
    api.setEnabled('generate', canGenerate);
    api.setEnabled('regenerate', !!state.generated && canGenerate);

    // Inserir: requer alt não vazio OU decorativa marcada.
    const canInsert = !!state.file && ((state.decorative === true) || (state.alt && state.alt.trim().length > 0));
    api.setEnabled('insert', canInsert);
};

// Liga eventos da dropzone/input e devolve funções de controle.
const bindDropzone = (root, onFile) => {
    const dz = root.querySelector('[data-dropzone]');
    const fileBtn = root.querySelector('[data-file-button]');
    const fileInput = root.querySelector('[data-file-input]');
    const preview = root.querySelector('[data-preview]');
    const previewImg = root.querySelector('[data-preview-img]');

    let objectURL = null;

    const clearPreview = () => {
        if (objectURL) {
            URL.revokeObjectURL(objectURL);
            objectURL = null;
        }
        previewImg.removeAttribute('src');
        preview.hidden = true;
    };

    const showPreview = (file) => {
        clearPreview();
        objectURL = URL.createObjectURL(file);
        previewImg.src = objectURL;
        preview.hidden = false;
    };

    const handleFiles = (files) => {
        const file = files && files[0];
        if (!file) return {error: 'nofile'};
        if (!isSupportedFile(file)) return {error: 'unsupported'};
        showPreview(file);
        onFile(file);
        return {ok: true};
    };

    // Clique para selecionar
    fileBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

    // DnD
    const stop = (e) => { e.preventDefault(); e.stopPropagation(); };
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((ev) => dz.addEventListener(ev, stop));
    dz.addEventListener('drop', (e) => handleFiles(e.dataTransfer?.files));

    return {
        destroy: () => {
            clearPreview();
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((ev) => dz.removeEventListener(ev, stop));
            fileBtn.replaceWith(fileBtn.cloneNode(true)); // remove listeners
            fileInput.replaceWith(fileInput.cloneNode(true));
        },
    };
};

/**
 * Abre o modal principal.
 * - Seleciona/arrasta imagem (em memória)
 * - Gera descrição (IA) — chama AI.generateAlt quando existir
 * - Edita alt, marca decorativa
 * - Insere (chama Insert.uploadAndInsert)
 *
 * @param {TinyMCEEditor} editor
 */
export const open = async (editor) => {
    const strings = await loadStrings();

    // Estado do diálogo (em memória)
    const state = {
        file: null,
        alt: '',
        decorative: false,
        generated: false,
    };

    // Constrói o corpo do diálogo
    const body = {
        type: 'panel',
        items: [
            {type: 'htmlpanel', html: renderDropzoneHtml(strings)},
            {type: 'input', name: 'alt', label: strings.altlabel},
            {type: 'checkbox', name: 'decorative', label: strings.decorative},
        ],
    };

    const api = editor.windowManager.open({
        title: strings.dialogtitle,
        size: 'large',
        body,
        initialData: {alt: '', decorative: false},
        buttons: [
            {type: 'custom', name: 'generate', text: strings.generate, primary: false, disabled: true},
            {type: 'custom', name: 'regenerate', text: strings.regenerate, primary: false, disabled: true},
            {type: 'cancel', name: 'cancel', text: strings.cancel},
            {type: 'submit', name: 'insert', text: strings.insert, primary: true, disabled: true},
        ],
        onAction: async (apiRef, details) => {
            if (details.name === 'generate' || details.name === 'regenerate') {
                if (!state.file) {
                    editor.windowManager.alert(strings.error_nofile);
                    return;
                }
                // Desabilita botões enquanto gera.
                apiRef.setEnabled('generate', false);
                apiRef.setEnabled('regenerate', false);

                // Obtém prompt/config do Options (quando disponível).
                const promptText = (Options?.getPrompt && Options.getPrompt(editor)) || '';
                const start = performance.now();

                try {
                    let alt = '';
                    if (AI && typeof AI.generateAlt === 'function') {
                        const res = await AI.generateAlt(editor, state.file, { prompt: promptText });
                        alt = res?.alt || '';
                    } else {
                        const res = await fallbackGenerate();
                        alt = res?.alt || '';
                    }
                    state.alt = alt;
                    state.generated = true;
                    apiRef.setData({ alt: state.alt });

                } catch (err) {
                    // eslint-disable-next-line no-console
                    console.error('[tiny_acessibilia] IA error:', err);
                    editor.windowManager.alert(strings.error_ia);
                } finally {
                    // Reabilita botões; “regenerate” habilita após 1ª geração.
                    apiRef.setEnabled('generate', !!state.file);
                    apiRef.setEnabled('regenerate', !!state.file);
                    refreshButtons(apiRef, state);
                    const ms = Math.round(performance.now() - start);
                    // Eventos/telemetria serão tratados no telemetry.js (quando implementado).
                    // editor.fire('acessibilia:generate:success', { ms, chars: state.alt.length });
                }
            }
        },
        onChange: (apiRef, change) => {
            if (change.name === 'alt') {
                state.alt = (apiRef.getData().alt || '');
            }
            if (change.name === 'decorative') {
                const {decorative} = apiRef.getData();
                state.decorative = !!decorative;
                // Se decorativa, limpamos e desabilitamos o campo alt.
                const altValue = state.decorative ? '' : state.alt;
                state.alt = altValue;
                apiRef.setData({alt: altValue});
                // Tiny não tem disable por-campo diretamente; poderíamos alternar via redial,
                // mas para o MVP manteremos apenas a validação no botão "Inserir".
            }
            refreshButtons(apiRef, state);
        },
        onSubmit: async (apiRef) => {
            if (!state.file) {
                editor.windowManager.alert(strings.error_nofile);
                return;
            }
            // Regras de alt
            if (!state.decorative && (!state.alt || state.alt.trim().length === 0)) {
                // Força alt não vazio (MVP).
                editor.windowManager.alert(strings.error_nofile); // Reaproveitamos; ideal: string específica.
                return;
            }
            try {
                // editor.fire('acessibilia:insert:start');
                await Insert.uploadAndInsert(editor, state.file, state.alt, state.decorative);
                // editor.fire('acessibilia:insert:success');
                apiRef.close();
            } catch (err) {
                // eslint-disable-next-line no-console
                console.error('[tiny_acessibilia] insert error:', err);
                editor.windowManager.alert(strings.error_ia);
                // editor.fire('acessibilia:insert:error', { code: 'upload' });
            }
        },
        onClose: () => {
            dzControls?.destroy?.();// Cleanup se necessário (listeners da dropzone são removidos pelo retorno de bindDropzone).
        },
    });

    // Após abertura, ligar a dropzone/inputs no HTMLPanel.
    const container = document.querySelector('.tox-dialog [data-acessibilia-root]');
    const dzControls = bindDropzone(container, (file) => {
        state.file = file;
        // editor.fire('acessibilia:file:selected', { mime: file.type, bytes: file.size });

        // Ao selecionar arquivo, habilita “Gerar descrição”.
        refreshButtons(api, state);
    });

    // Habilita/desabilita assim que abre (sem arquivo ainda).
    refreshButtons(api, state);

    // Retorna referência útil (opcional)
    return {
        close: () => api.close(),
        destroy: () => {
            dzControls?.destroy?.();
            api.close();
        },
    };
};

export default {open};
