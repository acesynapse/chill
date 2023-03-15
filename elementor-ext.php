<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

function add_elementor_widget_categories( $elements_manager ) {
	$elements_manager->add_category(
		'chill',
		[
			'title' => esc_html__( 'Chill', 'textdomain' ),
			'icon' => 'fa fa-plug',
		]
	);
}
add_action( 'elementor/elements/categories_registered', 'add_elementor_widget_categories' );

/**
 * Register List Widget.
 *
 * Include widget file and register widget class.
 *
 * @since 1.0.0
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
 * @return void
 */
function register_list_widget( $widgets_manager ) {

	require_once( __DIR__ . '/widgets/term-widget.php' );
	require_once( __DIR__ . '/widgets/vote-widget.php' );
	require_once( __DIR__ . '/widgets/podcast-widget.php' );

	$widgets_manager->register( new \Elementor_Term_Widget() );
	$widgets_manager->register( new \Elementor_Vote_Widget() );
	$widgets_manager->register( new \Elementor_Votebemflu_Widget() );


}
add_action( 'elementor/widgets/register', 'register_list_widget' );
