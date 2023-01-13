<?php

namespace SUPT\StarterpackI18n;

use function SUPT\StarterpackI18n\Requirements\is_dep_available;

const TRANSLATABLE_WP_SETTINGS = [
	'blogdescription',
	'date_format',
	'time_format',
];

class TranslationStrings {
	function __construct() {
		add_action( 'admin_init', [$this, 'register_translation_strings'] );
		add_action( 'graphql_register_types', [$this, 'register_graphql_query'], 10, 0 );
	}

	function register_graphql_query() {
		if ( !is_dep_available() ) return;

		register_graphql_object_type('TranslationString', [
			'description' => __('Translated string', 'spcki18n'),
			'fields'      => [
				'id' => [
					'type'        => [ 'non_null' => 'String' ],
					'description' => __('String id', 'spcki18n')
				],
				'value' => [
					'type'        => 'String',
					'description' => __('Translated string value', 'spcki18n')
				]
			]
		]);

		register_graphql_field('RootQuery', 'stringsTranslations', [
			'type'        => ['list_of' => 'TranslationString'],
			'description' => __('Strings', 'spcki18n'),
			'args'        => [
				'language' => [
					'type' => [ 'non_null' => 'LanguageCodeEnum' ]
				]
			],
			'resolve' => function ($source, $args, $context, $info) {
				$strings = $this->get_translation_strings();

				$translations = [];

				foreach ($strings as $key => $value) {
					$translations[] = [
						'id'    => $key,
						'value' => pll_translate_string(
							$value['message'],
							$args['language']
						)
					];
				}

				foreach (TRANSLATABLE_WP_SETTINGS as $key) {
					$translations[] = [
						'id'    => $key,
						'value' => pll_translate_string(get_option($key), $args['language']),
					];
				}

				return $translations;
			}
		]);
	}

	/**
	 * Get the group name for the Polylang "Strings translation" view.
	 *
	 * Returns  the directory name of the current theme, without the trailing slash.
	 */
	function get_group_name() {
		return get_template();
	}

	/**
	 * Get the strings to be translated
	 */
	function get_translation_strings() {
		$json_filepaths = apply_filters(
			'spcki18n_translation_strings_filepaths',
			[ SPCKI18N_PATH . '/translation-strings.json' ]
		);

		return array_reduce( $json_filepaths, function ($strings, $filepath) {
			if (is_readable($filepath)) {
				$content = json_decode(file_get_contents($filepath), true);
				$strings = array_merge($strings, $content);
			}
			return $strings;
		}, [] );
	}

	/**
	 * Register strings for the Polylang translation UI
	 */
	function register_translation_strings() {
		if ( !is_dep_available() ) return;

		$strings = $this->get_translation_strings();
		$group = $this->get_group_name();

		foreach ($strings as $key => $value) {
			pll_register_string($key, $value['message'], $group);
		}
	}

	/**
	 * Return strings translated into the given language
	 */
	function translate_strings(string $lang) {
		$strings = $this->get_translation_strings();

		$translations = [];

		foreach ($strings as $key => $value) {
			$translations[$key]['message'] = pll_translate_string(
				$value['message'],
				$lang
			);
		}

		return $translations;
	}
}


// Initialize Translations strings
new TranslationStrings();
