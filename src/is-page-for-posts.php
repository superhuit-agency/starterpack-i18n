<?php

namespace SUPT\StarterpackI18n;


use function SUPT\StarterpackI18n\Requirements\is_dep_available;

add_filter( 'spck_is_page_for_posts', __NAMESPACE__.'\is_page_for_posts', 10, 3 );

function is_page_for_posts( $is_page_for_posts, $page_id, $page_for_posts_id ) {

	if ( !is_dep_available('polylang') ) return $is_page_for_posts;

	$page_lang = pll_get_post_language($page_id);
	$page_for_posts_id = get_localized_post_id( $page_for_posts_id, $page_lang );

	return $page_for_posts_id === $page_id;
}
