<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

/**
 * Elementor Term Widget.
 *
 * @since 1.0.0
 */
class Elementor_Vote_Widget extends \Elementor\Widget_Base {

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
		return 'vote';
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
		return esc_html__( 'Vote Calculator', 'elementor-term-widget' );
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
		return [ 'vote', '259', 'tax' ];
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
 			echo '<script>const formatter=new Intl.NumberFormat("en-US",{style: "currency", currency: "USD", minimumFractionDigits: 2, maximumFractionDigits: 2,});function voteCalc(){let homev=Number(document.getElementById("valueNumber").value); let homew=Number(homev/1000); let equas=Number(document.getElementById("townSelect").value); let burd=homew * equas; document.getElementById("resultterms").innerText=formatter.format(burd);}</script><form>Property Value: $<input type="number" id="valueNumber"/><br><label for="town">Choose your town:</label> <select id="townSelect" name="town"> <option value="0.44">Chautauqua</option> <option value="0.45">Clymer</option> <option value="0.46">French Creek</option> <option value="0.46">Mina</option> <option value="0.57">North Harmony</option> <option value="0.48">Ripley</option> <option value="0.46">Sherman</option> <option value="0.66">Westfield</option> </select><br><input type="button" onClick="voteCalc()" Value="Calculate"/></form><p>The Result is : <br><span id="resultterms"></span></p>';
 	 	}

	 }
