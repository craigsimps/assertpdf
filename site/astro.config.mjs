// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

// Canonical origin for <link rel="canonical">, Open Graph URLs, the sitemap and robots.txt.
// assertpdf.com is the registered domain; set SITE_URL in a Cloudflare preview environment to override it.
const site = process.env.SITE_URL ?? 'https://assertpdf.com';

export default defineConfig({
  site,
  output: 'static',
  trailingSlash: 'never',
  integrations: [sitemap()],
  build: {
    // Keep styles in a cacheable file rather than inline <style>, so the CSP in public/_headers
    // can stay at style-src 'self'.
    inlineStylesheets: 'never',
  },
  devToolbar: { enabled: false },
});
