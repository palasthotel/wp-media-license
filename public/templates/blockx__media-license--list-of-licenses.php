<?php

/**
 * @var ListOfLicensesContent $content
 */

use Palasthotel\WordPress\BlockX\Blocks\ListOfLicensesContent;

echo "<div>";
echo "<stong>Licenses:</stong>";
echo "<ul>";
foreach ($content->captions as $imageId => $caption){
	if(empty($caption)) continue;
	echo "<li>$caption</li>";
}
echo "</ul>";
echo "</div>";
