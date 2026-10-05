import { readFileSync } from 'node:fs';
import assert from 'node:assert/strict';
import { test } from 'node:test';

const source = readFileSync(new URL('../../resources/views/board.blade.php', import.meta.url), 'utf8');
function method(name) {
    const match = source.match(new RegExp(`^                (?:async )?${name}\\([^]*?^                },`, 'm'));
    assert.ok(match, name);
    return Function(`return ({${match[0]}})`)()[name];
}
function board(editingTask = null) {
    const state = {
        canEdit: true, taskSaving: false, editingTask, editingCheckItemIndex: null,
        checkItemDraft: '', newCheckItem: '', checklistComposerOpen: false,
        form: { title: 'Review', description: '', priority: 'متوسط', columnId: '1',
            dueDate: '', dueTime: '', assignees: [], tags: [], checklist: [], comments: [] },
        columns: [{ id: '1', tasks: [{ dbId: 7 }] }],
        $nextTick() {}, flushMutationSnapshot() {}, showToast() {},
        async uploadQueuedDescription() { return true; },
        closeModal() { this.closed = true; },
    };
    for (const name of ['addCheckItem', 'startCheckItemEdit', 'finishCheckItemEdit',
        'cancelCheckItemEdit', 'removeCheckItem', 'formFingerprint', 'saveTask']) state[name] = method(name);
    return state;
}

for (const editingTask of [null, 7]) {
    test(`saving ${editingTask ? 'an existing' : 'a new'} task includes the pending checklist item`, async () => {
        const state = board(editingTask);
        state.form.checklist = [{ text: 'Original', done: true }];
        state.editingCheckItemIndex = 0;
        state.checkItemDraft = ' Renamed ';
        state.newCheckItem = ' Pending ';
        let request;
        globalThis.window = { neovaFetch: async (url, options) => {
            request = options;
            return { ok: true, json: async () => ({ ...JSON.parse(options.body), id: 8, display_id: 'UX-8' }) };
        } };
        await state.saveTask();
        assert.equal(request.method, editingTask ? 'PUT' : 'POST');
        assert.deepEqual(JSON.parse(request.body).checklist, [
            { text: 'Renamed', done: true }, { text: 'Pending', done: false },
        ]);
        assert.equal(state.closed, true);
        assert.equal(state.newCheckItem, '');
    });
}

test('retrying a failed save does not duplicate the pending checklist item', async () => {
    const state = board(7);
    state.newCheckItem = 'Pending';
    const payloads = [];
    globalThis.window = { neovaFetch: async (url, options) => {
        payloads.push(JSON.parse(options.body));
        return { ok: payloads.length > 1, json: async () => ({ message: 'Try again' }) };
    } };
    await state.saveTask();
    assert.equal(state.closed, undefined);
    assert.equal(state.taskError, 'Try again');
    await state.saveTask();
    assert.deepEqual(payloads[1].checklist, [{ text: 'Pending', done: false }]);
    assert.equal(state.closed, true);
});

test('pending items and inline edits mark the task as dirty before they are committed', () => {
    const state = board(7);
    state.form.checklist = [{ text: 'Original', done: false }];
    const baseline = state.formFingerprint();
    state.newCheckItem = 'Pending';
    assert.notEqual(state.formFingerprint(), baseline);
    state.newCheckItem = '   ';
    assert.equal(state.formFingerprint(), baseline);
    state.editingCheckItemIndex = 0;
    state.checkItemDraft = 'Changed';
    assert.notEqual(state.formFingerprint(), baseline);
    state.cancelCheckItemEdit();
    assert.equal(state.formFingerprint(), baseline);
});

test('renaming preserves completion; Escape and empty renames preserve the original text', () => {
    const state = board();
    state.form.checklist = [{ text: 'Original', done: true }];
    state.startCheckItemEdit(0);
    state.checkItemDraft = 'Changed';
    state.cancelCheckItemEdit();
    assert.equal(state.form.checklist[0].text, 'Original');
    state.startCheckItemEdit(0);
    state.checkItemDraft = '   ';
    state.finishCheckItemEdit();
    assert.equal(state.form.checklist[0].text, 'Original');
    state.startCheckItemEdit(0);
    state.checkItemDraft = ' Renamed ';
    state.finishCheckItemEdit();
    assert.deepEqual(state.form.checklist[0], { text: 'Renamed', done: true });
});

test('a stale blur cannot finish editing another item', () => {
    const state = board();
    state.form.checklist = [{ text: 'First', done: false }, { text: 'Second', done: false }];
    state.startCheckItemEdit(0);
    state.checkItemDraft = 'Updated first';
    state.startCheckItemEdit(1);
    state.finishCheckItemEdit(0);
    assert.equal(state.editingCheckItemIndex, 1);
    assert.equal(state.checkItemDraft, 'Second');
    assert.equal(state.form.checklist[0].text, 'Updated first');
});

test('empty items and read-only checklist changes are ignored', () => {
    const state = board();
    state.newCheckItem = '   ';
    state.addCheckItem();
    assert.deepEqual(state.form.checklist, []);
    state.canEdit = false;
    state.newCheckItem = 'Pending';
    state.addCheckItem();
    assert.deepEqual(state.form.checklist, []);
});
