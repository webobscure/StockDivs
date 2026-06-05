import { useState } from 'react';
import { Search } from 'lucide-react';
import { api } from '../api/client';
import { StockResult } from '../components/StockResult';
import { Page, Panel } from '../components/ui';
import { useI18n } from '../i18n/I18nContext';

export function SearchPage() {
  const { t } = useI18n();
  const [query, setQuery] = useState('AAPL');
  const [results, setResults] = useState([]);
  const [busy, setBusy] = useState(false);
  const search = async (event) => {
    event?.preventDefault();
    setBusy(true);
    const payload = await api(`/stocks/search?query=${encodeURIComponent(query)}`).finally(() => setBusy(false));
    setResults(payload.data ?? []);
  };

  return (
    <Page title={t('searchStocks')}>
      <form className="search-bar" onSubmit={search}>
        <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder={t('tickerOrCompany')} />
        <button className="primary-button" disabled={busy}><Search size={16} /> {t('search')}</button>
      </form>
      <Panel title={t('results')}>
        <div className="stock-grid">{results.map((stock) => <StockResult key={stock.ticker} stock={stock} />)}</div>
      </Panel>
    </Page>
  );
}
