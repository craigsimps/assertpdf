// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

// Canonical origin for <link rel="canonical">, Open Graph URLs, the sitemap and robots.txt.
// PLACEHOLDER until Craig creates the Cloudflare Pages project or connects a domain:
// set SITE_URL in the Cloudflare build environment (see README.md) to override it.
const site = process.env.SITE_URL ?? 'https://laravel-pdf-testing.pages.dev';

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
