<?php

namespace SUPT\StarterpackI18n\GraphQL;

use function SUPT\StarterpackI18n\Requirements\is_dep_available;

class GeneralSettings {

	static $root_query_locale_mapping = [];

	static public function init() {
		add_filter( 'graphql_RootQuery_fields',       [__CLASS__, 'register_language_arg'], 20);
		add_action( 'graphql_before_resolve_field',   [__CLASS__, 'set_lang_before_resolve_fields'], 10, 4 );
		add_filter( 'graphql_generalSettings_fields', [__CLASS__, 'translate_fields'], 10 );
	}

	/**
	 * Register `language` argument for generalSettings field
	 *
	 * @source https://github.com/valu-digital/wp-graphql-polylang/blob/v0.6.0/src/OptionsPages.php#L81-L100
	 */
	static public function register_language_arg ($fields) {
		if ( is_dep_available('polylang') && isset($fields['generalSettings']) ) {
			$fields['generalSettings']['args'] = [
				'language' => [
					'type'        => 'LanguageCodeFilterEnum',
					'description' => 'Filter by by language code (Polylang)',
				],
			];
		}
		return $fields;
	}

	static public function set_lang_before_resolve_fields( $source, $args, $context, $info ) {
		// bail early if polylang not enabled
		if ( !is_dep_available('polylang') ) return;

		// If is generalSeeting root query, store the languague arg if set
		if ( isset($args['language']) && count($info->path) === 1 && in_array('generalSettings', $info->path) ) {
			self::$root_query_locale_mapping[$info->path[0]] = $args['language'];
		}
	}

	static public function translate_fields( $fields ) {
		// bail early if polylang not enabled
	if ( !is_dep_available('polylang') ) return $fields;

		if ( isset( $fields['title'] ) ) {
			$fields['title']['resolve'] = function($root, $args, $context, $info) {
				$root_query = $info->path[0];
				$title = get_bloginfo('title');
				if ( isset(self::$root_query_locale_mapping[$root_query]) )
					$title = pll_translate_string($title, self::$root_query_locale_mapping[$root_query]);

				return $title;
			};
		}

		if ( isset( $fields['description'] ) ) {
			$fields['description']['resolve'] = function($root, $args, $context, $info) {
				$root_query = $info->path[0];
				$desc = get_bloginfo('description');
				if ( isset(self::$root_query_locale_mapping[$root_query]) )
					$desc = pll_translate_string($desc, self::$root_query_locale_mapping[$root_query]);

				return $desc;
			};
		}

		return $fields;
	}
}


GeneralSettings::init();


