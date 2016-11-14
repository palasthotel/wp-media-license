# Media License

Allows you to add media license info to your media files. Plugin is available at [WordPress.org](https://wordpress.org/plugins/media-license/)

Captions are automatically added 


## Templates

You can copy the default templates from plugins "templates" folder.

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

### Mainupate caption text

```php
add_filter( 'media_license_edit_caption', 'your_function', 10, 3);
```

**Parameters:**

_$caption_ ==> The manipulated caption.

_$caption_original_ ==> The unmanipulated caption.

_$info_ ==> Array of info field values.

**Return**

_manipulated_caption_ ===> Manipulate the $caption and return the result

### Add even more info fields

```php
add_filter( 'media_license_add_fields', 'your_function');
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
