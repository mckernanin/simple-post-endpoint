<?php
/*
Plugin Name: Simple Post Endpoint
Description: Adds a simple endpoint for posts
Author: Kevin McKernan
Version: 0.1
Author URI: https://mckernan.in
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

register_activation_hook( __FILE__, 'spe_activate' );
function spe_activate() {
	flush_rewrite_rules();
}

add_action( 'rest_api_init', 'spe_register_api_hooks' );
function spe_register_api_hooks() {
	$namespace = 'simple-posts/v1';

	register_rest_route( $namespace, '/list-posts/', array(
		'methods'  => 'GET',
		'callback' => 'spe_get_posts',
	) );
}


function spe_get_posts() {
	if ( 0 || false === ( $return = get_transient( 'spe_all_posts' ) ) ) {
		$query     = apply_filters( 'spe_get_posts_query', array(
			'numberposts' => 10,
			'post_type'   => 'post',
			'post_status' => 'publish',
		) );
		$all_posts = get_posts( $query );
		$return    = array();
		foreach ( $all_posts as $post ) {
			$return[] = array(
				'ID'        => $post->ID,
				'title'     => $post->post_title,
				'permalink' => get_permalink( $post->ID ),
			);
		}

		// cache for 10 minutes
		set_transient( 'spe_all_posts', $return, apply_filters( 'spe_posts_ttl', 60 * 10 ) );
	}
	$response = new WP_REST_Response( $return );
	$response->header( 'Access-Control-Allow-Origin', apply_filters( 'spe_access_control_allow_origin', '*' ) );

	return $response;
}
