import { readFileSync } from 'node:fs';
import assert from 'node:assert/strict';
import { test } from 'node:test';

const source = readFileSync(new URL('../../resources/views/board.blade.php', import.meta.url), 'utf8');
function method(name) {
    const match = source.match(new RegExp(`^                ${name}\\([^]*?^                },`, 'm'));
    assert.ok(match, `Missing board method: ${name}`);
    return Function(`return ({${match[0]}})` )()[name];
}

test('date-only deadlines remain due all day, while timed deadlines can expire today', () => {
    const state = { workspaceNowParts: () => ({ date: '2026-09-29', time: '14:30' }), isOverdue: method('isOverdue') };
    assert.equal(state.isOverdue('2026-09-29'), false);
    assert.equal(state.isOverdue('2026-09-29', '14:29'), true);
    assert.equal(state.isOverdue('2026-09-29', '14:30'), false);
    assert.equal(state.isOverdue('2026-09-28'), true);
    assert.equal(state.isOverdue('2026-09-30'), false);
});

test('due filters use the task time and workspace date', () => {
    const state = {
        workspaceNowParts: () => ({ date: '2026-09-29', time: '14:30' }),
        isOverdue: method('isOverdue'), matchesDueFilter: method('matchesDueFilter'), filterByDue: 'next7',
    };
    assert.equal(state.matchesDueFilter({ dueDate: '2026-10-06' }), true);
    assert.equal(state.matchesDueFilter({ dueDate: '2026-10-07' }), false);
    state.filterByDue = 'overdue';
    assert.equal(state.matchesDueFilter({ dueDate: '2026-09-29', dueTime: '14:00' }), true);
    assert.equal(state.matchesDueFilter({ dueDate: '2026-09-29', dueTime: '' }), false);
});

function pickerState(dueTime = '') {
    const state = { canEdit: true, taskSaving: false, form: { dueDate: '2026-10-07', dueTime },
        jalaliDatePicker: { open: true }, timePickerOpen: false, floatingMenuRevision: 0 };
    for (const name of ['openTaskTimePicker', 'closeTaskTimePicker', 'closeJalaliDatePicker', 'updateTaskTime', 'clearJalaliDate']) state[name] = method(name);
    return state;
}

test('opening and dismissing a time picker preserves a date-only deadline', () => {
    const state = pickerState();
    state.openTaskTimePicker();
    assert.equal(state.jalaliDatePicker.open, false);
    assert.equal(state.timePickerOpen, true);
    state.closeTaskTimePicker();
    assert.equal(state.form.dueTime, '');
});

test('changing deadline minutes preserves its existing hour and clearing the date closes both pickers', () => {
    const state = pickerState('14:35');
    state.openTaskTimePicker();
    state.timePickerMinute = '45';
    state.updateTaskTime();
    assert.equal(state.form.dueTime, '14:45');
    state.clearJalaliDate();
    assert.equal(state.form.dueDate, '');
    assert.equal(state.form.dueTime, '');
    assert.equal(state.timePickerOpen, false);
    assert.equal(state.jalaliDatePicker.open, false);
});
