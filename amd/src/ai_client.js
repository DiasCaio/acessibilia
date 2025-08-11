import Ajax from 'core/ajax';
import * as Options from './options';

const METHOD = 'tiny_acessibilia_generate_alt';

// Converte um File em { mime, data } (data = base64 sem "data:")
const fileToBase64Parts = (file) => new Promise((resolve, reject) => {
  const fr = new FileReader();
  fr.onload = () => {
    const s = String(fr.result || '');
    const m = s.match(/^data:([^;]+);base64,(.*)$/);
    if (!m) return reject(new Error('dataurl-parse-failed'));
    resolve({ mime: m[1], data: m[2] });
  };
  fr.onerror = reject;
  fr.readAsDataURL(file);
});

/**
 * Chama o webservice e retorna { alt: string }.
 * @param {TinyMCEEditor} editor
 * @param {File} file
 * @param {{prompt?: string}} [opts]
 * @returns {Promise<{alt: string}>}
 */
export const generateAlt = async (editor, file, opts = {}) => {
  if (!file) throw new Error('no-file');

  const { mime, data } = await fileToBase64Parts(file);

  const args = {
    prompt: opts.prompt ?? (Options.getPrompt(editor) || ''),
    model:  Options.getModel(editor) || 'gemini-2.0-flash',
    image:  { mime, data }, // <<< ESSENCIAL
  };

  // (Opcional) log temporário:
  // console.log('[acessibilia] ws args', { mime, len: data.length });

  // Devolvemos o próprio objeto do WS: { alt: '...' }.
  return Ajax.call([{ methodname: METHOD, args }])[0];
};

export default { generateAlt };
