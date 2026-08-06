# Media License (WordPress-Plugin)

Allows you to add media license info to your media files. Plugin is available at [WordPress.org](https://wordpress.org/plugins/media-license/)

On the front end, `public/js/api.js` scans the rendered page for `<img class="wp-image-{id}">` elements (the class core blocks like image and gallery add), fetches their captions from the REST API below, and inserts a `<figcaption>` - this requires JavaScript and that class, so it won't run for images without it (e.g. added via raw HTML or a block that doesn't set it) or with JS disabled. Elsewhere you can use the [media_license_get_caption](#get-license-caption-by-attachment-id) function.

The credit reads `Image by <author>, <license>`, with either half dropped when it
is not set. If the attachment has a caption of its own it goes first, separated by
` | `; if the *block* already carries a caption, that one is kept and only the
credit is appended after the same separator.

## REST API

A read-only `GET media_license/v1/captions` route (`public/classes/Rest.php`) takes
an `ids` array of attachment ids and returns each one's rendered caption - the same
markup `media_license_get_caption()` produces, for headless or JS-driven front ends
that can't call the PHP function directly.

## Gutenberg

### Append license info

Image-bearing core blocks get an **Append license info** toggle in the block
sidebar. It is on unless it is switched off, so content that predates the toggle -
and any block nobody has touched - keeps appending the caption exactly as before.

Switching it off writes `mediaLicenseAppendCaption: false` into the block. On
render, `Gutenberg::mark_append_caption_optout()` puts a `data-media-license-skip`
attribute on that block's images, and `public/js/api.js` leaves them alone: no
`figcaption`, and the attachment id never enters the REST request either.

Only the opt-out is stored. Turning the toggle back on clears the attribute rather
than writing `true`, so a block that was never touched and one that was toggled
twice serialize identically.

The blocks that offer the toggle default to `core/image`, `core/gallery`,
`core/media-text` and `core/cover`, and can be changed with
`media_license_append_caption_block_types`.

To switch the automatic captions off for a whole site instead, use the
`media_license_autoload_async_image_license` filter (see Filters below).

### List of licenses

The **List of licenses** block (`public/classes/BlockX/ListOfLicenses.php`, part of
the [BlockX](https://github.com/palasthotel/blockx) integration) lists every license
used among a set of images. `FILTER_INDIVIDUAL_BLOCK_SETTINGS` (see Filters below)
controls whether a given block type shows license info inline, only via a
`data-attribute` for a theme to render, or collects it for that list block instead.

## Templates

You can copy the default templates from plugins "templates" folder to "%theme%/plugin-parts/*".

### media-license-caption.tpl.php

Available variables in template:

_$this_ ===> MediaLicense object context.

_$caption_ ===> Image caption text.

_$original_caption_ ===> Unmanipulated caption text. (No manipulations from other plugins)

_$license_ ===> MediaLicense\CreativeCommon object

_$info_ ===> Array of meta information. (author, info, url, all additional from add_fields filter)

_$media_license_author_  ===> Author field text.

_$media_license_info_ ===> License info text.

_$media_license_url_ ===> License url.


---

## Filters

Available filters for media license plugin.

### Disable default frontend styles

Add the following filter in your code


```php
add_filter(Plugin::FILTER_ENABLE_FRONTEND_STYLES, fn () => true);
```

### Add overwrite settings for individual Gutenberg blocks

Add the following filter in your code


```php
if(class_exists("\Palasthotel\MediaLicense\Plugin")){
    add_filter(\Palasthotel\MediaLicense\Plugin::FILTER_ENABLE_FRONTEND_STYLES, fn () => true);
}
```

### Provide custom template path

Add the following filter in your custom plugins code

```php
if(class_exists("\Palasthotel\MediaLicense\Plugin")){
	add_filter(\Palasthotel\MediaLicense\Plugin::FILTER_INDIVIDUAL_BLOCK_SETTINGS, function($settings, $central_setting){
        $settings['core/cover'] = 'collect';
        return $settings;
	});
}
```

**Parameters:**

_$settings_ ==> Array of all active individual block settings.

_$central_setting ==> central settings that is set for all blocks.

**Return**

_$settings_ ===> Manipulate the $settings and return the result

### Manipulate caption text

```php
add_filter( 'media_license_edit_caption', 'myplugin_media_license_edit_caption', 10, 3);
function myplugin_media_license_edit_caption($caption, $original_caption, $info){
	// manipulate $caption
	return $caption;
}
```

**Parameters:**

_$caption_ ==> The manipulated caption.

_$caption_original_ ==> The unmanipulated caption.

_$info_ ==> Array of info field values.

**Return**

_manipulated_caption_ ===> Manipulate the $caption and return the result

### Add even more info fields

```php
add_filter( 'media_license_add_fields', 'myplugin_media_license_add_fields');
function myplugin_media_license_add_fields($fields){
	// manipulate $fields
	return $fields;
}
```

**Parameters:**

_$fields_ ==> Array of field definitions.

Available Types:

```php
$fields['my_text_field'] = array(
	'label' => 'Field label',
	'input' => 'text',
	'value' => 'default value',
	'helps' => 'Descriptive text',
);

$fields['my_select_field'] = array(
    'label' => 'Field label',
    'input' => 'select',
    'value' => '',
    'helps' => 'Descriptive text',
    'selections' => array(
        array(
            "value" => 'slug1',
            "label" => 'Label 1',
        ),
        ...
    ),
);
```

---

### Autoload captions

```php
add_filter( 'media_license_autoload_async_image_license', 'myplugin_media_license_autoload_async_image_license', 10, 3);
function myplugin_media_license_autoload_async_image_license($autoload){
	// disable autoload 
	return false;
}
```

**Parameters:**

_$autoload_ ==> boolean.

**Return**

_autoload_ ===> if captions should be autoloaded in post content

## Functions

Public plugin function. Always use ```php function_exists(...)``` before using an function.
 
### Get license caption by attachment ID

```php
$caption = media_license_get_caption($attachment_id)
```

**Parameters:**

_$attachment_id_ ==> ID of the attachment.

---

## Repository layout

`public/` is exactly what ships to WordPress.org. Everything outside it is
repository-only.

| Path | Description |
|---|---|
| `public/media-license.php` | plugin header and bootstrap |
| `public/classes/` | the plugin's PHP |
| `public/templates/` | overridable output templates |
| `public/dist/` | compiled Gutenberg block assets — **generated**, not in the repository |
| `public/js/`, `public/styles/` | hand-written admin/frontend assets, not generated |
| `public/languages/` | translations |
| `public/vendor/` | generated composer autoloader, no third-party code |
| `src/` | Gutenberg block JavaScript source |
| `assets/` | media for the WordPress.org plugin page — not part of the download |
| `media-license.php` | DEV wrapper, loads `public/media-license.php` when the repository is checked out into `wp-content/plugins/` |
| `bin/` | release helper scripts |
| `.github/workflows/` | CI/CD — see [.github/WORKFLOWS.md](.github/WORKFLOWS.md) |

## Development

```sh
npm ci
npm run build      # → public/dist/
npx wp-env start   # http://localhost:8888, admin / password
bash bin/pack.sh   # → media-license.zip
```

`public/dist/` is generated and gitignored — the release pipeline builds it. Run
`npm run build` before `wp-env start` or `bin/pack.sh`; the pack script refuses to
package an unbuilt payload.

## Releasing

Releases are automated with [release-please](https://github.com/googleapis/release-please)
and deployed to the WordPress.org SVN repository. Commit with
[conventional commits](https://www.conventionalcommits.org/) and merge the release PR:

```
fix: …   → patch    feat: …  → minor    feat!: … → major
```

Details in [.github/WORKFLOWS.md](.github/WORKFLOWS.md), commit conventions in
[CONTRIBUTING.md](CONTRIBUTING.md).

## License

GNU General Public License v3.0 or later — see [LICENSE](LICENSE).

The plugin's early history (pre-2018) never recorded an explicit "or later" clause
next to its GPLv2 header, and predates this repository's `git log`. It has been
treated throughout as GPL-2.0-or-later, consistent with the wordpress.org readme's
own (previously unsynced) claim of GPLv3 - which is what makes the move to
GPL-3.0-or-later here a one-way, always-permitted relicensing rather than one that
would need every past contributor's consent.

**Return**

_caption_ ===> rendered caption.
