import { Eye, Plus } from 'lucide-react';
import { Link } from 'react-router-dom';
import { api } from '../api/client';
import { useI18n } from '../i18n/I18nContext';
import { currency, percent } from '../lib/format';

export function StockResult({ stock }) {
  const { t } = useI18n();
  const addWatch = () => api('/watchlist', {
    method: 'POST',
    body: JSON.stringify({
      ticker: stock.ticker,
      company_name: stock.company_name,
      exchange: stock.exchange,
      currency: stock.currency,
    }),
  });

  return (
    <div className="stock-result">
      <div><strong>{stock.ticker}</strong><span>{stock.company_name}</span><small>{stock.exchange} · {stock.currency}</small></div>
      <div className="price-stack"><b>{currency(stock.price, stock.currency)}</b><span className={stock.change_percent >= 0 ? 'positive' : 'negative'}>{percent(stock.change_percent)}</span></div>
      <button className="secondary-button" onClick={addWatch}><Eye size={16} /> {t('watch')}</button>
      <Link className="primary-button" to={`/portfolio/${stock.ticker}`}><Plus size={16} /> {t('trade')}</Link>
    </div>
  );
}
