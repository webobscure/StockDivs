import { useState } from 'react';
import { useParams } from 'react-router-dom';
import { api } from '../api/client';
import { PositionsTable, TransactionsTable } from '../components/tables';
import { EmptyState, Page, Panel, Select, TextInput } from '../components/ui';
import { useFetch } from '../hooks/useFetch';
import { useI18n } from '../i18n/I18nContext';

export function PortfolioDetailPage() {
  const { t } = useI18n();
  const { ticker } = useParams();
  const { data, reload } = useFetch(`/portfolio/${ticker}`);
  const [form, setForm] = useState({
    ticker,
    type: 'buy',
    quantity: 1,
    price: 100,
    currency: 'USD',
    transaction_date: new Date().toISOString().slice(0, 10),
    commission: 0,
    notes: '',
  });
  const position = data?.data;
  const baseCurrency = data?.base_currency ?? 'USD';
  const transactions = data?.transactions?.data ?? [];
  const submit = async (event) => {
    event.preventDefault();
    await api('/portfolio/transactions', { method: 'POST', body: JSON.stringify(form) });
    reload();
  };

  return (
    <Page title={`${ticker} ${t('details')}`}>
      <section className="dashboard-grid">
        <Panel title={t('position')}>{position ? <PositionsTable rows={[position]} baseCurrency={baseCurrency} /> : <EmptyState title={t('noActiveHolding')} text={t('noActiveHoldingText')} />}</Panel>
        <Panel title={t('buyOrSell')}>
          <form className="form-grid" onSubmit={submit}>
            <Select label={t('type')} value={form.type} onChange={(type) => setForm({ ...form, type })} options={['buy', 'sell']} />
            <TextInput label={t('quantity')} type="number" value={form.quantity} onChange={(quantity) => setForm({ ...form, quantity })} />
            <TextInput label={t('price')} type="number" value={form.price} onChange={(price) => setForm({ ...form, price })} />
            <TextInput label={t('currency')} value={form.currency} onChange={(currencyValue) => setForm({ ...form, currency: currencyValue })} />
            <TextInput label={t('date')} type="date" value={form.transaction_date} onChange={(transactionDate) => setForm({ ...form, transaction_date: transactionDate })} />
            <TextInput label={t('commission')} type="number" value={form.commission} onChange={(commission) => setForm({ ...form, commission })} />
            <button className="primary-button">{t('saveTransaction')}</button>
          </form>
        </Panel>
      </section>
      <Panel title={t('transactions')}><TransactionsTable rows={transactions} /></Panel>
    </Page>
  );
}
