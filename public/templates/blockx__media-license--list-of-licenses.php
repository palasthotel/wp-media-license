<?php

/**
 * @var ListOfLicensesContent $content
 */

use Palasthotel\WordPress\BlockX\Blocks\ListOfLicensesContent;

echo "<div>";
echo "<strong>" . esc_html__( 'Licenses:', 'media-license' ) . "</strong>";
echo "<ul>";
foreach ($content->captions as $imageId => $caption){
	if(empty($caption)) continue;
	echo "<li>$caption</li>";
}
echo "</ul>";
echo "</div>";
