export async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    });

    if (res.status === 401) {
        window.location.href = '/login';
        return;
    }

    if (res.redirected) {
        window.location.href = res.url;
        return;
    }

    let data = {};
    try {
        data = await res.json();
    } catch (e) {}

    return { res, data };
}
