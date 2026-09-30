const socketHeaders = (headers = {}) => {
    const result = new Headers(headers)
    const socketId = window.Echo?.socketId?.()
    if (socketId) result.set('X-Socket-ID', socketId)
    return result
}

window.neovaMutationResponse = data => {
    if (data?.board) window.dispatchEvent(new CustomEvent('neova:mutation-snapshot', { detail: data.board }))
    return data
}

window.neovaFetch = async (input, init = {}) => {
    const response = await window.fetch(input, { ...init, signal: init.signal || AbortSignal.timeout(30000), headers: socketHeaders(init.headers) })
    if (response.ok && !['GET', 'HEAD'].includes((init.method || 'GET').toUpperCase())
        && response.headers.get('content-type')?.includes('application/json')) {
        window.neovaMutationResponse(await response.clone().json())
    }
    return response
}

window.createRealtimeRefresher = ({ url, apply, onState = () => {}, timeoutMs = 10000 }) => {
    let timer = null
    let running = null
    let pending = false
    let disposed = false
    let failures = 0
    let controller = null

    const refreshNow = () => {
        if (disposed) return Promise.resolve(false)
        if (running) { pending = true; return running }
        clearTimeout(timer)
        running = (async () => {
          controller = new AbortController()
          const timeout = setTimeout(() => controller.abort(), timeoutMs)
          try {
            const response = await window.neovaFetch(url, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                signal: controller.signal,
            })
            if ([401, 403, 404].includes(response.status)) {
                onState({ state: 'access-error', status: response.status })
                pending = false
                return false
            }
            if (!response.ok) throw new Error(`Snapshot failed (${response.status})`)
            const data = await response.json()
            if (disposed) return false
            await apply(data)
            failures = 0
            onState({ state: 'current' })
            return true
          } catch (error) {
            if (!disposed) {
                failures++
                onState({ state: 'stale' })
                timer = setTimeout(refreshNow, Math.min(30000, 1000 * 2 ** Math.min(failures, 5)))
            }
            return false
          } finally {
            clearTimeout(timeout)
            controller = null
          }
        })().finally(() => {
            running = null
            if (pending && !disposed) { pending = false; refreshNow() }
        })
        return running
    }

    return {
        refreshNow,
        schedule() {
            if (disposed) return
            clearTimeout(timer)
            timer = setTimeout(refreshNow, 100)
        },
        dispose() { disposed = true; clearTimeout(timer); controller?.abort() },
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
