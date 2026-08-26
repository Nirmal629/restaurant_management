export function subscribeRealtime(topics, onChange) {
    if (!topics?.length || typeof fetch === 'undefined') return null;

    const endpoint = window.realtimeVersionsUrl || '/realtime/versions';
    const url = `${endpoint}?topics=${encodeURIComponent(topics.join(','))}`;
    let last = null;
    let timer = null;
    let controller = null;
    let stopped = false;

    const poll = async () => {
        if (stopped) return;
        if (document.visibilityState === 'hidden') {
            schedule(8000);
            return;
        }

        controller?.abort();
        controller = new AbortController();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.versions) {
                schedule(5000);
                return;
            }

            if (last) {
                const changed = Object.keys(data.versions).filter((topic) => data.versions[topic] !== last[topic]);
                if (changed.length) onChange(changed);
            }
            last = data.versions;
            schedule(2500);
        } catch (error) {
            if (error.name !== 'AbortError') schedule(5000);
        }
    };

    const schedule = (delay) => {
        clearTimeout(timer);
        timer = setTimeout(poll, delay);
    };

    poll();
    document.addEventListener('visibilitychange', poll);

    return () => {
        stopped = true;
        clearTimeout(timer);
        controller?.abort();
        document.removeEventListener('visibilitychange', poll);
    };
}
