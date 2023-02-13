<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

/**
 * Elementor Term Widget.
 *
 * @since 1.0.0
 */
class Elementor_Term_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve list widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'term';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve list widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Trustee Terms', 'elementor-term-widget' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve list widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-history';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the list widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'chill' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the list widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'term', 'trustee', 'calc', 'calculate' ];
	}

	/**
	 * Register term widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	 protected function register_controls() {

	 	}

	 	/**
	 	 * Render Term widget output on the frontend.
	 	 *
	 	 * Written in PHP and used to generate the final HTML.
	 	 *
	 	 * @since 1.0.0
	 	 * @access protected
	 	 */
		 protected function render() {
 			echo '<div><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.15.23/css/uikit.min.css"/> <script>function termCalc(){let dt=new Date(document.getElementById("dateNumber").value); let no_of_months=Number(document.getElementById("monthNumber").value); dt.setMonth(dt.getMonth() + no_of_months); document.getElementById("resulttermccls").innerText=dt.toLocaleDateString();}</script><div class="uk-card uk-card-default uk-card-body uk-border-rounded"> <span class="uk-text-lead">Trustee Term Calculator</span> <form class="uk-form-horizontal"><div class="uk-margin-small"><label class="uk-form-label uk-text-uppercase">Start Date:</label><div class="uk-form-controls"><input class="uk-input" type="date" id="dateNumber"/></div></div><div class="uk-margin-small"><label class="uk-form-label uk-text-uppercase">Length of Term (months):</label> <div class="uk-form-controls"><input class="uk-input" type="number" id="monthNumber" placeholder="36"/></div></div><div class="uk-margin-small"> <input class="uk-button uk-button-primary uk-border-rounded" type="button" onClick="termCalc()" Value="Calculate"/></div><div class="uk-margin-small"><label class="uk-form-label uk-text-uppercase">End of Term:</label> <div class="uk-form-controls"> <span class="uk-text-bold" id="resulttermccls">xx/xx/xxxx</span></div></div></form></div></div>';
 	 	}

	 }
