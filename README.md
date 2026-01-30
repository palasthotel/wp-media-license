# Media License

Allows you to add media license info to your media files. Plugin is available at [WordPress.org](https://wordpress.org/plugins/media-license/)

Captions are automatically added to images in post content. Elsewhere you can use [media_license_get_caption](#get-license-caption-by-attachment-id) function.


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

**Return**

_caption_ ===> rendered caption.
