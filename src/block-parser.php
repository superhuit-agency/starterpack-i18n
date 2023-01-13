<?php

namespace SUPT\StarterpackI18n\BlockParser;

use function SUPT\StarterpackI18n\Requirements\is_dep_available;

add_action( 'spck_blockparser_parse', __NAMESPACE__.'\add_language_to_parser' );
add_filter( 'spck_blockparser_block_section_news_args', __NAMESPACE__.'\add_language_to_block_args', 10, 2 );

function add_language_to_parser( $parser ) {
	if ( !is_dep_available('polylang') ) return;

	global $post;
	if ( !empty($post) ) $parser->language = pll_get_post_language($post->ID);
}

function add_language_to_block_args( $args, $parser ) {
	if ( !empty($parser->language) ) {
		$args['lang'] = $parser->language;
	}

	return $args;
}
