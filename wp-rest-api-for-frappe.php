<?php
/**
 * Plugin Name: WordPress REST API helper for Frappe Heatmap
 *
 * Plugin URI: https://github.com/ronilaukkarinen/minimalistmadness
 * Description: WP REST API route for Frappe.io heatmap chart at rollemaa.fi.
 * Version: 1.0.0
 * Author: Roni Laukkarinen
 * Author URI: https://github.com/ronilaukkarinen
 * Requires at least: 5.0
 * Tested up to: 5.8.2
 * License: GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package wprestapiforfrappe
 */

add_action( 'rest_api_init', 'init_wordcount_endpoint' );

/**
 * Count the number of words in post content
 * @param string $content The post content
 * @return integer $count Number of words in the content
 */
function post_word_count( $content ) {
  $decode_content = html_entity_decode( $content );
  $filter_shortcode = do_shortcode( $decode_content );
  $strip_tags = wp_strip_all_tags( $filter_shortcode, true );
  $count = str_word_count( $strip_tags );
  return $count;
}

// Add our WP REST API endpoint for Frappe.
function init_wordcount_endpoint() {
  register_rest_route( 'words/v1',
      'getposts', array(
      'methods'   => 'GET',
      'callback'  => __NAMESPACE__ . '\get_posts_and_words',
    )
  );
}

// REST API search endpoint callback.
function get_posts_and_words( $request ) {

  // Build default args
  $heatmap_args = array(
    'post_type' => 'any',
    'posts_per_page' => 1000, // phpcs:ignore
    'post_status' => 'publish',
  );

  // Check if we already have the data
  $heatmap_query = get_transient( 'heatmap_query' );

  if ( false === $heatmap_query ) {
    $heatmap_query = get_posts( $heatmap_args );

    // Put the results in a transient.
    $hours = 1;
    set_transient( 'heatmap_query', $heatmap_query, 3600 );
  }

  return $heatmap_query;
}
