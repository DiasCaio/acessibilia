// amd/src/configuration.js
// Tiny Acessibilia configuration — alinha com o plugin de mídia.

import {buttonName, menuItemName} from './common';

/**
 * Garante que o item do Acessibilia exista no menu Insert.
 */
const configureMenu = (menu) => {
  if (!menu.insert) {
    menu.insert = {items: ''};
  } else if (typeof menu.insert.items !== 'string') {
    menu.insert.items = String(menu.insert.items || '');
  }

  const item = menuItemName;
  const pattern = new RegExp(`\\b${item}\\b`);
  if (!pattern.test(menu.insert.items)) {
    menu.insert.items = menu.insert.items ? `${menu.insert.items} ${item}` : item;
  }
  return menu;
};

/**
 * Adiciona o botão do Acessibilia no início da seção 'content' da toolbar.
 */
const configureToolbar = (toolbar) => {
  return toolbar.map((section) => {
    if (section.name === 'content') {
      if (!section.items.includes(buttonName)) {
        section.items.unshift(buttonName);
      }
    }
    return section;
  });
};

export const configure = (instanceConfig) => {
  // DEBUG: veja se as opções namespaced estão aqui
  console.log('[acessibilia] instanceConfig',
    instanceConfig && {
      apiurl: instanceConfig['tiny_acessibilia:apiurl'],
      prompt: instanceConfig['tiny_acessibilia:prompt'],
      apikeyprovided: instanceConfig['tiny_acessibilia:apikeyprovided'],
    }
  );
  // FIM DO DEBUG
  return {
    menu: configureMenu(instanceConfig.menu),
    toolbar: configureToolbar(instanceConfig.toolbar),
  };
};

export default {configure};
