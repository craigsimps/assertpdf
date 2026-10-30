import { existsSync, mkdirSync, readFileSync, readdirSync, unlinkSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { createCanvas } from '@napi-rs/canvas';
import { PNG } from 'pngjs';
import pixelmatch from 'pixelmatch';

const dependencies = JSON.parse(readFileSync(new URL('../package.json', import.meta.url))).dependencies;
const renderer = { dependencies, node: process.version, platform: process.platform, arch: process.arch, scale: 1 };

function applyMask(image, mask, pageNumber) {
  if (!mask || mask.page !== pageNumber) return;
  if (mask.x + mask.width > image.width || mask.y + mask.height > image.height) {
    throw new Error('Mask extends beyond the page dimensions.');
  }
  for (let y = mask.y; y < mask.y + mask.height; y++) {
    image.data.fill(255, (y * image.width + mask.x) * 4, (y * image.width + mask.x + mask.width) * 4);
  }
}

function pad(image, width, height) {
  const padded = new PNG({ width, height });
  // Transparent pixels distinguish missing page area from a white page.
  PNG.bitblt(image, padded, 0, 0, image.width, image.height, 0, 0);
  return padded;
}

export async function compareSnapshot(document, { directory, mask, maxDiffPixels, update }) {
  if (!directory || directory.includes('://')) throw new Error('Snapshot directory must be a local path.');
  if (!Number.isInteger(maxDiffPixels) || maxDiffPixels < 0) throw new Error('maxDiffPixels must be a nonnegative integer.');
  if (mask && (!['page', 'x', 'y', 'width', 'height'].every(key => Number.isInteger(mask[key])) ||
      mask.page < 1 || mask.page > document.numPages || mask.x < 0 || mask.y < 0 || mask.width < 1 || mask.height < 1)) {
    throw new Error('Mask requires a valid 1-based page and nonnegative integer x/y, positive width/height.');
  }
  directory = resolve(directory);
  const manifestPath = join(directory, 'manifest.json');
  if (!update && !existsSync(manifestPath)) {
    return { matches: false, message: `Missing PDF snapshot: ${directory}. Set PDF_TESTING_UPDATE_SNAPSHOTS=1 to create it intentionally.` };
  }
  const manifest = update ? null : JSON.parse(readFileSync(manifestPath, 'utf8'));
  if (manifest && JSON.stringify(manifest.renderer) !== JSON.stringify(renderer)) {
    return { matches: false, message: 'PDF snapshot renderer/platform differs. Review and explicitly update the baseline in the target environment.' };
  }
  const failures = [];
  if (manifest && manifest.pages !== document.numPages) {
    failures.push(`PDF snapshot page count: expected ${manifest.pages}, found ${document.numPages}.`);
  }
  for (let number = 1; number <= document.numPages; number++) {
    const page = await document.getPage(number);
    const viewport = page.getViewport({ scale: 1 });
    const canvas = createCanvas(Math.ceil(viewport.width), Math.ceil(viewport.height));
    await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
    const bytes = canvas.toBuffer('image/png');
    page.cleanup();
    const actual = PNG.sync.read(bytes);
    // Validate before persisting; baselines always retain the unmasked pixels.
    applyMask(actual, mask, number);
    const baselinePath = join(directory, `page-${number}.png`);
    if (update) {
      mkdirSync(directory, { recursive: true });
      writeFileSync(baselinePath, bytes);
      continue;
    }
    const artifactDirectory = join(directory, 'artifacts');
    mkdirSync(artifactDirectory, { recursive: true });
    writeFileSync(join(artifactDirectory, `actual-${number}.png`), bytes);
    if (!existsSync(baselinePath)) {
      failures.push(`Missing PDF snapshot page ${number}: ${baselinePath}.`);
      continue;
    }
    const expected = PNG.sync.read(readFileSync(baselinePath));
    applyMask(expected, mask, number);
    const dimensionsDiffer = actual.width !== expected.width || actual.height !== expected.height;
    const width = Math.max(actual.width, expected.width);
    const height = Math.max(actual.height, expected.height);
    const diff = new PNG({ width, height });
    const changed = pixelmatch(pad(expected, width, height).data, pad(actual, width, height).data, diff.data, width, height, { threshold: 0.1 });
    const diffPath = join(artifactDirectory, `diff-${number}.png`);
    if (dimensionsDiffer || changed > maxDiffPixels) {
      writeFileSync(diffPath, PNG.sync.write(diff));
      failures.push(`PDF snapshot page ${number}: ${dimensionsDiffer ? 'dimensions differ; ' : ''}${changed} changed pixels (allowed ${maxDiffPixels}). Diff: ${diffPath}`);
    } else if (existsSync(diffPath)) {
      unlinkSync(diffPath);
    }
  }
  if (update) {
    for (const name of readdirSync(directory)) {
      const match = /^page-(\d+)\.png$/.exec(name);
      if (match && Number(match[1]) > document.numPages) unlinkSync(join(directory, name));
    }
    writeFileSync(manifestPath, JSON.stringify({ renderer, pages: document.numPages }, null, 2) + '\n');
  }
  return { matches: failures.length === 0, message: failures.join('\n') };
}
