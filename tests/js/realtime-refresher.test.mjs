import assert from 'node:assert/strict';
import { test } from 'node:test';

globalThis.window = { dispatchEvent() {} };
await import('../../resources/js/realtime.js');
const create = window.createRealtimeRefresher;
const mutationFetch = window.neovaFetch;

test('mutation fetch includes socket ID and exposes canonical snapshot without consuming response', async () => {
    let event, request;
    window.Echo = { socketId: () => 'socket.1' };
    window.dispatchEvent = value => { event = value; };
    window.fetch = async (_, init) => { request = init; return new Response(JSON.stringify({ board: { columns: [] } }), { headers: { 'content-type': 'application/json' } }); };
    const response = await mutationFetch('/write', { method: 'POST' });
    assert.equal(request.headers.get('X-Socket-ID'), 'socket.1');
    assert.equal(event.type, 'neova:mutation-snapshot');
    assert.deepEqual(await response.json(), { board: { columns: [] } });
});
test('expired access preserves page and reports recoverable status without reload', async () => {
    let status;
    window.neovaFetch = async () => new Response('', { status: 403 });
    window.location = { reload() { assert.fail('Must preserve drafts'); } };
    const refresher = create({ url: '/snapshot', apply() { assert.fail('Forbidden payload'); }, onState: value => { status = value; } });
    assert.equal(await refresher.refreshNow(), false);
    assert.deepEqual(status, { state: 'access-error', status: 403 });
    refresher.dispose();
});
test('hung snapshots time out and retry can recover', async () => {
    const states = [];
    window.neovaFetch = (_, { signal }) => new Promise((_, reject) => signal.addEventListener('abort', () => reject(new Error('timeout'))));
    const refresher = create({ url: '/snapshot', timeoutMs: 10, apply() {}, onState: value => states.push(value.state) });
    assert.equal(await refresher.refreshNow(), false);
    window.neovaFetch = async () => new Response('{}', { headers: { 'content-type': 'application/json' } });
    assert.equal(await refresher.refreshNow(), true);
    assert.deepEqual(states, ['stale', 'current']);
    refresher.dispose();
});
test('bursts serialize snapshots and perform a final reconciliation', async () => {
    let release, calls = 0, applied = 0;
    window.neovaFetch = async () => {
        calls++;
        if (calls === 1) await new Promise(resolve => { release = resolve; });
        return new Response('{}');
    };
    const refresher = create({ url: '/snapshot', apply() { applied++; } });
    const first = refresher.refreshNow();
    const second = refresher.refreshNow();
    assert.equal(calls, 1);
    release(); await Promise.all([first, second]);
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(calls, 2);
    assert.equal(applied, 2);
    refresher.dispose();
});
