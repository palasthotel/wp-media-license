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


/**
 * the caption itself, if there is one
 */
$caption_html = "";
if ( "" != $caption ) {
	// These fields are attachment postmeta, saved through sanitize_text_field() -
	// which strips tags but does not escape quotes - so a value like
	// x" onmouseover="alert(1) still needs esc_url()/esc_html() here, at the point
	// it is echoed into an attribute or text node.
	$caption_html = "<span class='media-license__caption'>" . esc_html( wp_strip_all_tags( $caption ) ) . "</span>";
}

/**
 * the license, either as the Creative Commons badge or as a plain label
 */
$license_html = "";
if( $license->hasLicensePath() && "" != $license->getLink( $license->getImage() )){
	$license_html = $license->getLink( $license->getImage());
} else if($license->hasLicense()){
	$license_html = esc_html( $license->getLabel() );
}

/**
 * credit line: "Image by <author>, <license>" - with either half dropped when it
 * is not set, so a license without an author does not render a dangling "by".
 */
$credit = "";
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

	$credit = sprintf(
		/* translators: %s: name of the author, possibly wrapped in a link */
		__( 'Image by %s', 'media-license' ),
		$pre_link . esc_html( $media_license_author ) . $post_link
	);
}

if ( "" != $credit && "" != $license_html ) {
	$credit .= ", " . $license_html;
} else if ( "" != $license_html ) {
	$credit = $license_html;
}

if ( "" != $credit ) {
	$credit = "<span class='media-license__author'>" . $credit . "</span>";
}

/**
 * The separator is a bare text node on purpose: api.js strips root level text
 * nodes when it appends the license to a caption the block already has, and adds
 * its own separator there instead - otherwise the two would double up.
 */
$output = $caption_html;
if ( "" != $caption_html && "" != $credit ) {
	$output .= " | ";
}
$output .= $credit;

/**
 * shout it out!
 */
echo $output;
