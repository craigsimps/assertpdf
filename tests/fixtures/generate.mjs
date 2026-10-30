import { writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

// Tiny, deterministic, uncompressed PDFs: no generator or system font dependency.
function pdf({ pages = 1, moved = false, timestamp = '10:30', width = 300 } = {}) {
  const objects = [
    '<< /Type /Catalog /Pages 2 0 R >>',
    `<< /Type /Pages /Kids [${Array.from({ length: pages }, (_, i) => `${4 + i * 2} 0 R`).join(' ')}] /Count ${pages} >>`,
    '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
  ];
  for (let i = 0; i < pages; i++) {
    const stream = `BT /F1 18 Tf 25 260 Td (Invoice 1001) Tj ET\nBT /F1 10 Tf 25 235 Td (Issued ${timestamp}) Tj ET\n0.1 0.3 0.7 rg ${moved ? 100 : 25} 100 80 40 re f\n`;
    objects.push(`<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${width} 300] /Resources << /Font << /F1 3 0 R >> >> /Contents ${5 + i * 2} 0 R >>`);
    objects.push(`<< /Length ${Buffer.byteLength(stream)} >>\nstream\n${stream}endstream`);
  }
  let result = '%PDF-1.4\n';
  const offsets = [0];
  objects.forEach((object, i) => {
    offsets.push(Buffer.byteLength(result));
    result += `${i + 1} 0 obj\n${object}\nendobj\n`;
  });
  const start = Buffer.byteLength(result);
  result += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n`;
  result += offsets.slice(1).map(offset => `${String(offset).padStart(10, '0')} 00000 n \n`).join('');
  return result + `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${start}\n%%EOF\n`;
}

for (const [name, options] of Object.entries({
  invoice: {}, 'two-pages': { pages: 2 }, moved: { moved: true },
  timestamp: { timestamp: '11:59' }, resized: { width: 320 },
  'two-timestamps': { pages: 2, timestamp: '11:59' },
})) {
  writeFileSync(fileURLToPath(new URL(`${name}.pdf`, import.meta.url)), pdf(options));
}
writeFileSync(fileURLToPath(new URL('broken.pdf', import.meta.url)), '%PDF-1.4\nnot a PDF object tree\n');
