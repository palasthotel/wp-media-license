# Contributing

## Branching

`main` is the default branch and always reflects what is released (or about to be
released). Work on a feature branch and open a pull request against `main`.

## Commit messages

Releases and the changelog are generated from the commit history, so commit messages
follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope][!]: <description>

[optional body]

[optional footer]
```

| Type | Effect on the version | Appears in changelog |
|---|---|---|
| `fix:` | patch (1.6.9 → 1.6.10) | yes, "Bug Fixes" |
| `feat:` | minor (1.6.9 → 1.7.0) | yes, "Features" |
| `feat!:` or `BREAKING CHANGE:` footer | major (1.6.9 → 2.0.0) | yes, highlighted |
| `docs:`, `refactor:`, `chore:`, `deps:`, `style:`, `test:`, `ci:` | none | no |

A pull request that should trigger a release needs at least one `fix:` or `feat:`
commit. When squash-merging, make sure the squash commit message itself is a
conventional commit — that is the message release-please reads.

### Which changes get `fix:` or `feat:`

Only changes that matter to someone using the plugin. `fix:` and `feat:` decide the
version *and* write the line that ends up in the changelog on the wordpress.org
plugin page, so the question to ask before committing is whether a user of the plugin
would care about that line.

Everything else takes a type that releases nothing — workflows and CI, release
tooling, repository documentation, internal refactoring, and anything touching files
that are not shipped. As a rule of thumb, a change confined to files outside
`public/` is almost never a `fix:`.

That includes hardening. Blocking direct access to a file that is not part of the
download is `chore:`, not `fix:` — nothing changes for anyone who installed the
plugin.

`src/` is the exception to the "outside `public/`" rule: it is compiled into
`public/dist/` and does reach users, so a change there can be a `fix:` or `feat:`
even though the files live outside `public/`.

## Repository layout

`public/` is exactly what ships to WordPress.org. Everything outside it is
repository-only.

| Path | Description |
|---|---|
| `public/media-license.php` | plugin header and bootstrap |
| `public/classes/` | the plugin's PHP |
| `public/templates/` | overridable output templates |
| `public/dist/` | compiled Gutenberg block assets — **not in the repository**, built from `src/` |
| `public/js/`, `public/styles/` | hand-written admin/frontend assets, not generated |
| `public/languages/` | translations |
| `public/vendor/` | generated composer autoloader, no third-party code |
| `public/composer.json`, `public/composer.lock` | the autoload config `public/vendor/` is generated from |
| `src/` | Gutenberg block JavaScript source |
| `resource/` | wp-env helpers |
| `bin/` | release helper scripts |
| `assets/` | media for the WordPress.org plugin page — not part of the download |

## Local setup

```sh
npm ci
npm run build         # → public/dist/
npx wp-env start      # http://localhost:8888, admin / password
```

`bash bin/pack.sh` stages the payload in `build/media-license/` and zips it to
`media-license.zip` — the same payload the release deploys. It needs `composer`,
because the packed copy gets a freshly generated `--no-dev` autoloader and the
composer files are dropped from it. Run `npm run build` first; the script refuses to
pack an unbuilt payload.

`public/dist/` is generated and gitignored. The release builds it, so there is nothing
to commit and no stale asset to review.

## Versions

Never edit version numbers by hand. `package.json`, `CHANGELOG.md`,
`public/media-license.php` and the `Stable tag:` in `public/readme.txt` are all
maintained by the release pipeline — see
[.github/WORKFLOWS.md](.github/WORKFLOWS.md).

Content changes to `public/readme.txt` (description, FAQ, screenshots, tested-up-to)
are of course done by hand; just leave `Stable tag:` and the `== Changelog ==`
entries alone.

## Checks

Every PR runs `php -l` against PHP 7.4, 8.2, 8.3 and 8.4, builds the Gutenberg block
and asserts the file the plugin enqueues was produced, and packs the plugin so a
broken `bin/pack.sh` surfaces in the pull request rather than in a release.
