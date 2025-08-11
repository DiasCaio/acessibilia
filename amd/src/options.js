// amd/src/options.js
// Registro e acesso às opções do tiny_acessibilia (lado TinyMCE).
// As values efetivas vêm do servidor (settings.php → plugininfo.php).
// Aqui definimos o namespace, os tipos e defaults de segurança.

import {getPluginOptionName} from 'editor_tiny/options';
import {pluginName} from './common';

// Sufixos em snake_case — devem coincidir com as chaves retornadas no plugininfo.php
const KEYS = {
  API_URL: 'apiurl',
  MODEL: 'model',
  PROMPT: 'prompt',
  TIMEOUT: 'timeout',
  LOGS_ENABLED: 'logs',
  FORCE_ALT: 'forcealt',
  ALLOW_DECORATIVE: 'allowdecorative',
  API_KEY_PROVIDED: 'apikeyprovided',
};

const on = (key) => getPluginOptionName(pluginName, key);

export const register = (editor) => {
  // Strings
  editor.options.register(on(KEYS.API_URL), {
    processor: 'string',
    default: 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
  });

  editor.options.register(on(KEYS.MODEL), {
    processor: 'string',
    default: 'gemini-2.0-flash',
  });

  editor.options.register(on(KEYS.PROMPT), {
    processor: 'string',
    default: 'Gere uma descrição objetiva e útil para leitores de tela, em português.',
  });

  // Number
  editor.options.register(on(KEYS.TIMEOUT), {
    processor: 'number',
    default: 15000, // ms
  });

  // Booleans
  editor.options.register(on(KEYS.LOGS_ENABLED), {
    processor: 'boolean',
    default: true,
  });

  editor.options.register(on(KEYS.FORCE_ALT), {
    processor: 'boolean',
    default: true,
  });

  editor.options.register(on(KEYS.ALLOW_DECORATIVE), {
    processor: 'boolean',
    default: true,
  });

  editor.options.register(on(KEYS.API_KEY_PROVIDED), {
    processor: 'boolean',
    default: false,
  });
};

// Getters convenientes
export const getApiUrl = (editor) => editor.options.get(on(KEYS.API_URL));
export const getModel = (editor) => editor.options.get(on(KEYS.MODEL));
export const getPrompt = (editor) => editor.options.get(on(KEYS.PROMPT));
export const getTimeout = (editor) => Number(editor.options.get(on(KEYS.TIMEOUT))) || 15000;

export const isLogsEnabled = (editor) => !!editor.options.get(on(KEYS.LOGS_ENABLED));
export const isForceAlt = (editor) => !!editor.options.get(on(KEYS.FORCE_ALT));
export const isDecorativeAllowed = (editor) => !!editor.options.get(on(KEYS.ALLOW_DECORATIVE));
export const isApiKeyProvided = (editor) => !!editor.options.get(on(KEYS.API_KEY_PROVIDED));

export default {
  register,
  getApiUrl,
  getModel,
  getPrompt,
  getTimeout,
  isLogsEnabled,
  isForceAlt,
  isDecorativeAllowed,
  isApiKeyProvided,
};
