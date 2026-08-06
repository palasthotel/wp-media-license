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
	// These fields are attachment postmeta, saved through sanitize_text_field() -
	// which strips tags but does not escape quotes - so a value like
	// x" onmouseover="alert(1) still needs esc_url()/esc_html() here, at the point
	// it is echoed into an attribute or text node.
	$output = "<span class='media-license__caption'>" . esc_html( wp_strip_all_tags( $caption ) ) . "</span>";
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
		$pre_link = "<a href=\"" . esc_url( $media_license_url ) . "\" >";
		$post_link = "</a>";
	}

	$output .= "<span class='media-license__author'>";
	$output .= __(" by ", "media-license" ) . $pre_link . esc_html( $media_license_author ) . $post_link . " ";
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
