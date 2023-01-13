<?php

namespace SUPT\StarterpackI18n\Requirements;

add_action( 'admin_init', __NAMESPACE__.'\check_requirements' );

/**
 * Display a admin notice if required plugins are not enabled.
 */
function check_requirements() {
	$missing_dependencies = get_missing_deps();

	$display_admin_notice = static function () use ($missing_dependencies) {
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'The Starterpack i18n plugin can\'t be loaded because these dependencies are missing:', 'spcki18n' ); ?>
			</p>
			<ul>
			<?php foreach ($missing_dependencies as $missing_dependency): ?>
				<li>- <?php echo esc_html($missing_dependency['label']); ?></li>
			<?php endforeach; ?>
			</ul>
		</div>
		<?php
	};

	if (!empty($missing_dependencies)) {
		add_action('network_admin_notices', $display_admin_notice);
		add_action('admin_notices', $display_admin_notice);
	}
}


function get_missing_deps($dep_to_check = null) {
	$core_dependencies = [
		'polylang' => [
			'label' => 'Polylang plugin',
			'check' => function_exists('PLL')
		],
		'graphql'  => [
			'label' => 'WPGraphQL plugin',
			'check' => class_exists('WPGraphQL'),
		],
	];

	if ( !empty($dep_to_check) ) {
		$core_dependencies = array_filter(
			$core_dependencies,
			function($dep_key) use ($dep_to_check) {
				return $dep_to_check === $dep_key;
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	return array_filter(
		$core_dependencies,
		function($dep) {
			return !$dep['check'];
		},
	);
}

/**
 * Retrieve if given dependency is available.
 *
 * @param string $dep Dependency key. Optional. `polylang` or `graphql`. Default: All
 */
function is_dep_available($dep = null) {
	return empty( get_missing_deps($dep) );
}
