import { RefreshCw } from 'lucide-react';
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { PositionsTable } from '../components/tables';
import { ErrorState, FullScreenState, Metric, Page, Panel } from '../components/ui';
import { useFetch } from '../hooks/useFetch';
import { useI18n } from '../i18n/I18nContext';
import { currency, percent } from '../lib/format';

const chartSeed = [
  { month: 'Jan', value: 18400 },
  { month: 'Feb', value: 19180 },
  { month: 'Mar', value: 18940 },
  { month: 'Apr', value: 20450 },
  { month: 'May', value: 21880 },
  { month: 'Jun', value: 22960 },
];
const pieColors = ['#2563eb', '#059669', '#f59e0b', '#7c3aed', '#dc2626'];

export function DashboardPage() {
  const { t } = useI18n();
  const { data, error, loading, reload } = useFetch('/portfolio/summary');
  const summary = data?.data;
  const positions = summary?.positions ?? [];

  if (loading) return <FullScreenState title={t('dashboardTitle')} />;
  if (error) return <ErrorState error={error} reload={reload} />;

  return (
    <Page title={t('dashboardTitle')} action={<button className="secondary-button" onClick={reload}><RefreshCw size={16} /> {t('refresh')}</button>}>
      <section className="metric-grid">
        <Metric label={t('portfolioValue')} value={currency(summary.total_current_value, summary.base_currency)} trend={percent(summary.daily_change_percent)} />
        <Metric label={t('investedAmount')} value={currency(summary.total_invested, summary.base_currency)} />
        <Metric label={t('totalPl')} value={currency(summary.total_profit, summary.base_currency)} trend={percent(summary.total_profit_percent)} positive={summary.total_profit >= 0} />
        <Metric label={t('annualDividends')} value={currency(summary.expected_annual_dividends, summary.base_currency)} />
      </section>
      <section className="dashboard-grid">
        <Panel title={t('portfolioValue')}>
          <ResponsiveContainer height={280}>
            <AreaChart data={chartSeed.map((item, index) => ({ ...item, value: item.value + (summary.total_current_value || 0) * index / 30 }))}>
              <defs><linearGradient id="value" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stopColor="var(--blue)" stopOpacity="0.28" /><stop offset="100%" stopColor="var(--blue)" stopOpacity="0" /></linearGradient></defs>
              <CartesianGrid strokeDasharray="3 3" stroke="var(--line)" />
              <XAxis dataKey="month" axisLine={false} tickLine={false} />
              <YAxis axisLine={false} tickLine={false} />
              <Tooltip />
              <Area type="monotone" dataKey="value" stroke="var(--blue)" strokeWidth={3} fill="url(#value)" />
            </AreaChart>
          </ResponsiveContainer>
        </Panel>
        <Panel title={t('allocation')}>
          <ResponsiveContainer height={280}>
            <PieChart>
              <Pie data={summary.allocation} dataKey="value" nameKey="ticker" innerRadius={62} outerRadius={96} paddingAngle={2}>
                {summary.allocation.map((_, index) => <Cell key={index} fill={pieColors[index % pieColors.length]} />)}
              </Pie>
              <Tooltip formatter={(value) => currency(value, summary.base_currency)} />
            </PieChart>
          </ResponsiveContainer>
          <div className="legend-list">{summary.allocation.map((item, index) => <span key={item.ticker}><i style={{ background: pieColors[index % pieColors.length] }} />{item.ticker} {item.weight}%</span>)}</div>
        </Panel>
      </section>
      <section className="dashboard-grid narrow">
        <Panel title={t('topPositions')}><PositionsTable rows={positions.slice(0, 5)} baseCurrency={summary.base_currency} /></Panel>
        <Panel title={t('currencyExposure')}>
          <ResponsiveContainer height={220}>
            <BarChart data={summary.currencies}>
              <CartesianGrid strokeDasharray="3 3" stroke="var(--line)" />
              <XAxis dataKey="currency" axisLine={false} tickLine={false} />
              <YAxis axisLine={false} tickLine={false} />
              <Tooltip formatter={(value) => currency(value, summary.base_currency)} />
              <Bar dataKey="value" fill="var(--green)" radius={[6, 6, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </Panel>
      </section>
    </Page>
  );
}
