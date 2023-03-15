<?php
// If this file is called directly, the teapot refuses to brew coffee.
defined('ABSPATH') || die(http_response_code(418));

/**
 * Elementor Term Widget.
 *
 * @since 1.0.0
 */
class Elementor_Votebemflu_Widget extends \Elementor\Widget_Base {

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
		return 'votebf';
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
		return esc_html__( 'Vote Calculator (BEM/FLU)', 'elementor-votebemflu-widget' );
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
 			echo '<div> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.15.23/css/uikit.min.css"/> <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script> <script type="text/javascript">$(document).ready(function(){$("#valueNumber").maskMoney({prefix:"$ ", precision:"0"});}) </script> <script>const formatter=new Intl.NumberFormat("en-US",{style: "currency", currency: "USD", minimumFractionDigits: 2, maximumFractionDigits: 2,}); function voteCalc(){let homep=document.getElementById("valueNumber").value; let homeq=homep.replace("$",""); let homev=Number(homeq.replace(/,/g,"")); let homew=Number(homev / 1000); let equas=Number(document.getElementById("townSelect").value); let burd=homew * equas; document.getElementById("resultterms").innerText=formatter.format(burd);}</script> <div class="uk-card uk-card-default uk-card-body uk-border-rounded"> <span class="uk-text-lead">Library Vote Calculator</span> <form class="uk-form-horizontal"> <div class="uk-margin-small"> <label class="uk-form-label uk-text-uppercase">Property Value:</label> <div class="uk-form-controls"> <input class="uk-input" type="text" name="mask_input" id="valueNumber" placeholder="$100,000"/> </div></div><div class="uk-margin-small"> <label class="uk-form-label uk-text-uppercase" for="town">Choose your town:</label> <div class="uk-form-controls"> <select class="uk-select" id="townSelect" name="town"> <option value="0.23">Ellery</option> <option value="0.19">Ellicott</option> <option value="0.22">Gerry</option> </select> </div></div><div class="uk-margin-small"> <input class="uk-button uk-button-primary uk-border-rounded" type="button" onClick="voteCalc()" Value="Calculate"/> </div><div class="uk-margin-small"> <label class="uk-form-label uk-text-uppercase">Your Library Support:</label> <div class="uk-form-controls"> <span class="uk-text-bold" id="resultterms">$0.00</span> </div></div></form> </div></div>';
 	 	}

	 }
