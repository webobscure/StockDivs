import { useEffect, useState } from 'react';
import { api } from '../api/client';
import { Page, Panel, Select, TextInput, FullScreenState } from '../components/ui';
import { useFetch } from '../hooks/useFetch';
import { useI18n } from '../i18n/I18nContext';
import { languageOptions } from '../i18n/messages';

export function SettingsPage({ setUser }) {
  const { t } = useI18n();
  const { data, reload } = useFetch('/settings');
  const [form, setForm] = useState(null);

  useEffect(() => {
    if (data?.data) setForm(data.data);
  }, [data]);

  const submit = async (event) => {
    event.preventDefault();
    const payload = await api('/settings', { method: 'PUT', body: JSON.stringify(form) });
    setUser((user) => user ? { ...user, setting: payload.data } : user);
    reload();
  };

  if (!form) return <FullScreenState title={t('settingsTitle')} />;

  return (
    <Page title={t('settingsTitle')}>
      <Panel title={t('preferences')}>
        <form className="form-grid compact" onSubmit={submit}>
          <TextInput label={t('baseCurrency')} value={form.base_currency} onChange={(baseCurrency) => setForm({ ...form, base_currency: baseCurrency })} />
          <Select label={t('language')} value={form.language} onChange={(language) => setForm({ ...form, language })} options={languageOptions.map(([value]) => value)} labels={Object.fromEntries(languageOptions)} />
          <Select label={t('theme')} value={form.theme} onChange={(theme) => setForm({ ...form, theme })} options={['light', 'dark', 'system']} />
          <button className="primary-button">{t('saveSettings')}</button>
        </form>
      </Panel>
    </Page>
  );
}
