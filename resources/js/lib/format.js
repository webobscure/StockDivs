export function currency(value, code = 'USD') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: code,
    maximumFractionDigits: 2,
  }).format(value ?? 0);
}

export function percent(value) {
  return `${Number(value ?? 0).toFixed(2)}%`;
}
