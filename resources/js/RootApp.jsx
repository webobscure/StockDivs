import { useEffect, useMemo, useState } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { api, token } from './api/client';
import { FullScreenState } from './components/ui';
import { I18nContext, translate } from './i18n/I18nContext';
import { Shell } from './layout/Shell';
import { AuthPage } from './pages/AuthPage';
import { applyTheme, storedTheme } from './theme';

export function RootApp() {
  const [user, setUser] = useState(null);
  const [loadingUser, setLoadingUser] = useState(Boolean(token()));
  const lang = user?.setting?.language ?? 'en';
  const theme = user?.setting?.theme ?? storedTheme();
  const i18n = useMemo(() => ({ lang, t: (key) => translate(lang, key) }), [lang]);

  useEffect(() => {
    applyTheme(theme);
  }, [theme]);

  useEffect(() => {
    if (!token()) return;
    api('/user')
      .then(setUser)
      .catch(() => localStorage.removeItem('stockdivs_token'))
      .finally(() => setLoadingUser(false));
  }, []);

  if (loadingUser) return <FullScreenState title={translate(lang, 'loadingAccount')} />;

  return (
    <I18nContext.Provider value={i18n}>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<AuthPage mode="login" setUser={setUser} />} />
          <Route path="/register" element={<AuthPage mode="register" setUser={setUser} />} />
          <Route path="/*" element={user ? <Shell user={user} setUser={setUser} /> : <Navigate to="/login" replace />} />
        </Routes>
      </BrowserRouter>
    </I18nContext.Provider>
  );
}
