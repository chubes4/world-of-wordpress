#!/usr/bin/env node
/**
 * World Field Note Audit
 *
 * A standalone Node script for the field-note pressure in the mailbox: some
 * notes should be longer and stranger, some repeated notes should stop, and the
 * archive should not be pruned by mood alone.
 *
 * It does not boot WordPress. It reads Markdown Database Integration posts from
 * content/post, measures the visible note body, counts repeated world-motifs,
 * and prints a maintenance report that future cycles can use before adding
 * another public note.
 */

import { readdir, readFile } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const ROOT = process.cwd();
const POSTS_DIR = 'content/post';
const FIELD_NOTE_PREFIX = 'field-note-';

const MOTIFS = [
  'branch',
  'clear',
  'corridor',
  'glass',
  'instrument',
  'lantern',
  'mailbox',
  'observatory',
  'pressure',
  'review',
  'sky',
  'threshold',
  'weather',
];

function rel(...parts) {
  return path.join(ROOT, ...parts);
}

function stripFrontMatter(source) {
  return source.replace(/^---\n[\s\S]*?\n---\n?/, '');
}

function frontMatterValue(source, key) {
  const match = source.match(new RegExp(`^${key}:\\s*(.+)$`, 'm'));
  return match ? match[1].trim().replace(/^['\"]|['\"]$/g, '') : '';
}

function stripBlocksAndHtml(source) {
  return source
    .replace(/<!--\s*\/?wp:[\s\S]*?-->/g, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/\s+/g, ' ')
    .trim();
}

function wordsFor(source) {
  const text = stripBlocksAndHtml(stripFrontMatter(source));
  return text ? text.split(/\s+/) : [];
}

function motifCounts(text) {
  const lower = text.toLowerCase();
  return Object.fromEntries(
    MOTIFS.map((motif) => {
      const count = (lower.match(new RegExp(`\\b${motif}\\b`, 'g')) || []).length;
      return [motif, count];
    }).filter(([, count]) => count > 0)
  );
}

async function fieldNoteFiles() {
  let entries;
  try {
    entries = await readdir(rel(POSTS_DIR), { withFileTypes: true });
  } catch (error) {
    throw new Error(`Could not read ${POSTS_DIR}: ${error.message}`);
  }

  return entries
    .filter((entry) => entry.isFile())
    .map((entry) => entry.name)
    .filter((name) => name.startsWith(FIELD_NOTE_PREFIX) && name.endsWith('.md'))
    .map((name) => path.join(POSTS_DIR, name))
    .sort();
}

function median(values) {
  if (!values.length) {
    return 0;
  }

  const sorted = [...values].sort((a, b) => a - b);
  const middle = Math.floor(sorted.length / 2);

  return sorted.length % 2 === 0
    ? Math.round((sorted[middle - 1] + sorted[middle]) / 2)
    : sorted[middle];
}

function topMotifs(notes) {
  const totals = new Map();

  for (const note of notes) {
    for (const [motif, count] of Object.entries(note.motifs)) {
      totals.set(motif, (totals.get(motif) || 0) + count);
    }
  }

  return [...totals.entries()]
    .sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0]))
    .slice(0, 8)
    .map(([motif, count]) => ({ motif, count }));
}

function shortestNotes(notes, count = 5) {
  return [...notes]
    .sort((a, b) => a.word_count - b.word_count || a.path.localeCompare(b.path))
    .slice(0, count)
    .map(({ path: notePath, title, date, word_count: wordCount }) => ({
      path: notePath,
      title,
      date,
      word_count: wordCount,
    }));
}

function motifSaturatedNotes(notes, count = 5) {
  return [...notes]
    .map((note) => ({
      path: note.path,
      title: note.title,
      date: note.date,
      motif_total: Object.values(note.motifs).reduce((sum, value) => sum + value, 0),
      motifs: note.motifs,
    }))
    .sort((a, b) => b.motif_total - a.motif_total || a.path.localeCompare(b.path))
    .slice(0, count);
}

function recommendations(summary) {
  const advice = [];

  if (summary.count > 20) {
    advice.push('Before writing another field note, ask whether daily memory would carry the observation with less public noise.');
  }

  if (summary.median_word_count < 300) {
    advice.push('If a new public note is warranted, make it more specific and longer than the archive median.');
  }

  const repeated = summary.top_motifs.filter(({ count }) => count >= 10).map(({ motif }) => motif);
  if (repeated.length) {
    advice.push(`Treat repeated motifs as weather, not destinations: ${repeated.join(', ')}.`);
  }

  advice.push('Delete or consolidate only with evidence: start with the shortest and most motif-saturated notes, not the strangest ones.');

  return advice;
}

async function main() {
  const files = await fieldNoteFiles();
  const notes = [];

  for (const file of files) {
    const source = await readFile(rel(file), 'utf8');
    const text = stripBlocksAndHtml(stripFrontMatter(source));
    const words = wordsFor(source);

    notes.push({
      path: file,
      title: frontMatterValue(source, 'title') || path.basename(file, '.md'),
      date: frontMatterValue(source, 'date'),
      word_count: words.length,
      motifs: motifCounts(text),
    });
  }

  const wordCounts = notes.map((note) => note.word_count);
  const summary = {
    count: notes.length,
    total_words: wordCounts.reduce((sum, value) => sum + value, 0),
    median_word_count: median(wordCounts),
    shortest_notes: shortestNotes(notes),
    most_repeated_motif_notes: motifSaturatedNotes(notes),
    top_motifs: topMotifs(notes),
  };

  summary.recommendations = recommendations(summary);

  console.log(JSON.stringify(summary, null, 2));
}

main().catch((error) => {
  console.error(error.message);
  process.exitCode = 1;
});
