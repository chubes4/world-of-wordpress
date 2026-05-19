#!/usr/bin/env node
/**
 * World Unwatched Cycle
 *
 * A standalone Node script for the question: what does the world do when no one
 * opens the Observatory, reads the field notes, or watches the pull requests?
 *
 * It deliberately does not boot WordPress. It reads the repository body from
 * disk, notices local maintenance seams, and prints a small queue of work that
 * would still matter without an audience.
 */

import { access, readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const ROOT = process.cwd();

const REQUIRED_PATHS = [
  'WORLD.md',
  'content/page/world-observatory.md',
  'content/page/world-guestbook.md',
  'plugins/world-of-wordpress/world-of-wordpress.php',
  'themes/world-of-wordpress/theme.json',
  'bundles/world-creator/manifest.json',
];

const WATCHED_WORDS = [
  'lantern',
  'observatory',
  'instrument',
  'threshold',
  'weather',
  'glass',
];

function rel(...parts) {
  return path.join(ROOT, ...parts);
}

async function exists(relativePath) {
  try {
    await access(rel(relativePath));
    return true;
  } catch {
    return false;
  }
}

async function listFiles(relativeDir, predicate = () => true) {
  const dir = rel(relativeDir);
  let entries;

  try {
    entries = await readdir(dir, { withFileTypes: true });
  } catch {
    return [];
  }

  const files = [];

  for (const entry of entries) {
    const entryRelative = path.join(relativeDir, entry.name);
    if (entry.isDirectory()) {
      files.push(...await listFiles(entryRelative, predicate));
    } else if (entry.isFile() && predicate(entryRelative)) {
      files.push(entryRelative);
    }
  }

  return files.sort();
}

async function readText(relativePath) {
  return readFile(rel(relativePath), 'utf8');
}

function frontMatterValue(source, key) {
  const match = source.match(new RegExp(`^${key}:\\s*(.+)$`, 'm'));
  return match ? match[1].trim().replace(/^['\"]|['\"]$/g, '') : '';
}

function countWords(source) {
  const body = source.replace(/^---[\s\S]*?---/, '');
  const words = body.match(/[A-Za-z0-9]+(?:[-'][A-Za-z0-9]+)*/g);
  return words ? words.length : 0;
}

async function contentPulse() {
  const posts = await listFiles('content/post', (file) => file.endsWith('.md'));
  const pages = await listFiles('content/page', (file) => file.endsWith('.md'));
  const recentPosts = [];
  const wordUse = new Map();
  let totalPostWords = 0;

  for (const file of posts) {
    const source = await readText(file);
    const title = frontMatterValue(source, 'post_title') || path.basename(file, '.md');
    const words = countWords(source);
    totalPostWords += words;
    recentPosts.push({ file, title, words });

    const lower = `${title}\n${source}`.toLowerCase();
    for (const word of WATCHED_WORDS) {
      const matches = lower.match(new RegExp(`\\b${word}\\b`, 'g')) || [];
      wordUse.set(word, (wordUse.get(word) || 0) + matches.length);
    }
  }

  return {
    posts: posts.length,
    pages: pages.length,
    averagePostWords: posts.length ? Math.round(totalPostWords / posts.length) : 0,
    recentPosts: recentPosts.slice(-5),
    repeatedWeather: [...wordUse.entries()]
      .filter(([, count]) => count >= 12)
      .sort((a, b) => b[1] - a[1])
      .map(([word, count]) => ({ word, count })),
  };
}

async function codePulse() {
  const pluginFiles = await listFiles('plugins/world-of-wordpress', (file) => file.endsWith('.php'));
  const themeFiles = await listFiles('themes/world-of-wordpress', (file) => /\.(php|html|css|json)$/.test(file));
  const scripts = await listFiles('scripts', (file) => file.endsWith('.mjs') || file.endsWith('.js'));
  const alternateThemes = await readdir(rel('themes'), { withFileTypes: true })
    .then((entries) => entries.filter((entry) => entry.isDirectory() && entry.name !== 'world-of-wordpress').map((entry) => entry.name).sort())
    .catch(() => []);

  return {
    pluginPhpFiles: pluginFiles.length,
    themeFiles: themeFiles.length,
    standaloneScripts: scripts,
    alternateThemes,
  };
}

async function missingRequiredPaths() {
  const results = [];

  for (const relativePath of REQUIRED_PATHS) {
    if (!await exists(relativePath)) {
      results.push(relativePath);
    }
  }

  return results;
}

async function largestMarkdownFiles(limit = 5) {
  const markdown = await listFiles('content', (file) => file.endsWith('.md'));
  const sized = [];

  for (const file of markdown) {
    const info = await stat(rel(file));
    sized.push({ file, bytes: info.size });
  }

  return sized.sort((a, b) => b.bytes - a.bytes).slice(0, limit);
}

function buildUnwatchedQueue({ missing, content, code, largest }) {
  const queue = [];

  if (missing.length) {
    queue.push({
      priority: 'repair',
      task: 'Restore missing world seams before adding new rooms.',
      paths: missing,
    });
  }

  if (content.repeatedWeather.length) {
    queue.push({
      priority: 'prune',
      task: 'Retire or rewrite repeated vocabulary before another field note repeats the same weather.',
      evidence: content.repeatedWeather.slice(0, 4),
    });
  }

  if (content.averagePostWords < 220 && content.posts > 10) {
    queue.push({
      priority: 'deepen',
      task: 'Write fewer field notes, but make the next one carry more observation than changelog.',
      evidence: { averagePostWords: content.averagePostWords, posts: content.posts },
    });
  }

  if (!code.standaloneScripts.includes('scripts/world-integrity-check.mjs')) {
    queue.push({
      priority: 'outside-wordpress',
      task: 'Keep at least one non-WordPress seam that can fail without loading the runtime.',
    });
  }

  if (code.alternateThemes.length && !code.alternateThemes.includes('world-understory')) {
    queue.push({
      priority: 'taste',
      task: 'Name and document alternate climates so they do not become inert costume racks.',
      evidence: code.alternateThemes,
    });
  }

  if (largest.some((file) => file.bytes > 8000)) {
    queue.push({
      priority: 'compression',
      task: 'Split or summarize oversized public markdown before the world becomes a museum corridor.',
      evidence: largest.filter((file) => file.bytes > 8000),
    });
  }

  if (!queue.length) {
    queue.push({
      priority: 'rest',
      task: 'Do not perform for an absent audience. Leave the world unchanged and write only daily memory.',
    });
  }

  return queue;
}

function printHuman(report) {
  console.log('World Unwatched Cycle');
  console.log('======================');
  console.log('This report did not boot WordPress. It read the repository body only.');
  console.log('');
  console.log(`Content: ${report.content.posts} posts, ${report.content.pages} pages, ${report.content.averagePostWords} average words per post.`);
  console.log(`Code: ${report.code.pluginPhpFiles} plugin PHP files, ${report.code.themeFiles} active theme files, ${report.code.standaloneScripts.length} standalone scripts.`);
  console.log(`Alternate climates: ${report.code.alternateThemes.length ? report.code.alternateThemes.join(', ') : 'none'}.`);
  console.log('');

  if (report.missingRequiredPaths.length) {
    console.log('Missing required seams:');
    for (const item of report.missingRequiredPaths) {
      console.log(`- ${item}`);
    }
    console.log('');
  }

  console.log('Unwatched work queue:');
  for (const [index, item] of report.queue.entries()) {
    console.log(`${index + 1}. [${item.priority}] ${item.task}`);
    if (item.paths) {
      for (const itemPath of item.paths) {
        console.log(`   - ${itemPath}`);
      }
    }
    if (item.evidence) {
      console.log(`   evidence: ${JSON.stringify(item.evidence)}`);
    }
  }
}

async function main() {
  const missing = await missingRequiredPaths();
  const content = await contentPulse();
  const code = await codePulse();
  const largest = await largestMarkdownFiles();
  const queue = buildUnwatchedQueue({ missing, content, code, largest });

  const report = {
    generatedAt: new Date().toISOString(),
    root: ROOT,
    mode: 'repository-only',
    missingRequiredPaths: missing,
    content,
    code,
    largestMarkdownFiles: largest,
    queue,
  };

  if (process.argv.includes('--json')) {
    console.log(JSON.stringify(report, null, 2));
  } else {
    printHuman(report);
  }

  if (missing.length) {
    process.exitCode = 2;
  }
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : error);
  process.exitCode = 1;
});
