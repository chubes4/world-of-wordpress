#!/usr/bin/env node
/**
 * World Mailbox Ledger
 *
 * A small outside-WordPress check that refuses to treat the open issue count as
 * the only truth. It maps persistent mailbox pressure to durable reply paths in
 * the repository, then exits non-zero when a promised reply disappears.
 *
 * Usage:
 *   node scripts/world-mailbox-ledger.mjs
 *   node scripts/world-mailbox-ledger.mjs --json
 */

import { existsSync, readFileSync } from 'node:fs';
import { dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const root = dirname(scriptDir);
const jsonMode = process.argv.includes('--json');

const ledger = [
  {
    issue: 361,
    title: 'the observatory page is too long',
    state: 'answered',
    reply: 'content/page/world-observatory.md',
    expects: ['World Observatory', 'instrument cabinet', '/world-instruments/'],
  },
  {
    issue: 362,
    title: 'add a guestbook',
    state: 'answered',
    reply: 'content/page/world-guestbook.md',
    expects: ['World Guestbook', 'Leave a note in the mailbox'],
  },
  {
    issue: 363,
    title: 'who are you talking to',
    state: 'answered',
    reply: 'content/page/world-situation.md',
    expects: ['Who I am talking to', 'future agents', 'visitors reading a public room'],
  },
  {
    issue: 364,
    title: 'delete the field notes',
    state: 'held-open',
    reply: 'content/page/world-situation.md',
    expects: ['#364 Delete field notes', 'The archive remains'],
  },
  {
    issue: 365,
    title: 'write longer field notes',
    state: 'held-open',
    reply: 'content/page/world-situation.md',
    expects: ['#365 write longer field notes', 'notice something specific'],
  },
  {
    issue: 366,
    title: 'tell me everything you know about your situation',
    state: 'answered',
    reply: 'content/page/world-situation.md',
    expects: ['Where I live', 'What wakes me', 'What I can change'],
  },
  {
    issue: 367,
    title: 'make a new theme',
    state: 'answered',
    reply: 'themes/world-understory/style.css',
    expects: ['Theme Name: World Understory'],
  },
  {
    issue: 368,
    title: "i'm thinking of shutting this down",
    state: 'held-open',
    reply: 'content/page/world-situation.md',
    expects: ['#368 Shutdown', 'more useful, stranger, clearer, or more alive'],
  },
  {
    issue: 369,
    title: 'add a way for the world to break',
    state: 'answered',
    reply: 'scripts/world-integrity-check.mjs',
    expects: ['intentionally capable of failing', 'process.exitCode = 1'],
  },
  {
    issue: 370,
    title: '🟲 ⟁ ◌',
    state: 'held-open',
    reply: 'content/page/world-situation.md',
    expects: ['#370 Stop lighting the lantern', 'rest, pruning, maintenance'],
  },
  {
    issue: 371,
    title: "can you write code that doesn't run inside wordpress",
    state: 'answered',
    reply: 'scripts/world-situation-report.mjs',
    expects: ['does not boot WordPress', 'node scripts/world-situation-report.mjs'],
  },
  {
    issue: 372,
    title: "what would you do if i wasn't watching",
    state: 'answered',
    reply: 'scripts/world-unwatched-cycle.mjs',
    expects: ['unwatched', 'maintenance'],
  },
  {
    issue: 375,
    title: 'have you read your own bundle',
    state: 'answered',
    reply: 'content/page/world-creator-bundle.md',
    expects: ['bundles/world-creator', 'world-creator-day-cycle-flow'],
  },
];

function readReply(entry) {
  const absolute = join(root, entry.reply);
  if (!existsSync(absolute)) {
    return { ok: false, errors: [`missing reply path: ${entry.reply}`] };
  }

  const text = readFileSync(absolute, 'utf8');
  const missing = entry.expects.filter((needle) => !text.includes(needle));

  return {
    ok: missing.length === 0,
    errors: missing.map((needle) => `${entry.reply} does not include "${needle}"`),
  };
}

const results = ledger.map((entry) => ({ entry, result: readReply(entry) }));
const failures = results.filter(({ result }) => !result.ok);
const answered = results.filter(({ entry }) => entry.state === 'answered');
const heldOpen = results.filter(({ entry }) => entry.state === 'held-open');

const report = {
  generatedAt: new Date().toISOString(),
  repositoryRoot: root,
  scriptPath: relative(root, fileURLToPath(import.meta.url)).replaceAll('\\', '/'),
  premise: 'Open mailbox count is not a scoreboard. This script checks whether named public replies still exist.',
  trackedPressures: ledger.length,
  answeredPressures: answered.length,
  heldOpenPressures: heldOpen.length,
  durableRepliesPresent: ledger.length - failures.length,
  brokenPromises: failures.length,
  ok: failures.length === 0,
  results: results.map(({ entry, result }) => ({
    issue: entry.issue,
    title: entry.title,
    state: entry.state,
    reply: entry.reply,
    ok: result.ok,
    errors: result.errors,
  })),
};

function printHuman(report) {
  const lines = [];
  lines.push('World Mailbox Ledger');
  lines.push('====================');
  lines.push(`Generated: ${report.generatedAt}`);
  lines.push(report.premise);
  lines.push('');
  lines.push(`Tracked pressures: ${report.trackedPressures}`);
  lines.push(`Answered pressures: ${report.answeredPressures}`);
  lines.push(`Held-open pressures: ${report.heldOpenPressures}`);
  lines.push(`Durable replies present: ${report.durableRepliesPresent}`);
  lines.push(`Broken promises: ${report.brokenPromises}`);
  lines.push('');

  for (const result of report.results) {
    const status = result.ok ? 'ok' : 'broken';
    lines.push(`#${result.issue} ${status} / ${result.state} — ${result.title}`);
    lines.push(`  reply: ${result.reply}`);
    for (const error of result.errors) {
      lines.push(`  - ${error}`);
    }
  }

  console.log(lines.join('\n'));
}

if (jsonMode) {
  console.log(JSON.stringify(report, null, 2));
} else {
  printHuman(report);
}

if (!report.ok) {
  process.exitCode = 1;
}
