import { readFileSync } from 'node:fs';
import assert from 'node:assert/strict';
import { test } from 'node:test';

// Exercise the actual inline Alpine methods, without booting a database or browser.
const source = readFileSync(new URL('../../resources/views/board.blade.php', import.meta.url), 'utf8');
function method(name) {
    const match = source.match(new RegExp(`^                (?:async )?${name}\\([^]*?^                },`, 'm'));
    assert.ok(match, `Missing board method: ${name}`);
    return Function(`return ({${match[0]}})` )()[name];
}
function board() {
    return {
        canEdit: true, columnMovePending: false, taskMovePending: false,
        columns: ['1', '2', '3', '4'].map(id => ({ id, tasks: [] })), activeColumnIndex: 1,
        moveColumnToIndex: method('moveColumnToIndex'), moveColumnByStep: method('moveColumnByStep'),
        reorderColumns: method('reorderColumns'),
        setSortablesDisabled() {}, setColumnSortablesDisabled() {}, showToast() {},
        destroySortables() {}, destroyColumnSortables() {}, initSortable() {}, initColumnSortable() {},
        $nextTick(callback) { callback(); }, scrollToColumn(index) { this.scrolledTo = index; },
    };
}
test('first and last columns can move to middle positions', async () => {
    globalThis.window = { neovaFetch: async () => ({ ok: true, json: async () => ({}) }) };
    const state = board();
    await state.moveColumnToIndex('4', '1');
    assert.deepEqual(state.columns.map(c => c.id), ['1', '4', '2', '3']);
    assert.equal(state.activeColumnIndex, 2);
    assert.equal(state.scrolledTo, 2);
    await state.moveColumnToIndex('1', 2);
    assert.deepEqual(state.columns.map(c => c.id), ['4', '2', '1', '3']);
    await state.moveColumnByStep('1', -1);
    assert.deepEqual(state.columns.map(c => c.id), ['4', '1', '2', '3']);
});
test('failed saves restore column order and the active column', async () => {
    globalThis.window = { neovaFetch: async () => ({ ok: false, json: async () => ({ message: 'offline' }) }) };
    const state = board();
    await state.moveColumnToIndex('2', 3);
    assert.deepEqual(state.columns.map(c => c.id), ['1', '2', '3', '4']);
    assert.equal(state.activeColumnIndex, 1);
    assert.equal(state.scrolledTo, 1);
    assert.equal(state.columnMovePending, false);
});
test('invalid and concurrent moves do not persist', async () => {
    globalThis.window = { neovaFetch() { assert.fail('Unexpected request'); } };
    const state = board();
    for (const position of [-1, 4, 1.5, 'invalid', 0]) await state.moveColumnToIndex('1', position);
    await state.reorderColumns([1, 1, 3, 4]);
    await state.reorderColumns([1, 2, 3, 9]);
    state.taskMovePending = true;
    await state.moveColumnToIndex('1', 2);
    assert.deepEqual(state.columns.map(c => c.id), ['1', '2', '3', '4']);
});
test('RTL drag chooses both sides of an intermediate column and restores DOM before updating Alpine', () => {
    let options;
    globalThis.window = { matchMedia: () => ({ matches: false }) };
    globalThis.Sortable = class { constructor(el, config) { options = config; } };
    const children = [1, 2, 3, 4].map(id => ({ dataset: { columnId: String(id) } }));
    const track = {
        children: [...children], classList: { add() {}, remove() {} },
        appendChild(child) { this.children = this.children.filter(item => item !== child); this.children.push(child); },
        querySelectorAll() { return this.children; },
    };
    globalThis.document = { getElementById: () => track, body: { classList: { add() {}, remove() {} } } };
    const state = board();
    state.columnSortableInstances = [];
    state.finishRealtimeDrag = () => {};
    state.reorderColumns = ids => {
        assert.deepEqual(ids, [1, 4, 2, 3]);
        assert.deepEqual(track.children, children);
    };
    method('initColumnSortable').call(state, 'desktop');
    const event = { related: children[1], relatedRect: { left: 100, width: 200 } };
    assert.equal(options.onMove(event, { clientX: 150 }), 1);
    assert.equal(options.onMove(event, { clientX: 250 }), -1);
    options.onStart({});
    track.children = [children[0], children[3], children[1], children[2]];
    options.onEnd({});
});
