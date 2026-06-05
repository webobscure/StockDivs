export function applyTheme(theme = 'light') {
  document.documentElement.dataset.theme = theme;
  localStorage.setItem('stockdivs_theme', theme);
}

export function storedTheme() {
  return localStorage.getItem('stockdivs_theme') ?? 'light';
}
