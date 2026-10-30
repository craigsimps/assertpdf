// Outbound links to GitHub carry one shared referrer tag so visits from the site can be told apart.
export const REF = 'site';

/** Appends ref=site to an absolute URL, using ? or & as needed and keeping any #fragment. */
export function withRef(url) {
  const u = new URL(url);
  u.searchParams.set('ref', REF);
  return u.href;
}
