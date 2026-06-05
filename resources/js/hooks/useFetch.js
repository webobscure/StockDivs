import { useEffect, useMemo, useState } from 'react';
import { api } from '../api/client';

export function useFetch(path) {
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [nonce, setNonce] = useState(0);

  useEffect(() => {
    setLoading(true);
    api(path)
      .then((payload) => {
        setData(payload);
        setError(null);
      })
      .catch(setError)
      .finally(() => setLoading(false));
  }, [path, nonce]);

  return useMemo(
    () => ({ data, error, loading, reload: () => setNonce((value) => value + 1) }),
    [data, error, loading],
  );
}
