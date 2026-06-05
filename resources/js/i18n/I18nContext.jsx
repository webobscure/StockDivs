import React from 'react';
import { messages } from './messages';

export const I18nContext = React.createContext({ lang: 'en', t: (key) => key });

export function translate(lang, key) {
  return messages[lang]?.[key] ?? messages.en[key] ?? key;
}

export function useI18n() {
  return React.useContext(I18nContext);
}
