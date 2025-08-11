// amd/src/commands.js
// Tiny Acessibilia Commands (estilo plugin de mídia)

import {getString} from 'core/str';
import {getButtonImage} from 'editor_tiny/utils';
import {component, buttonName, menuItemName, iconName, KEYS} from './common';
import * as Dialog from './dialog';

// Pré-carrega textos + SVG do ícone (pix/icon.svg).
const loadAssets = async () => {
  const [buttonTitle, menuLabel, iconSvgRaw] = await Promise.all([
    getString(KEYS.BUTTON_TITLE, component),
    getString(KEYS.MENU_LABEL, component),
    getButtonImage('icon', component),
  ]);

  // Normaliza o retorno do ícone para string.
  const iconSvg = (typeof iconSvgRaw === 'string')
    ? iconSvgRaw
    : (iconSvgRaw?.html ?? iconSvgRaw?.toString?.() ?? '');

  return {buttonTitle, menuLabel, iconSvg};
};

// Padrão: getSetup() (async) → retorna função (editor)=>{...} síncrona.
export const getSetup = async () => {
  const {buttonTitle, menuLabel, iconSvg} = await loadAssets();

  return (editor) => {
    // 1) Registrar o ícone SVG no Tiny
    if (iconSvg) {
      editor.ui.registry.addIcon(iconName, iconSvg);
    } else {
      // Fallback simples caso algo dê errado ao obter o SVG
      editor.ui.registry.addIcon(iconName, '<svg viewBox="0 0 24 24"><path d="M3 5h18v14H3z"/></svg>');
    }

    // 2) Botão na toolbar
    editor.ui.registry.addButton(buttonName, {
      tooltip: buttonTitle,
      icon: iconName,
      onAction: () => Dialog.open(editor),
    });

    // 3) Item no menubar (Insert)
    editor.ui.registry.addMenuItem(menuItemName, {
      text: menuLabel,
      icon: iconName,
      onAction: () => Dialog.open(editor),
    });
  };
};

export default { getSetup };
