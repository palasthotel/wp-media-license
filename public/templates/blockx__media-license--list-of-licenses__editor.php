<?php

/**
 * @var ListOfLicensesContent $content
 */

use Palasthotel\WordPress\BlockX\Blocks\ListOfLicensesContent;

$items = array_filter($content->captions, function ($caption) {
	return !empty($caption);
});
?>
<?php // Open in the editor: a collapsed block reads as an empty one on the canvas. ?>
<details class="media-license-list" open>
	<summary class="media-license-list__summary"><?php esc_html_e('Licenses', 'media-license'); ?></summary>
	<?php if (empty($items)) : ?>
		<p class="media-license-list__empty">
			<?php esc_html_e('No licensed images in this post yet.', 'media-license'); ?>
		</p>
	<?php else : ?>
		<ul class="media-license-list__items">
			<?php foreach ($items as $imageId => $caption) : ?>
				<li class="media-license-list__item">
					<?php
					// No anchor here - the editor canvas is not the rendered post, so
					// there is nothing to jump to.
					echo wp_get_attachment_image(
						$imageId,
						'thumbnail',
						false,
						[
							'class' => 'media-license-list__thumbnail',
							'alt' => '',
						]
					);
					?>
					<span class="media-license-list__caption"><?php echo $caption; ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</details>
