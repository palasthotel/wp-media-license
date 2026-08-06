<?php

/**
 * @var ListOfLicensesContent $content
 */

use Palasthotel\WordPress\BlockX\Blocks\ListOfLicensesContent;

$items = array_filter($content->captions, function ($caption) {
	return !empty($caption);
});

// An empty accordion is worse than none at all.
if (empty($items)) {
	return;
}
?>
<details class="media-license-list">
	<summary class="media-license-list__summary"><?php esc_html_e('Licenses:', 'media-license'); ?></summary>
	<ul class="media-license-list__items">
		<?php foreach ($items as $imageId => $caption) : ?>
			<li class="media-license-list__item">
				<?php
				// wp_get_attachment_image() returns "" for an attachment that is gone,
				// in which case the entry stays but without the thumbnail.
				$thumbnail = wp_get_attachment_image(
					$imageId,
					'thumbnail',
					false,
					[
						'class' => 'media-license-list__thumbnail',
						'alt' => '',
						'loading' => 'lazy',
					]
				);
				if ($thumbnail) {
					printf(
						'<a class="media-license-list__anchor" href="#%s" aria-label="%s">%s</a>',
						esc_attr(media_license_get_image_anchor($imageId)),
						esc_attr__('Jump to the image in the article', 'media-license'),
						$thumbnail
					);
				}
				?>
				<span class="media-license-list__caption"><?php echo $caption; ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</details>
