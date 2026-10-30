import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { getDocument } from 'pdfjs-dist/legacy/build/pdf.mjs';
import { compareSnapshot } from './snapshot.mjs';

try {
  const request = JSON.parse(readFileSync(0, 'utf8'));
  const resources = new URL('../node_modules/pdfjs-dist/', import.meta.url);
  const task = getDocument({
    data: new Uint8Array(Buffer.from(request.pdf, 'base64')),
    standardFontDataUrl: fileURLToPath(new URL('standard_fonts/', resources)),
    cMapUrl: fileURLToPath(new URL('cmaps/', resources)),
    cMapPacked: true,
    wasmUrl: fileURLToPath(new URL('wasm/', resources)),
    useWorkerFetch: false,
    useSystemFonts: false,
    isEvalSupported: false,
    verbosity: 0,
  });
  try {
    const document = await task.promise;
    if (request.operation === 'snapshot') {
      process.stdout.write(JSON.stringify(await compareSnapshot(document, request)));
    } else {
      const pages = [];
      for (let pageNumber = 1; pageNumber <= document.numPages; pageNumber++) {
        const page = await document.getPage(pageNumber);
        const content = await page.getTextContent();
        pages.push(content.items.map(item => item.str + (item.hasEOL ? '\n' : ' ')).join('').trim());
      }
      process.stdout.write(JSON.stringify({ pages: document.numPages, text: pages.join('\n\n') }));
    }
  } finally {
    await task.destroy();
  }
} catch (error) {
  process.stderr.write(`${error.message}\n`);
  process.exitCode = 1;
}
