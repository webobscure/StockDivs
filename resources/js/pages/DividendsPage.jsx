import { DividendTable } from '../components/tables';
import { Page, Panel } from '../components/ui';
import { useFetch } from '../hooks/useFetch';
import { useI18n } from '../i18n/I18nContext';

export function DividendsPage() {
  const { t } = useI18n();
  const { data } = useFetch('/dividends/upcoming');
  const rows = data?.data ?? [];

  return <Page title={t('dividendsTitle')}><Panel title={t('upcomingPayments')}><DividendTable rows={rows} /></Panel></Page>;
}
