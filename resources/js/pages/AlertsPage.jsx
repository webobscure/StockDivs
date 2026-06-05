import { useState } from 'react';
import { api } from '../api/client';
import { AlertsTable } from '../components/tables';
import { Page, Panel, Select, TextInput } from '../components/ui';
import { useFetch } from '../hooks/useFetch';
import { useI18n } from '../i18n/I18nContext';

export function AlertsPage() {
  const { t } = useI18n();
  const { data, reload } = useFetch('/alerts');
  const [form, setForm] = useState({ ticker: 'AAPL', type: 'price_above', target_value: 250 });
  const submit = async (event) => {
    event.preventDefault();
    await api('/alerts', { method: 'POST', body: JSON.stringify(form) });
    reload();
  };

  return (
    <Page title={t('alertsTitle')}>
      <section className="dashboard-grid">
        <Panel title={t('newAlert')}>
          <form className="form-grid" onSubmit={submit}>
            <TextInput label={t('ticker')} value={form.ticker} onChange={(ticker) => setForm({ ...form, ticker })} />
            <Select label={t('type')} value={form.type} onChange={(type) => setForm({ ...form, type })} options={['price_above', 'price_below', 'percent_change', 'dividend_date']} />
            <TextInput label={t('target')} type="number" value={form.target_value} onChange={(targetValue) => setForm({ ...form, target_value: targetValue })} />
            <button className="primary-button">{t('createAlert')}</button>
          </form>
        </Panel>
        <Panel title={t('activeAlerts')}><AlertsTable rows={data?.data ?? []} /></Panel>
      </section>
    </Page>
  );
}
