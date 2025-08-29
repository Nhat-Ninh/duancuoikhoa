(function() {
  const ORIG_FETCH = window.fetch.bind(window);

  // Promise báo "guard đã check xong"
  window.__authReady = new Promise(res => window.__authReadyResolve = res);
  window.__isAuthenticated = false;

  // ALWAYS map endpoints cũ -> user endpoints
  window.fetch = (input, init) => {
    let url = (typeof input === 'string') ? input : input.url;
    init = init || {};
    init.credentials = 'include';

    const map = {
      '/api/get_metrics.php':     '/api/user/metrics/list.php',
      '/api/add_metric.php':      '/api/user/metrics/add.php',
      '/api/get_settings.php':    '/api/user/settings/get.php',
      '/api/update_settings.php': '/api/user/settings/update.php'
    };
    if (typeof url === 'string') {
      if (url in map) url = map[url];
      if (url.startsWith('/api/delete_metric.php')) {
        url = url.replace('/api/delete_metric.php','/api/user/metrics/delete.php');
      }
    }
    return ORIG_FETCH(url, init);
  };

  // Kiểm tra đăng nhập rồi resolve __authReady
  const isAuthPage = location.pathname.includes('/auth/');
  ORIG_FETCH('/api/auth/me.php', { credentials: 'include' })
    .then(r => r.json())
    .then(me => {
      window.__isAuthenticated = !!me.authenticated;
      window.__authReadyResolve && window.__authReadyResolve();
      if (!window.__isAuthenticated && !isAuthPage) {
        const next = encodeURIComponent(location.pathname + location.search);
        location.href = `/auth/login.html?next=${next}`;
      }
    })
    .catch(() => {
      window.__isAuthenticated = false;
      window.__authReadyResolve && window.__authReadyResolve();
      if (!isAuthPage) {
        const next = encodeURIComponent(location.pathname + location.search);
        location.href = `/auth/login.html?next=${next}`;
      }
    });
})();
