import { readFileSync } from 'node:fs';
import assert from 'node:assert/strict';
import { test } from 'node:test';

const source = readFileSync(new URL('../../resources/views/board.blade.php', import.meta.url), 'utf8');
function method(name) {
    const match = source.match(new RegExp(`^                (?:async )?${name}\\([^]*?^                },`, 'm'));
    assert.ok(match, name);
    return Function(`return ({${match[0]}})`)()[name];
}
function snapshot(version = 1, name = 'Project', generatedAt = '2026-09-30T10:00:00.000000Z') {
    return {
        columns: [{ id: '1', tasks: [{ dbId: 7, title: 'Server title', version, updatedAt: 'same-second' }] }],
        members: [], workspacePeople: [], activeCycle: null, generatedAt,
        project: { name, key: 'UX', description: '', boardStyle: 'simple', version: 1 },
    };
}
function board() {
    const state = {
        columns: snapshot().columns, selectedTaskIds: [], activeColumnIndex: 0,
        showModal: true, editingTask: 7, modalSnapshot: 'original', formFingerprint: () => 'draft',
        form: { title: 'My draft', version: 1, updatedAt: 'same-second' },
        pendingDescriptionFiles: [], pendingCommentFiles: [],
        projectState: {}, projectForm: { name: 'Project', key: 'UX', description: '', board_style: 'simple' },
        projectBaseline: '', lastSnapshotAt: '',
        destroySortables() {}, destroyColumnSortables() {}, $nextTick() {},
        openEditModal(task) { this.form = { ...task }; }, closeModal() { this.showModal = false; },
    };
    for (const name of ['boardMutationBusy', 'projectSettingsDirty', 'applyRealtimeSnapshot', 'queueMutationSnapshot', 'flushMutationSnapshot']) state[name] = method(name);
    state.projectBaseline = JSON.stringify(state.projectForm);
    return state;
}

test('unrelated remote updates preserve drafts without a conflict banner', () => {
    const state = board();
    state.applyRealtimeSnapshot(snapshot());
    assert.equal(state.realtimeConflict, undefined);
    assert.equal(state.form.title, 'My draft');
});
test('live task changes retain the draft without a conflict prompt', () => {
    const state = board();
    state.applyRealtimeSnapshot(snapshot(2));
    assert.equal(state.realtimeConflict, undefined);
    assert.equal(state.form.version, 2);
    assert.equal(state.form.title, 'My draft');
});
test('own comment update advances the edit baseline without discarding task draft', () => {
    const state = board();
    state.applyRealtimeSnapshot({ ...snapshot(2), originTaskIds: [7] }, { ownMutation: true });
    assert.equal(state.form.version, 2);
    assert.equal(state.form.title, 'My draft');
    assert.equal(state.realtimeConflict, undefined);
});
test('another task update preserves the draft when the edited task also changes', () => {
    const state = board();
    state.applyRealtimeSnapshot({ ...snapshot(2), originTaskIds: [8] }, { ownMutation: true });
    assert.equal(state.form.version, 2);
    assert.equal(state.realtimeConflict, undefined);
});
test('live project rename preserves dirty settings while updating displayed identity', () => {
    const state = board();
    state.projectForm.name = 'Unsaved name';
    state.applyRealtimeSnapshot(snapshot(1, 'Remote name'));
    assert.equal(state.projectForm.name, 'Unsaved name');
    assert.equal(state.projectState.name, 'Remote name');
});
test('discussion is updated alongside a preserved task draft without a false conflict', () => {
    const state = board();
    const data = snapshot(); data.columns[0].tasks[0].comments = [{ text: 'Remote comment' }];
    state.applyRealtimeSnapshot(data);
    assert.equal(state.form.comments[0].text, 'Remote comment');
    assert.equal(state.form.title, 'My draft');
    assert.equal(state.realtimeConflict, undefined);
});
test('changes to cycle or tags do not make an unchanged settings baseline stale', () => {
    const state = board();
    state.projectForm.name = 'Unsaved name';
    const data = snapshot(); data.project.version = 3;
    state.applyRealtimeSnapshot(data);
    assert.equal(state.projectBaselineVersion, 3);
    assert.equal(state.projectForm.name, 'Unsaved name');
});
test('older snapshots cannot replace newer task state', () => {
    const state = board();
    state.applyRealtimeSnapshot(snapshot(3, 'New', '2026-09-30T10:00:02.000000Z'));
    state.applyRealtimeSnapshot(snapshot(2, 'Old', '2026-09-30T10:00:01.000000Z'));
    assert.equal(state.columns[0].tasks[0].version, 3);
    assert.equal(state.projectState.name, 'New');
});
test('own mutation snapshot waits for local save to finish and then reconciles', async () => {
    const state = board();
    state.taskSaving = true;
    state.queueMutationSnapshot({ ...snapshot(2), originTaskIds: [7] });
    await new Promise(resolve => setTimeout(resolve, 5));
    assert.equal(state.form.version, 1);
    state.taskSaving = false;
    await new Promise(resolve => setTimeout(resolve, 40));
    assert.equal(state.form.version, 2);
    state.destroyed = true;
    clearTimeout(state.mutationTimer);
});
test('delayed older mutation responses cannot replace a newer queued response', async () => {
    const state = board();
    state.taskSaving = true;
    state.queueMutationSnapshot({ ...snapshot(3, 'New', '2026-09-30T10:00:03.000000Z'), originTaskIds: [7] });
    state.queueMutationSnapshot({ ...snapshot(2, 'Old', '2026-09-30T10:00:02.000000Z'), originTaskIds: [7] });
    state.taskSaving = false;
    await new Promise(resolve => setTimeout(resolve, 10));
    assert.equal(state.columns[0].tasks[0].version, 3);
    assert.equal(state.projectState.name, 'New');
    clearTimeout(state.mutationTimer);
});
test('deleting the edited task preserves the draft and queued files', () => {
    const state = board();
    state.formFingerprint = () => 'original';
    state.pendingDescriptionFiles = [{ name: 'draft.txt' }];
    const data = snapshot(); data.columns[0].tasks = [];
    state.applyRealtimeSnapshot(data);
    assert.equal(state.realtimeTaskDeleted, undefined);
    assert.equal(state.pendingDescriptionFiles.length, 1);
    assert.equal(state.showModal, true);
});

test('the next edit uses the acknowledged version before the queue timer runs', () => {
    const state = board();
    state.queueMutationSnapshot({ ...snapshot(2), originTaskIds: [7] });
    state.flushMutationSnapshot();
    assert.equal(state.form.version, 2);
    assert.equal(state.form.title, 'My draft');
    assert.equal(state.realtimeConflict, undefined);
    assert.equal(state.mutationSnapshot, null);
});

test('a delayed read cannot revert a queued move or flag our own write as a conflict', () => {
    const state = board();
    const moved = snapshot(2, 'Project', '2026-09-30T10:00:02.000000Z');
    moved.columns[0].id = '2';
    state.queueMutationSnapshot({ ...moved, originTaskIds: [7] });
    state.applyRealtimeSnapshot(snapshot());
    assert.equal(state.columns[0].id, '2');
    assert.equal(state.form.version, 2);
    assert.equal(state.realtimeConflict, undefined);
});

test('queued writes to different tasks retain the edited task acknowledgement', () => {
    const state = board();
    state.queueMutationSnapshot({ ...snapshot(2), originTaskIds: [7] });
    state.queueMutationSnapshot({ ...snapshot(2, 'Project', '2026-09-30T10:00:01.000000Z'), originTaskIds: [8] });
    state.flushMutationSnapshot();
    assert.equal(state.form.version, 2);
    assert.equal(state.realtimeConflict, undefined);
});

test('a newer queued response updates the board while retaining the local draft', () => {
    const state = board();
    state.queueMutationSnapshot({ ...snapshot(2), originTaskIds: [7] });
    state.queueMutationSnapshot({ ...snapshot(3, 'Project', '2026-09-30T10:00:01.000000Z'), originTaskIds: [8] });
    state.flushMutationSnapshot();
    assert.equal(state.form.version, 3);
    assert.equal(state.form.title, 'My draft');
    assert.equal(state.realtimeConflict, undefined);
});
test('Persian character variants and spacing match without rendering task HTML', () => {
    const normalize = method('normalizeSearch');
    assert.equal(normalize('كارهاي من'), normalize('کارهای‌من'));
    const highlight = method('highlightText');
    assert.equal(highlight('<img src=x onerror=alert(1)>', ''), '&lt;img src=x onerror=alert(1)&gt;');
    assert.match(highlight('hello <script>', 'hello'), /<mark/);
    assert.ok(!highlight('hello <script>', 'hello').includes('<script>'));
});
test('drag sorting stays disabled under filters after a pending mutation finishes', () => {
    let disabled;
    method('setSortablesDisabled').call({ canEdit: true, activeFilterCount: () => 1, boardSearchQuery: '', sortableInstances: [{ instance: { option(_, value) { disabled = value; } } }] }, false);
    assert.equal(disabled, true);
});

test('archived task stays readable and realtime restore returns it to the board', () => {
    const state = board();
    state.formFingerprint = () => 'original';
    const archived = { ...snapshot(2).columns[0].tasks[0], columnId: '1', archivedAt: '2026-10-06T10:00:00Z' };
    state.applyRealtimeSnapshot({ ...snapshot(2), columns: [{ id: '1', tasks: [] }], archivedTasks: [archived] });
    assert.equal(state.showModal, true);
    assert.equal(state.form.archivedAt, archived.archivedAt);
    assert.equal(state.columns[0].tasks.length, 0);
    assert.equal(state.archivedTasks.length, 1);
    state.applyRealtimeSnapshot({ ...snapshot(3), archivedTasks: [] });
    assert.equal(state.form.archivedAt, undefined);
    assert.equal(state.archivedTasks.length, 0);
    assert.equal(state.columns[0].tasks.length, 1);
});

test('remote archiving disables editing while preserving a dirty local draft', () => {
    const state = board();
    const archived = { ...snapshot(2).columns[0].tasks[0], archivedAt: '2026-10-06T10:00:00Z' };
    state.applyRealtimeSnapshot({ ...snapshot(2), columns: [{ id: '1', tasks: [] }], archivedTasks: [archived] });
    assert.equal(state.form.title, 'My draft');
    assert.equal(state.form.archivedAt, archived.archivedAt);
    assert.equal(state.showModal, true);
});
