import { Plus } from 'lucide-react';
import { Link } from 'react-router-dom';
import { PositionsTable } from '../components/tables';
import { EmptyState, ErrorState, FullScreenState, Page, Panel } from '../components/ui';
import { useFetch } from '../hooks/useFetch';
import { useI18n } from '../i18n/I18nContext';

export function PortfolioPage() {
  const { t } = useI18n();
  const { data, loading, error, reload } = useFetch('/portfolio');
  const positions = data?.data ?? [];
  const baseCurrency = data?.base_currency ?? 'USD';

  return (
    <Page title={t('portfolioTitle')} action={<Link className="primary-button" to="/search"><Plus size={16} /> {t('addStock')}</Link>}>
      {loading ? <FullScreenState title={t('loadingPortfolio')} /> : error ? <ErrorState error={error} reload={reload} /> : positions.length ? <Panel title={t('positions')}><PositionsTable rows={positions} baseCurrency={baseCurrency} showActions /></Panel> : <EmptyState title={t('noPositions')} text={t('noPositionsText')} />}
    </Page>
  );
}
