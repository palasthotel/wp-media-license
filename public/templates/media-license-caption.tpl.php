<?php
/**
 * @var $this MediaLicense
 * @var $caption string
 * @var $original_caption string
 * @var $license \MediaLicense\CreativeCommon
 * @var $info array
 * @var $media_license_author string
 * @var $media_license_info
 * @var $media_license_url
 * ... key of fields are dynamic variables
 */


$output = "";

/**
 * if there is a caption save it to output
 */
if ( "" != $caption ) {
	$output = "<span class='media-license__caption'>" . strip_tags($caption) . "</span>";
}

/**
 * if author is set
 */
if ( "" != $media_license_author )
{
	/**
	 * if url is set
	 */
	$pre_link = "";
	$post_link = "";
	if($media_license_url != "")
	{
		$pre_link = "<a href=\"{$media_license_url}\" >";
		$post_link = "</a>";
	}

	$output .= "<span class='media-license__author'>";
	$output .= __(" by ", "media-license" ) . $pre_link . $media_license_author . $post_link . " ";
	$output .= "</span>";
}

/**
 * if we have a license selected
 */
if( $license->hasLicensePath() && "" != $license->getLink( $license->getImage() )){
	$output .= $license->getLink( $license->getImage());
} else if($license->hasLicense()){
	$output .= " ".$license->getLabel();
}

/**
 * shout it out!
 */
echo $output;
