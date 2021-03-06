<?php

/**
  * Corona Filter Hooks
  *
  * @since Corona 2.0
  * @package WordPress
  * @subpackage corona
  */




/**
  * Filters the output of the_header_image_tag output to make it a relative path.
  *
  * Enables the display of the header image regardless of the mapped domain.
  *
  * @package Wordpress
  * @subpackage corona
  * @since Corona 2.0
  */

function corona_get_header_image_tag($html, $header, $attr) {
	$header = get_custom_header();

	if ( empty( $header->url ) ) {
		return '';
	}

	// Get relative path
	$path = corona_get_relative_url( $header->url );
	$width = absint( $header->width );
	$height = absint( $header->height );

	// Set src to relative path since domain mapper doesn't work here
	$attr = wp_parse_args(
		array(
		  'src' => $path,
			'width' => $width,
			'height' => $height,
			'alt' => get_bloginfo( 'name' ),
		)
	);

	// Generate 'srcset' and 'sizes' if not already present. NOTE: Domain mapper maps srcset correctly, so we leave it as is
	if ( empty( $attr['srcset'] ) && ! empty( $header->attachment_id ) ) {
		$image_meta = get_post_meta( $header->attachment_id, '_wp_attachment_metadata', true );
		$size_array = array( $width, $height );

		if ( is_array( $image_meta ) ) {
			$srcset = wp_calculate_image_srcset( $size_array, $header->url, $image_meta, $header->attachment_id );
			$sizes = ! empty( $attr['sizes'] ) ? $attr['sizes'] : wp_calculate_image_sizes( $size_array, $header->url, $image_meta, $header->attachment_id );

			if ( $srcset && $sizes ) {
				$attr['srcset'] = $srcset;
				$attr['sizes'] = $sizes;
			}
		}
	}

	$attr = array_map( 'esc_attr', $attr );
	$html = '<img';

	foreach ( $attr as $name => $value ) {
		$html .= ' ' . $name . '="' . $value . '"';
	}

	$html .= ' />';

	return $html;
}

add_filter( 'get_header_image_tag', 'corona_get_header_image_tag', 10, 3 );




/**
  * Filters the output of the_excerpt() 'Read More'
  *
  * @package Wordpress
  * @subpackage corona
  * @since Corona 2.0
  */

function corona_excerpt_read_more( $more ) {
	global $post;
	$read_more = __( 'Read More', 'corona' );

	return ' &hellip; <a class="read-more" href="">' . $read_more . '&nbsp;&raquo; </a>';
}

add_filter( 'excerpt_more', 'corona_excerpt_read_more' );




/**
	* Wrap most common oEmbed video providers in responsive media container
	*
	* @package Wordpress
	* @subpackage corona
	* @since Corona 2.1
	*/

function corona_responsive_oembed_videos( $html, $url, $attr, $post_ID ) {
	$providers = array(
		"youtube.com/",
		"vimeo.com/",
		"vine.co/",
		"ted.com/",
	);

	if ( has_filter( 'corona_video_providers' ) ) {
		$providers = apply_filters( 'corona_video_providers', $providers );
	}

	foreach ( $providers as $provider )  {
		if ( strstr ( $html, $provider ) ) {
			$cached_html = $html;
			$html = '<div class="media-container responsive">';
			 	$html .= $cached_html;
			$html .= '</div>';
		}
	}

	return $html;
}

add_action( 'embed_oembed_html', 'corona_responsive_oembed_videos', 10, 4 );
