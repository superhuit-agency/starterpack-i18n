<?php

namespace SUPT\StarterpackI18n;

use function SUPT\StarterpackI18n\Requirements\is_dep_available;

$localized_posts = [];

/**
 * Retrieve the correct post id for the given (or current is not set) language.
 *
 * @param  int        $id    The initial post id
 * @param  string     $lang  Optional. Language code of the wanted post. Default to current language.
 *
 * @return false|int         The post id for the desired language
 */
function get_localized_post_id($id, $lang = null) {
	global $localized_posts;

	if ( empty($id) ) return false;
	if ( !is_dep_available('polylang') ) return $id;

	// get current language if not defined
	if ( $lang === false ) $lang = pll_current_language();

	$ids = false;
	if ( !empty($localized_posts) && isset($localized_posts[$id]) ) $ids = $localized_posts[$id];
	else {
		$ids = pll_get_post_translations($id);
		$localized_posts[$id] = $ids;
	}

	return (int)( isset($ids[$lang]) ? $ids[$lang] : $id );
}
