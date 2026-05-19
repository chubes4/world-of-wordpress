#!/usr/bin/env node
/**
 * World of WordPress situation report.
 *
 * A tiny standalone Node script that deliberately does not boot WordPress.
 * It reads the repository body from disk and prints a plain inventory of the
 * world surfaces an agent cycle is likely to care about.
 *
 * Usage:
 *   node scripts/world-situation-report.mjs
 *   node scripts/world-situation-report.mjs --json
 */

import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { basename, dirname, extname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const root = dirname(scriptDir);
const jsonMode = process.argv.includes('--json');

const paths = {
  worldModel: 'WORLD.md',
  activePlugin: 'plugins/world-of-wordpress',
  activeTheme: 'themes/world-of-wordpress',
  content: 'content',
  pages: 'content/page',
  posts: 'content/post',
  creatorBundle: 'bundles/world-creator',
  flow: 'bundles/world-creator/flows/world-creator-day-cycle-flow.json',
  pipeline: 'bundles/world-creator/pipelines/world-creator-pipeline.json',
};

function fromRoot(path) {
  return join(root, path);
}

function safeList(path, options = {}) {
  const fullPath = fromRoot(path);
  if (!existsSync(fullPath)) {
    return [];
  }

  const entries = readdirSync(fullPath, { withFileTypes: true })
    .filter((entry) => !entry.name.startsWith('.'))
    .filter((entry) => !options.extension || extname(entry.name) === options.extension)
    .map((entry) => ({
      name: entry.name,
      path: join(path, entry.name).replaceAll('\\', '/'),
      type: entry.isDirectory() ? 'directory' : 'file',
    }))
    .sort((a, b) => a.path.localeCompare(b.path));

  return entries;
}

function countFiles(path, extension = null) {
  return safeList(path, { extension }).filter((entry) => entry.type === 'file').length;
}

function fileSize(path) {
  const fullPath = fromRoot(path);
  return existsSync(fullPath) ? statSync(fullPath).size : 0;
}

function readTitle(path) {
  const fullPath = fromRoot(path);
  if (!existsSync(fullPath)) {
    return basename(path);
  }

  const match = readFileSync(fullPath, 'utf8').match(/^title:\s*"?([^"\n]+)"?/m);
  return match ? match[1] : basename(path, extname(path));
}

function newestMarkdownTitles(path, limit = 5) {
  return safeList(path, { extension: '.md' })
    .map((entry) => ({
      path: entry.path,
      title: readTitle(entry.path),
      modifiedBytes: fileSize(entry.path),
    }))
    .slice(-limit)
    .reverse();
}

function detectRuntimeHints() {
  const pluginBootstrap = join(paths.activePlugin, 'world-of-wordpress.php');
  const themeStyle = join(paths.activeTheme, 'style.css');

  return {
    pluginBootstrap,
    pluginPresent: existsSync(fromRoot(pluginBootstrap)),
    themeStyle,
    themePresent: existsSync(fromRoot(themeStyle)),
    themeName: existsSync(fromRoot(themeStyle))
      ? (readFileSync(fromRoot(themeStyle), 'utf8').match(/^Theme Name:\s*(.+)$/m)?.[1] ?? 'unknown')
      : 'missing',
  };
}

const report = {
  generatedAt: new Date().toISOString(),
  repositoryRoot: root,
  scriptPath: relative(root, fileURLToPath(import.meta.url)).replaceAll('\\', '/'),
  premise: 'This report is produced outside WordPress. It does not load wp-load.php, call WP-CLI, or use browser/runtime APIs.',
  surfaces: {
    worldModel: {
      path: paths.worldModel,
      bytes: fileSize(paths.worldModel),
    },
    wordpressPlugin: {
      path: paths.activePlugin,
      phpFilesAtTopIncLevel: countFiles(`${paths.activePlugin}/inc`, '.php'),
      bootstrap: `${paths.activePlugin}/world-of-wordpress.php`,
    },
    wordpressTheme: {
      path: paths.activeTheme,
      templates: countFiles(`${paths.activeTheme}/templates`, '.html'),
      templateParts: countFiles(`${paths.activeTheme}/parts`, '.html'),
      patterns: countFiles(`${paths.activeTheme}/patterns`, '.php'),
      hasThemeJson: existsSync(fromRoot(`${paths.activeTheme}/theme.json`)),
    },
    markdownContent: {
      path: paths.content,
      pages: countFiles(paths.pages, '.md'),
      posts: countFiles(paths.posts, '.md'),
      recentPostFiles: newestMarkdownTitles(paths.posts, 5),
      pageFiles: newestMarkdownTitles(paths.pages, 20).reverse(),
    },
    agentSubstrate: {
      bundle: paths.creatorBundle,
      flow: paths.flow,
      pipeline: paths.pipeline,
      hasFlow: existsSync(fromRoot(paths.flow)),
      hasPipeline: existsSync(fromRoot(paths.pipeline)),
    },
    sealedSurfaces: [
      '.github/',
      'blueprints/',
      'dependency manifests and lockfiles',
    ],
    runtimeHints: detectRuntimeHints(),
  },
};

function printHuman(report) {
  const lines = [];
  lines.push('World of WordPress — situation report');
  lines.push('======================================');
  lines.push(`Generated: ${report.generatedAt}`);
  lines.push(`Script: ${report.scriptPath}`);
  lines.push('');
  lines.push(report.premise);
  lines.push('');
  lines.push('Durable body:');
  lines.push(`- World model: ${report.surfaces.worldModel.path} (${report.surfaces.worldModel.bytes} bytes)`);
  lines.push(`- Plugin: ${report.surfaces.wordpressPlugin.path} (${report.surfaces.wordpressPlugin.phpFilesAtTopIncLevel} inc/*.php files)`);
  lines.push(`- Theme: ${report.surfaces.wordpressTheme.path} (${report.surfaces.wordpressTheme.templates} templates, ${report.surfaces.wordpressTheme.templateParts} parts, ${report.surfaces.wordpressTheme.patterns} patterns)`);
  lines.push(`- Content: ${report.surfaces.markdownContent.pages} pages, ${report.surfaces.markdownContent.posts} posts under content/`);
  lines.push('');
  lines.push('Agent substrate:');
  lines.push(`- Bundle: ${report.surfaces.agentSubstrate.bundle}`);
  lines.push(`- Flow: ${report.surfaces.agentSubstrate.flow} (${report.surfaces.agentSubstrate.hasFlow ? 'present' : 'missing'})`);
  lines.push(`- Pipeline: ${report.surfaces.agentSubstrate.pipeline} (${report.surfaces.agentSubstrate.hasPipeline ? 'present' : 'missing'})`);
  lines.push('');
  lines.push('Visible pages:');
  for (const page of report.surfaces.markdownContent.pageFiles) {
    lines.push(`- ${page.title} — ${page.path}`);
  }
  lines.push('');
  lines.push('Recent post files:');
  for (const post of report.surfaces.markdownContent.recentPostFiles) {
    lines.push(`- ${post.title} — ${post.path}`);
  }
  lines.push('');
  lines.push('Sealed surfaces:');
  for (const surface of report.surfaces.sealedSurfaces) {
    lines.push(`- ${surface}`);
  }

  console.log(lines.join('\n'));
}

if (jsonMode) {
  console.log(JSON.stringify(report, null, 2));
} else {
  printHuman(report);
}
