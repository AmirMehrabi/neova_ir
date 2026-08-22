const socketHeaders = (headers = {}) => {
    const result = new Headers(headers)
    const socketId = window.Echo?.socketId?.()
    if (socketId) result.set('X-Socket-ID', socketId)
    return result
}

window.neovaFetch = (input, init = {}) => window.fetch(input, {
    ...init,
    headers: socketHeaders(init.headers),
})

window.createRealtimeRefresher = ({ url, apply }) => {
    let timer = null
    let running = false
    let pending = false

    const refreshNow = async () => {
        if (running) { pending = true; return }
        running = true
        try {
            const response = await window.neovaFetch(url, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            })
            if ([401, 403, 404].includes(response.status)) {
                window.location.reload()
                return
            }
            if (!response.ok) throw new Error(`Snapshot failed (${response.status})`)
            await apply(await response.json())
        } catch (error) {
            console.error('Realtime refresh failed', error)
        } finally {
            running = false
            if (pending) { pending = false; await refreshNow() }
        }
    }

    return {
        refreshNow,
        schedule() {
            clearTimeout(timer)
            timer = setTimeout(refreshNow, 100)
        },
    }
}

window.subscribeProjectRealtime = (projectId, callback) =>
    window.Echo?.private(`project.${projectId}`).listen('.project.changed', callback)

window.subscribeTodayRealtime = (workspaceId, callback) =>
    window.Echo?.private(`workspace.${workspaceId}.today`).listen('.today.changed', callback)

const bindConnectionEvents = () => {
    const connection = window.Echo?.connector?.pusher?.connection
    if (!connection) return
    let wasDisconnected = false
    connection.bind('state_change', ({ current }) => {
        if (['unavailable', 'failed', 'disconnected'].includes(current)) wasDisconnected = true
        window.dispatchEvent(new CustomEvent('neova:realtime-state', { detail: { state: current } }))
        if (current === 'connected' && wasDisconnected) {
            wasDisconnected = false
            window.dispatchEvent(new Event('neova:realtime-reconnected'))
        }
    })
}

bindConnectionEvents()
