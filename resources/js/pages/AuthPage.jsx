import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { TrendingUp } from 'lucide-react';
import { api } from '../api/client';
import { TextInput } from '../components/ui';
import { useI18n } from '../i18n/I18nContext';

export function AuthPage({ mode, setUser }) {
  const { t } = useI18n();
  const navigate = useNavigate();
  const isRegister = mode === 'register';
  const [form, setForm] = useState({ name: '', email: 'demo@stockdivs.test', password: 'password123', password_confirmation: 'password123' });
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const submit = async (event) => {
    event.preventDefault();
    setBusy(true);
    setError('');
    try {
      const payload = await api(isRegister ? '/register' : '/login', { method: 'POST', body: JSON.stringify(form) });
      localStorage.setItem('stockdivs_token', payload.token);
      setUser(payload.user);
      navigate('/dashboard');
    } catch (err) {
      setError(err.message);
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="auth-screen">
      <form className="auth-card" onSubmit={submit}>
        <div className="brand auth-brand"><span className="brand-mark"><TrendingUp size={20} /></span><span>StockDivs</span></div>
        <h1>{isRegister ? t('createAccount') : t('signIn')}</h1>
        <p>{t('authCopy')}</p>
        {isRegister && <TextInput label={t('name')} value={form.name} onChange={(name) => setForm({ ...form, name })} />}
        <TextInput label={t('email')} value={form.email} onChange={(email) => setForm({ ...form, email })} />
        <TextInput label={t('password')} type="password" value={form.password} onChange={(password) => setForm({ ...form, password })} />
        {isRegister && <TextInput label={t('confirmPassword')} type="password" value={form.password_confirmation} onChange={(passwordConfirmation) => setForm({ ...form, password_confirmation: passwordConfirmation })} />}
        {error && <div className="error">{error}</div>}
        <button className="primary-button" disabled={busy}>{busy ? t('working') : isRegister ? t('register') : t('login')}</button>
        <Link to={isRegister ? '/login' : '/register'}>{isRegister ? t('alreadyHaveAccount') : t('needAccount')}</Link>
      </form>
    </div>
  );
}
