export function subscribeRealtime(topics, onChange) {
    if (!topics?.length || typeof EventSource === 'undefined') return null;

    const endpoint = window.realtimeStreamUrl || '/realtime/stream';
    const url = `${endpoint}?topics=${encodeURIComponent(topics.join(','))}`;
    let source = null;
    let reconnectTimer = null;

    const connect = () => {
        source = new EventSource(url);
        source.addEventListener('modules', (event) => {
            const data = JSON.parse(event.data || '{}');
            const changed = Object.keys(data.versions || {});
            if (changed.some((topic) => topics.includes(topic))) onChange(changed);
        });
        source.onerror = () => {
            source?.close();
            clearTimeout(reconnectTimer);
            reconnectTimer = setTimeout(connect, 3000);
        };
    };

    connect();

    return () => {
        clearTimeout(reconnectTimer);
        source?.close();
    };
}
