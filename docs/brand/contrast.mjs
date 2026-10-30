#!/usr/bin/env node
// Checks the brand palette's text/background pairs against WCAG 2.2 contrast thresholds.
// Usage: node docs/brand/contrast.mjs   (exits 1 if any pair falls below its target)

const colours = {
  paper: '#FFFFFF', paper2: '#F3F5F7', ink: '#16222E', ink2: '#3F4C59', ink3: '#5B6875',
  teal: '#0B6E66', tealHover: '#095A54', tealTint: '#E3F3F1', tealText: '#075E57',
  diff: '#B42318', diffTint: '#FDECEA',
  ground: '#0F171F', surface: '#182330', text: '#E8EDF2', text2: '#AEBBC8', muted: '#8A98A6',
  tealLight: '#5FD3C6', tealLightHover: '#8AE3D9', tealTintDark: '#12302E', diffLight: '#F28B82', diffTintDark: '#3A1C1A',
  tealDeep: '#06433E', diffMark: '#FF0000',
};

// [foreground, background, minimum ratio]. 4.5 = AA normal text, 3 = AA large text / UI.
const pairs = [
  ['ink', 'paper', 4.5], ['ink', 'paper2', 4.5], ['ink2', 'paper', 4.5], ['ink2', 'paper2', 4.5],
  ['ink3', 'paper', 4.5], ['ink3', 'paper2', 4.5],
  ['teal', 'paper', 4.5], ['teal', 'paper2', 4.5], ['tealHover', 'paper', 4.5], ['paper', 'teal', 4.5],
  ['tealText', 'tealTint', 4.5], ['ink', 'tealTint', 4.5],
  ['diff', 'paper', 4.5], ['diff', 'paper2', 4.5], ['diff', 'diffTint', 4.5], ['paper', 'diff', 4.5],
  ['ink', 'paper', 3], ['teal', 'paper', 3], // UI component boundaries (button outlines, focus ring)
  ['text', 'ground', 4.5], ['text', 'surface', 4.5], ['text2', 'ground', 4.5], ['text2', 'surface', 4.5],
  ['muted', 'ground', 4.5], ['muted', 'surface', 4.5],
  ['tealLight', 'ground', 4.5], ['tealLight', 'surface', 4.5], ['ground', 'tealLight', 4.5],
  ['tealLightHover', 'tealTintDark', 4.5], ['text', 'tealTintDark', 4.5],
  ['diffLight', 'ground', 4.5], ['diffLight', 'surface', 4.5], ['diffLight', 'diffTintDark', 4.5],
  // Teal deep band (added 2026-10-30)
  ['paper', 'tealDeep', 4.5], ['text', 'tealDeep', 4.5], ['text2', 'tealDeep', 4.5], ['tealLight', 'tealDeep', 4.5],
  ['diffMark', 'paper', 3], // graphic only (the diff PNG's red on a white page); never used for text
];

const channel = (c) => { c /= 255; return c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4; };
const luminance = (hex) => {
  const h = hex.replace('#', '');
  const [r, g, b] = [0, 2, 4].map((i) => parseInt(h.slice(i, i + 2), 16));
  return 0.2126 * channel(r) + 0.7152 * channel(g) + 0.0722 * channel(b);
};
const ratio = (a, b) => {
  const [hi, lo] = [luminance(a), luminance(b)].sort((x, y) => y - x);
  return (hi + 0.05) / (lo + 0.05);
};

let failed = false;
for (const [fg, bg, min] of pairs) {
  const r = ratio(colours[fg], colours[bg]);
  const grade = r >= 7 ? 'AAA' : r >= 4.5 ? 'AA' : r >= 3 ? 'AA large/UI' : 'fail';
  const ok = r >= min;
  failed ||= !ok;
  console.log(`${ok ? 'ok  ' : 'FAIL'} ${fg.padEnd(15)} on ${bg.padEnd(13)} ${r.toFixed(2).padStart(5)}:1  ${grade}  (min ${min})`);
}
process.exit(failed ? 1 : 0);
