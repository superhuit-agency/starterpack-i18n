<?php

namespace SUPT\StarterpackI18n\GraphQL;

use WPGraphQL\Model\Post;

use function SUPT\StarterpackI18n\Requirements\is_dep_available;

add_filter( 'graphql_Page_fields', 'resovle_isFrontPage_isPostsPage', 10, 1 );

/**
 * The isFrontPage and isPostsPage fields don't return correct results on translated pages
 * We fix this by overriding WPGraphQL isFrontPage and isPostsPage resolvers
 *
 * The resolver functions here are built on top of the original ones: https://github.com/wp-graphql/wp-graphql/blob/develop/src/Model/Post.php (look for isFrontPage and isPostsPage keys)
 * More about resolvers here: https://www.wpgraphql.com/docs/graphql-resolvers
 *
 * Note on WP GraphQL debugging
 * Make sure to enable WP GraphQL debug mode in GraphQL > Settings > Enable GraphQL Debug Mode
 * You can then use graphql_debug()	👉 https://www.wpgraphql.com/docs/debugging/
 */
function resovle_isFrontPage_isPostsPage( $fields ) {
	// bail early if polylang not enabled
	if ( !is_dep_available('polylang') ) return $fields;

	if ( isset( $fields['isFrontPage'] ) ) {
		$fields['isFrontPage']['resolve'] = function(Post $post) {
			if ( 'page' !== $post->post_type || 'page' !== get_option( 'show_on_front' ) ) {
				return false;
			}

			$post_translations = array_values(pll_get_post_translations($post->ID));
			$front_page_id     = absint( get_option( 'page_on_front', 0 ) );

			return in_array($front_page_id, $post_translations);
		};
	}

	if ( isset( $fields['isPostsPage'] ) ) {
		$fields['isPostsPage']['resolve'] = function(Post $post) {
			if ( 'page' !== $post->post_type ) return false;

			$post_translations = array_values(pll_get_post_translations($post->ID));
			$page_for_posts_id = absint( get_option( 'page_for_posts', 0 ) );

			return 'posts' !== get_option( 'show_on_front', 'posts' ) && in_array($page_for_posts_id, $post_translations) === true;
		};
	}

	return $fields;
}
