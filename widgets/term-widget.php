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
 			echo '<script>function termCalc(){let dt=new Date(document.getElementById("dateNumber").value); let no_of_months=Number(document.getElementById("monthNumber").value); dt.setMonth(dt.getMonth() + no_of_months); document.getElementById("resulttermccls").innerText=dt.toLocaleDateString();}</script><form>Start Date : <input type="date" id="dateNumber"/><br>Length of Term (months): <input type="number" id="monthNumber"/><br><input type="button" onClick="termCalc()" Value="Calculate"/></form><p>End of Term: <br><span id="resulttermccls"></span></p>';
 	 	}

	 }
