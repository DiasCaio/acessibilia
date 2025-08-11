// amd/src/telemetry.js
// Telemetria do Acessibilia: ouve eventos, loga no console e (opcional) envia metadados ao servidor.

import Ajax from 'core/ajax';
import * as Options from './options';
import {component} from './common';

// Deve existir em db/services.php apontando para classes/external/log_event.php.
const METHOD = 'tiny_acessibilia_log_event';

// Nomes dos eventos do plugin (iguais aos disparados pelo diálogo/fluxo).
const EV = {
  OPEN: 'acessibilia:open',
  FILE_SELECTED: 'acessibilia:file:selected',
  GENERATE_START: 'acessibilia:generate:start',
  GENERATE_SUCCESS: 'acessibilia:generate:success',
  GENERATE_ERROR: 'acessibilia:generate:error',
  INSERT_START: 'acessibilia:insert:start',
  INSERT_SUCCESS: 'acessibilia:insert:success',
  INSERT_ERROR: 'acessibilia:insert:error',
};

// Sanitiza o payload para evitar envio de conteúdo sensível.
const sanitize = (payload = {}) => {
  const out = {};
  for (const [k, v] of Object.entries(payload)) {
    const key = String(k).toLowerCase();
    if (key.includes('alt') || key.includes('image') || key.includes('data')) continue;

    const t = typeof v;
    if (t === 'string') {
      if (v.length > 500) continue;
      out[k] = v;
    } else if (t === 'number' || t === 'boolean') {
      out[k] = v;
    }
    // objetos/funções/símbolos são ignorados para evitar JSON circular
  }
  return out;
};


const send = (editor, event, payload = {}) => {
  // Sempre loga no console (útil p/ depuração local).
  // eslint-disable-next-line no-console
  console.log('[acessibilia]', event, payload);

  // Só envia ao servidor se estiver habilitado nas settings.
  if (!Options.isLogsEnabled(editor)) {
    return Promise.resolve();
  }

  const args = {
    event,
    component,
    ts: Date.now(),
    payload: JSON.stringify(sanitize(payload)), // tudo junto no campo payload
    };

  return Ajax.call([{ methodname: METHOD, args }])[0].catch(() => { /* silencia erros de telemetria */ });
};

// Anexa listeners no editor para todos os eventos do Acessibilia.
export const attach = (editor) => {
  Object.values(EV).forEach((name) => {
    editor.on(name, (e) => send(editor, name, e));
  });
};

export default { attach, EV };
