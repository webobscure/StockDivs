import { RefreshCw } from 'lucide-react';
import { StockResult } from '../components/StockResult';
import { FullScreenState, Page, Panel } from '../components/ui';
import { useFetch } from '../hooks/useFetch';
import { useI18n } from '../i18n/I18nContext';

export function WatchlistPage() {
  const { t } = useI18n();
  const { data, loading, reload } = useFetch('/watchlist');
  const rows = data?.data ?? [];

  return (
    <Page title={t('watchlistTitle')} action={<button className="secondary-button" onClick={reload}><RefreshCw size={16} /> {t('refresh')}</button>}>
      {loading ? <FullScreenState title={t('loadingWatchlist')} /> : <Panel title={t('trackedStocks')}><div className="stock-grid">{rows.map((stock) => <StockResult key={stock.ticker} stock={stock} />)}</div></Panel>}
    </Page>
  );
}
