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
