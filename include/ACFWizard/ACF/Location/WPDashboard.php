<?php

namespace ACFWizard\ACF\Location;

class WPDashboard extends \acf_location {

	/**
	 *	@inheritdoc
	 */
	function initialize() {

		// vars
		$this->name = 'wp_dashboard';
		$this->label = __( 'User Dashboard', 'acf-wp-objects' );
		$this->category = __( 'WordPress', 'acf-wp-objects' );

	}

	/**
	 *	@inheritdoc
	 */
	function rule_match( $result, $rule, $screen ) {
		//*
		// global $wp_current_filter;
// var_dump($result,$rule,$screen);
// 		return in_array( 'welcome_panel', $wp_current_filter );
		/*/
		return 'welcome_panel' === current_action();
		//*/
		if ( ! isset( $screen['wp_dashboard'] ) ) {
			return $result;
		}
		return $this->compare_to_rule( $screen['wp_dashboard'], $rule );
	}


	/**
	 *	@inheritdoc
	 */
	function rule_values( $choices, $rule ) {

		// global
		$choices = [
			'welcome_panel' => __( 'Welcome Panel', 'acf-wp-objects' ),
		];

		return $choices;

	}


}
