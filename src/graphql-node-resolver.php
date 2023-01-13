<?php

namespace SUPT\StarterpackI18n\GraphQL;

use WPGraphQL\AppContext;
use WPGraphQL\Model\Post;

use function SUPT\StarterpackI18n\get_localized_post_id;
use function SUPT\StarterpackI18n\Requirements\is_dep_available;

add_filter( 'graphql_pre_resolve_uri',  __NAMESPACE__.'\pre_resolve_uri_i18n', 10, 2);
add_filter( 'graphql_RootQuery_fields',  __NAMESPACE__.'\localize_home_front_page_option_resolver', 20);

/**
 * Handle internationalize page_on_front URI
 *
 * @param null $node The node, defaults to nothing.
 * @param string $uri The uri being searched.
 *
 * @see https://github.com/wp-graphql/wp-graphql/blob/v1.6.7/src/Data/NodeResolver.php#L352-L370
 *
 * @return null|GraphQLPost
 */
function pre_resolve_uri_i18n($node, $uri ) {
	// bail early if polylang not enabled
	if ( !is_dep_available('polylang') ) return null;

	$parsed_url = wp_parse_url( $uri );
	if ( preg_match("/^\/?(\w{2})\/?$/", $parsed_url['path'], $matches) ) {

		$page_id       = get_option( 'page_on_front', 0 );
		$show_on_front = get_option( 'show_on_front', 'posts' );
		if ( $show_on_front === 'page' && !empty($page_id)) {
			$localized_page_id = get_localized_post_id($page_id, $matches[1]);
			$node = new Post( get_post($localized_page_id) );
		}
	}

	return $node;
}

/**
 * Localize Home page & front page options on nodeByURI query
 */
function localize_home_front_page_option_resolver($fields) {
	// bail early if polylang not enabled
	if ( !is_dep_available('polylang') ) return $fields;

	if ( isset($fields['nodeByUri']) ) {
		$fields['nodeByUri']['resolve'] = function ( $root, $args, AppContext $context ) {
			if ( empty( $args['uri'] ) ) return null;

			$parsed_url = wp_parse_url( $args['uri'] );

			// If we match a language in the URI
			if ( preg_match("/^\/?(\w{2})\/.+/", $parsed_url['path'], $matches) ) {
				$lang = $matches[1];

				add_filter( 'option_page_on_front', function($value) use ($lang) {
					return get_localized_post_id($value, $lang);
				} );
				add_filter( 'option_page_for_posts', function($value) use ($lang) {
					return get_localized_post_id($value, $lang);
				} );
			}

			return $context->node_resolver->resolve_uri( $args['uri'] );
		};
	}

	return $fields;
}
