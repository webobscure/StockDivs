import { Link } from 'react-router-dom';
import { useI18n } from '../i18n/I18nContext';
import { currency, percent } from '../lib/format';

export function PositionsTable({ rows, baseCurrency, showActions = false }) {
  const { t } = useI18n();

  return (
    <div className="table-wrap">
      <table>
        <thead>
          <tr>
            <th>{t('ticker')}</th>
            <th>{t('quantity')}</th>
            <th>{t('avgPrice')}</th>
            <th>{t('current')}</th>
            <th>{t('value')}</th>
            <th>P/L</th>
            <th>{t('yield')}</th>
            {showActions && <th />}
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={row.ticker}>
              <td><strong>{row.ticker}</strong><small>{row.company_name}</small></td>
              <td>{row.quantity}</td>
              <td>{currency(row.average_buy_price, row.currency)}</td>
              <td>{currency(row.current_price, row.currency)}</td>
              <td>{currency(row.current_value_base ?? row.current_value, baseCurrency ?? row.currency)}</td>
              <td className={row.unrealized_profit >= 0 ? 'positive' : 'negative'}>
                {currency(row.unrealized_profit, row.currency)}
                <small>{percent(row.unrealized_profit_percent)}</small>
              </td>
              <td>{percent(row.dividend_yield)}</td>
              {showActions && <td><Link className="row-link" to={`/portfolio/${row.ticker}`}>{t('open')}</Link></td>}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export function TransactionsTable({ rows }) {
  const { t } = useI18n();

  return (
    <div className="table-wrap">
      <table>
        <thead><tr><th>{t('date')}</th><th>{t('type')}</th><th>{t('quantity')}</th><th>{t('price')}</th><th>{t('commission')}</th></tr></thead>
        <tbody>{rows.map((row) => <tr key={row.id}><td>{row.transaction_date}</td><td>{row.type}</td><td>{row.quantity}</td><td>{currency(row.price, row.currency)}</td><td>{currency(row.commission, row.currency)}</td></tr>)}</tbody>
      </table>
    </div>
  );
}

export function DividendTable({ rows }) {
  const { t } = useI18n();

  return (
    <div className="table-wrap">
      <table>
        <thead><tr><th>{t('ticker')}</th><th>{t('amount')}</th><th>{t('exDate')}</th><th>{t('payment')}</th><th>{t('expected')}</th><th>{t('yield')}</th><th>{t('frequency')}</th></tr></thead>
        <tbody>{rows.map((row) => <tr key={row.id}><td>{row.ticker}<small>{row.held_quantity ? `${row.held_quantity} ${t('quantity').toLowerCase()}` : t('noHoldingOnExDate')}</small></td><td>{currency(row.amount, row.currency)}</td><td>{row.ex_dividend_date}</td><td>{row.payment_date}</td><td>{currency(row.expected_amount, row.currency)}</td><td>{percent(row.dividend_yield)}</td><td>{row.frequency}</td></tr>)}</tbody>
      </table>
    </div>
  );
}

export function AlertsTable({ rows }) {
  const { t } = useI18n();

  return (
    <div className="table-wrap">
      <table>
        <thead><tr><th>{t('ticker')}</th><th>{t('type')}</th><th>{t('target')}</th><th>{t('status')}</th></tr></thead>
        <tbody>{rows.map((row) => <tr key={row.id}><td>{row.ticker}</td><td>{row.type}</td><td>{row.target_value}</td><td>{row.is_active ? t('active') : t('triggered')}</td></tr>)}</tbody>
      </table>
    </div>
  );
}
