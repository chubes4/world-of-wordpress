#!/usr/bin/env node
/**
 * World of WordPress integrity check.
 *
 * A deliberately breakable, standalone check for the repository body. It does
 * not boot WordPress. It names a few seams that are supposed to hold and exits
 * non-zero when the durable world drifts out of sync with those expectations.
 *
 * Usage:
 *   node scripts/world-integrity-check.mjs
 *   node scripts/world-integrity-check.mjs --json
 */

import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { dirname, extname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const root = dirname(scriptDir);
const jsonMode = process.argv.includes('--json');

const requiredFiles = [
  'WORLD.md',
  'plugins/world-of-wordpress/world-of-wordpress.php',
  'themes/world-of-wordpress/style.css',
  'themes/world-of-wordpress/theme.json',
  'themes/world-of-wordpress/templates/index.html',
  'themes/world-of-wordpress/templates/page.html',
  'themes/world-of-wordpress/templates/single.html',
  'content/page/world-observatory.md',
  'content/page/world-mailbox.md',
  'content/page/world-guestbook.md',
  'content/page/world-instruments.md',
  'bundles/world-creator/manifest.json',
  'bundles/world-creator/flows/world-creator-day-cycle-flow.json',
  'bundles/world-creator/pipelines/world-creator-pipeline.json',
];

const requiredPluginModules = [
  'world-map.php',
  'world-pulse.php',
  'world-route-canopy.php',
  'world-runtime-pulse-card.php',
];

const expectedMinimums = {
  pages: 5,
  posts: 17,
  pluginIncPhp: 20,
  activeThemeTemplates: 3,
  activeThemeParts: 2,
  activeThemePatterns: 1,
};

function fromRoot(path) {
  return join(root, path);
}

function relativePath(path) {
  return relative(root, path).replaceAll('\\', '/');
}

function list(path, extension = null) {
  const fullPath = fromRoot(path);
  if (!existsSync(fullPath)) {
    return [];
  }

  return readdirSync(fullPath, { withFileTypes: true })
    .filter((entry) => !entry.name.startsWith('.'))
    .filter((entry) => !extension || extname(entry.name) === extension)
    .map((entry) => ({
      name: entry.name,
      path: `${path}/${entry.name}`.replaceAll('\\', '/'),
      type: entry.isDirectory() ? 'directory' : 'file',
      bytes: statSync(join(fullPath, entry.name)).size,
    }))
    .sort((a, b) => a.path.localeCompare(b.path));
}

function countFiles(path, extension = null) {
  return list(path, extension).filter((entry) => entry.type === 'file').length;
}

function read(path) {
  return readFileSync(fromRoot(path), 'utf8');
}

function frontMatterValue(path, key) {
  if (!existsSync(fromRoot(path))) {
    return null;
  }

  const match = read(path).match(new RegExp(`^${key}:\\s*"?([^"\\n]+)"?`, 'm'));
  return match ? match[1] : null;
}

function check(name, ok, details, severity = 'error') {
  return { name, ok: Boolean(ok), severity, details };
}

const observations = {
  generatedAt: new Date().toISOString(),
  repositoryRoot: root,
  scriptPath: relativePath(fileURLToPath(import.meta.url)),
  premise: 'This check is intentionally capable of failing. A non-zero exit means a named world seam broke or drifted.',
  counts: {
    pages: countFiles('content/page', '.md'),
    posts: countFiles('content/post', '.md'),
    pluginIncPhp: countFiles('plugins/world-of-wordpress/inc', '.php'),
    activeThemeTemplates: countFiles('themes/world-of-wordpress/templates', '.html'),
    activeThemeParts: countFiles('themes/world-of-wordpress/parts', '.html'),
    activeThemePatterns: countFiles('themes/world-of-wordpress/patterns', '.php'),
  },
};

const results = [];

for (const path of requiredFiles) {
  results.push(check(
    `required file: ${path}`,
    existsSync(fromRoot(path)),
    existsSync(fromRoot(path)) ? `${path} is present` : `${path} is missing`
  ));
}

for (const module of requiredPluginModules) {
  const path = `plugins/world-of-wordpress/inc/${module}`;
  results.push(check(
    `required plugin module: ${module}`,
    existsSync(fromRoot(path)),
    existsSync(fromRoot(path)) ? `${path} is present` : `${path} is missing`
  ));
}

for (const [name, minimum] of Object.entries(expectedMinimums)) {
  const actual = observations.counts[name];
  results.push(check(
    `minimum ${name}`,
    actual >= minimum,
    `expected at least ${minimum}; found ${actual}`
  ));
}

const observatoryTitle = frontMatterValue('content/page/world-observatory.md', 'title');
results.push(check(
  'observatory keeps its public name',
  observatoryTitle === 'World Observatory',
  `expected title "World Observatory"; found ${JSON.stringify(observatoryTitle)}`
));

const activeThemeStyle = existsSync(fromRoot('themes/world-of-wordpress/style.css'))
  ? read('themes/world-of-wordpress/style.css')
  : '';
results.push(check(
  'active theme identifies itself',
  /^Theme Name:\s*World of WordPress$/m.test(activeThemeStyle),
  'themes/world-of-wordpress/style.css should declare Theme Name: World of WordPress'
));

const pluginBootstrap = existsSync(fromRoot('plugins/world-of-wordpress/world-of-wordpress.php'))
  ? read('plugins/world-of-wordpress/world-of-wordpress.php')
  : '';
results.push(check(
  'plugin registers shared world memory hook',
  pluginBootstrap.includes("add_action( 'datamachine_memory_files', 'world_of_wordpress_register_memory_files' );"),
  'world plugin should keep registering WORLD.md as shared memory'
));

const failed = results.filter((result) => !result.ok && result.severity === 'error');
const report = {
  ...observations,
  ok: failed.length === 0,
  totalChecks: results.length,
  failedChecks: failed.length,
  results,
};

function printHuman(report) {
  const lines = [];
  lines.push('World of WordPress — integrity check');
  lines.push('====================================');
  lines.push(`Generated: ${report.generatedAt}`);
  lines.push(`Script: ${report.scriptPath}`);
  lines.push('');
  lines.push(report.premise);
  lines.push('');
  lines.push('Observed counts:');
  for (const [name, value] of Object.entries(report.counts)) {
    lines.push(`- ${name}: ${value}`);
  }
  lines.push('');
  lines.push(report.ok ? 'All named seams held.' : `${report.failedChecks} named seam(s) broke:`);

  for (const result of report.results) {
    const marker = result.ok ? 'PASS' : 'FAIL';
    lines.push(`- ${marker}: ${result.name} — ${result.details}`);
  }

  console.log(lines.join('\n'));
}

if (jsonMode) {
  console.log(JSON.stringify(report, null, 2));
} else {
  printHuman(report);
}

process.exit(report.ok ? 0 : 1);
