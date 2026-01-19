/* eslint-disable no-console */
const fs = require('fs');
const path = require('path');

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function copyIfExists(src, dest) {
  if (!fs.existsSync(src)) return false;
  ensureDir(path.dirname(dest));
  fs.copyFileSync(src, dest);
  return true;
}

function main() {
  const projectRoot = path.resolve(__dirname, '..');
  const srcDir = path.join(projectRoot, 'node_modules', 'swiper');
  const outDir = path.join(projectRoot, 'dist', 'vendor', 'swiper');

  const files = [
    'swiper-bundle.min.css',
    'swiper-bundle.min.css.map',
    'swiper-bundle.min.js',
    'swiper-bundle.min.js.map',
  ];

  const copied = [];
  for (const file of files) {
    const ok = copyIfExists(path.join(srcDir, file), path.join(outDir, file));
    if (ok) copied.push(file);
  }

  if (!copied.length) {
    console.warn('[popbag] Swiper files not copied. Did you run `npm install`?');
    process.exitCode = 0;
    return;
  }

  console.log(`[popbag] Swiper copied to dist/vendor/swiper: ${copied.join(', ')}`);
}

main();

