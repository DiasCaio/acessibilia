// amd/src/insert.js
// Responsável por fazer upload da imagem (draft area) e inserir <img> no editor.

import uploadFile from 'editor_tiny/uploader';

/**
 * Faz upload do arquivo e insere <img> com alt.
 *
 * @param {TinyMCEEditor} editor
 * @param {File|Blob} file
 * @param {string} alt
 * @param {boolean} decorative - Se true, insere alt=""
 * @returns {Promise<void>}
 */
export const uploadAndInsert = async (editor, file, alt, decorative = false) => {
  // Mostra “loading” no Tiny
  editor.setProgressState(true);

  try {
    // Envia ao repositório padrão do Moodle (draft area).
    const url = await uploadFile(
      editor,
      'image',
      file,
      file?.name || 'image',
      // Progress callback (0–100). Por ora só logamos; sem UI de progresso no MVP.
      (pct) => { /* eslint-disable-next-line no-console */ console.log('[acessibilia] upload', pct, '%'); }
    );

    // Sanitiza atributos e monta o HTML final.
    const encUrl = editor.dom.encode(url);
    const encAlt = editor.dom.encode(decorative ? '' : (alt || ''));

    const html = `<img src="${encUrl}" alt="${encAlt}">`;
    editor.insertContent(html);
  } finally {
    editor.setProgressState(false);
  }
};

export default { uploadAndInsert };
