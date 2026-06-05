import { lazy, Suspense } from 'react';
import {
  Bell,
  Briefcase,
  CalendarDays,
  Eye,
  LayoutDashboard,
  LogOut,
  Search,
  Settings,
  ShieldCheck,
  TrendingUp,
} from 'lucide-react';
import { Link, NavLink, Navigate, Route, Routes, useNavigate } from 'react-router-dom';
import { api } from '../api/client';
import { FullScreenState } from '../components/ui';
import { useI18n } from '../i18n/I18nContext';

const AlertsPage = lazy(() => import('../pages/AlertsPage').then((module) => ({ default: module.AlertsPage })));
const DashboardPage = lazy(() => import('../pages/DashboardPage').then((module) => ({ default: module.DashboardPage })));
const DividendsPage = lazy(() => import('../pages/DividendsPage').then((module) => ({ default: module.DividendsPage })));
const PortfolioDetailPage = lazy(() => import('../pages/PortfolioDetailPage').then((module) => ({ default: module.PortfolioDetailPage })));
const PortfolioPage = lazy(() => import('../pages/PortfolioPage').then((module) => ({ default: module.PortfolioPage })));
const SearchPage = lazy(() => import('../pages/SearchPage').then((module) => ({ default: module.SearchPage })));
const SettingsPage = lazy(() => import('../pages/SettingsPage').then((module) => ({ default: module.SettingsPage })));
const WatchlistPage = lazy(() => import('../pages/WatchlistPage').then((module) => ({ default: module.WatchlistPage })));

const navItems = [
  ['dashboard', '/dashboard', LayoutDashboard],
  ['portfolio', '/portfolio', Briefcase],
  ['search', '/search', Search],
  ['watchlist', '/watchlist', Eye],
  ['dividends', '/dividends', CalendarDays],
  ['alerts', '/alerts', Bell],
  ['settings', '/settings', Settings],
];

export function Shell({ user, setUser }) {
  const navigate = useNavigate();
  const { t } = useI18n();
  const baseCurrency = user?.setting?.base_currency ?? 'USD';
  const logout = async () => {
    await api('/logout', { method: 'POST' }).catch(() => null);
    localStorage.removeItem('stockdivs_token');
    setUser(null);
    navigate('/login');
  };

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <Link className="brand" to="/dashboard">
          <span className="brand-mark"><TrendingUp size={20} /></span>
          <span>StockDivs</span>
        </Link>
        <nav>
          {navItems.map(([key, path, Icon]) => (
            <NavLink key={path} to={path} className={({ isActive }) => `nav-item ${isActive ? 'active' : ''}`}>
              <Icon size={18} />
              <span>{t(`nav.${key}`)}</span>
            </NavLink>
          ))}
        </nav>
        <div className="sidebar-footer">
          <div className="user-chip">
            <ShieldCheck size={18} />
            <span>{user.name}</span>
            <small>{baseCurrency}</small>
          </div>
          <button className="ghost-button" onClick={logout}><LogOut size={16} /> {t('logout')}</button>
        </div>
      </aside>
      <main className="main">
        <Suspense fallback={<FullScreenState title={t('loadingAccount')} />}>
          <Routes>
            <Route index element={<Navigate to="/dashboard" replace />} />
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/portfolio" element={<PortfolioPage />} />
            <Route path="/portfolio/:ticker" element={<PortfolioDetailPage />} />
            <Route path="/search" element={<SearchPage />} />
            <Route path="/watchlist" element={<WatchlistPage />} />
            <Route path="/dividends" element={<DividendsPage />} />
            <Route path="/alerts" element={<AlertsPage />} />
            <Route path="/settings" element={<SettingsPage setUser={setUser} />} />
          </Routes>
        </Suspense>
      </main>
    </div>
  );
}
