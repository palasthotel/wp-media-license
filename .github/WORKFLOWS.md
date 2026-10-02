# CI/CD Workflows

The four workflows in `.github/workflows/` call the shared ones in
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows). How
they work, every input and what to do when a deploy fails is described there, in
[docs/wp-plugin.md](https://github.com/palasthotel/github-workflows/blob/main/docs/wp-plugin.md).

What is specific to this plugin:

| | |
|---|---|
| wordpress.org slug | `media-license` |
| version file | `package.json` (`release-type: node`) |
| build step | `npm ci && npm run build` in `pr.yml` and the deploy - `public/dist/` (the editor script) is not in the repository |
| `required-files` | the built `dist/media-license.js` and `.asset.php`, the front-end and admin scripts, the stylesheet, the caption template and the translations |
| composer | `public/composer.json` only maps the autoloader; the pack regenerates `vendor/` without dev dependencies and drops `composer.json`/`composer.lock` from the payload |
| `php -l` | 8.0 to 8.4 - `Requires PHP: 8.0` |
| `assets/` | in the repository (the plugin page icons), mirrored into the SVN `assets/` on every release - files removed here are removed from the plugin page |

The deploy workflow was called `release.yml` before it moved to the shared workflow; it is
`wordpress-svn-release.yml` now, like in the other plugin repositories.
