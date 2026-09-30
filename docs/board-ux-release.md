# Board and project UX release

Implemented changes:

- Successful project mutations return a canonical board snapshot captured in the write transaction. The originating browser reconciles versions, workflow state, assignees, attachments, and sibling order without relying on its own WebSocket event.
- Integer edit versions detect rapid concurrent edits. Task discussion and sibling position changes do not invalidate editable task fields. Project settings retain a separate draft and version baseline.
- Snapshot reconciliation preserves drafts and focus, ignores older responses, and distinguishes genuine changes/deletion from unrelated updates. Initialization is idempotent and listeners/timers are cleaned up.
- Snapshot requests have timeouts, retry/backoff, reconnection/subscription refresh, and visible-tab recovery. Lost access preserves drafts rather than reloading them away. Upload requests include the socket header.
- Project settings have a visible label, direct links from Projects, accessible drawer tabs and focus handling, reactive project identity, and unsaved-change protection. Permanent deletion keeps typed-name confirmation. Archive/Restore preserves tasks/files; archived projects are read-only and excluded from active navigation/Today.
- Error messages persist and use error styling; activity failures differ from empty history. Task titles are native keyboard-accessible buttons. Board search normalizes Persian characters, digits, and spacing. Empty filter results explain recovery; drag sorting is disabled while filtered to avoid incorrect hidden-task ordering, with explicit column movement still available.

## Deployment

The version-column migration has been applied only to the local SQLite database. Production Reverb was not inspected or changed; it is hosted separately by the user.

Deploy the source with the normal production release process and run the migration before serving the new application code:

```bash
php artisan migrate --force
npm run build
php artisan optimize:clear
php artisan optimize
php artisan reverb:restart
```

Build frontend assets with the **production** `VITE_REVERB_*` settings. The locally generated build uses the local environment and should not replace a correctly configured production build. Reverb restart reloads the long-running process after deployment; its server/proxy configuration does not need to change for these application fixes.

Verify on production with two authorized browser sessions: repeatedly create/edit/move/bulk-update tasks without reloading; confirm the second session converges; add a comment while another session has a draft; disconnect/reconnect; rename/manage/archive/restore a disposable project; and check owner/admin/member/viewer controls. Confirm `/app/...` upgrades successfully and private-channel authentication succeeds if collaboration remains degraded.

The implementation currently returns full snapshots, matching the existing realtime reconciliation model. Large boards may benefit from incremental payloads after correctness and production behavior are verified. Visual browser QA and moderated user testing remain outstanding; the current environment exposes no browser session.

## Automated checks

Run `php artisan test --compact`, `node --test tests/js/*.test.mjs`, `vendor/bin/pint --dirty --test`, and `npm run build`. New suites cover mutation reconciliation, rapid conflicts, preserved drafts, permissions, archive recovery/files, timeout/recovery, and filtered ordering safeguards. Rendered board scripts and Alpine expressions are also checked locally for syntax errors.
