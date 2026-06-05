import { Activity } from 'lucide-react';
import { useI18n } from '../i18n/I18nContext';

export function Page({ title, action, children }) {
  const { t } = useI18n();

  return (
    <>
      <header className="topbar">
        <div>
          <h1>{title}</h1>
          <span>{t('appSubtitle')}</span>
        </div>
        {action}
      </header>
      <div className="page-content">{children}</div>
    </>
  );
}

export function Panel({ title, children }) {
  return <section className="panel"><div className="panel-title">{title}</div>{children}</section>;
}

export function Metric({ label, value, trend, positive = true }) {
  return (
    <div className="metric-card">
      <span>{label}</span>
      <strong>{value}</strong>
      {trend && <small className={positive ? 'positive' : 'negative'}>{trend}</small>}
    </div>
  );
}

export function TextInput({ label, value, onChange, type = 'text' }) {
  return (
    <label className="field">
      <span>{label}</span>
      <input type={type} value={value ?? ''} onChange={(event) => onChange(event.target.value)} />
    </label>
  );
}

export function Select({ label, value, onChange, options, labels = {} }) {
  return (
    <label className="field">
      <span>{label}</span>
      <select value={value} onChange={(event) => onChange(event.target.value)}>
        {options.map((option) => <option key={option} value={option}>{labels[option] ?? option}</option>)}
      </select>
    </label>
  );
}

export function FullScreenState({ title }) {
  return <div className="state"><Activity className="spin" size={22} /><strong>{title}</strong></div>;
}

export function EmptyState({ title, text }) {
  return <div className="state"><strong>{title}</strong><span>{text}</span></div>;
}

export function ErrorState({ error, reload }) {
  const { t } = useI18n();

  return (
    <div className="state error-state">
      <strong>{error.message}</strong>
      <button className="secondary-button" onClick={reload}>{t('retry')}</button>
    </div>
  );
}
