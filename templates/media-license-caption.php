<?php
/**
 * @var $this MediaLicense
 * @var $caption string
 * @var $original_caption string
 * @var $license \MediaLicense\CreativeCommon
 * @var $info array
 * @var $media_license_author string
 * @var $media_license_info
 * ... key of fields are dynamic variables
 */

echo $caption." by ".$media_license_author." ".$license->getLink($license->getImage());