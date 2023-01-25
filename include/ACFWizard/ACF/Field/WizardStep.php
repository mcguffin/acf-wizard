<?php

namespace ACFWizard\ACF\Field;

use ACFWizard\Asset;

class WizardStep extends \acf_field {

	/**
	 * @var bool
	 */
	public $show_in_rest = false;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->name = 'wizard_step';
		$this->label = __('Wizard Step', 'acf-wizard' );
		$this->category = 'layout';
		$this->defaults = [
			'endpoint'         => 0,
			'navigation_style' => 'name_number',
			'show_stepper'     => 1,
		];

		add_filter( 'acf/field_wrapper_attributes', [ $this, 'wrapper_attributes'], 9, 2 );

		parent::__construct();
	}

	/**
	 *	@filter acf/field_wrapper_attributes
	 */
	public function wrapper_attributes( $wrapper, $field ) {

		if ( $this->name === $field['type'] ) {

			$wrapper['data-wizard-nav']   = $field['navigation_style'];
			$wrapper['data-wizard-end']   = $field['endpoint'];
			$wrapper['data-show-stepper'] = $field['show_stepper'];

		}
		return $wrapper;
	}

	/**
	 * @inheritdoc
	 */
	public function render_field_general_settings( $field ) {
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Endpoint','acf-wizard' ),
				'instructions' => __( 'End the wizard here','acf-wizard' ),
				'type'         => 'true_false',
				'name'         => 'endpoint',
				'ui'           => 1,
			]
		);

	}
	//
	// /**
	//  * @inheritdoc
	//  */
	// public function render_field_validation_settings( $field ) {
	// }
	//
	/**
	 * @inheritdoc
	 */
	public function render_field_presentation_settings( $field ) {

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Stepper Navigation style', 'acf-wizard' ),
				'instructions' => '',
				'type'         => 'select',
				'name'         => 'navigation_style',
				'ui'           => 0,
				'multiple'     => 0,
				'choices'      => [
					'none'        => __( 'None', 'acf-wizard' ),
					'name_number' => __( 'Name and Number', 'acf-wizard' ),
					'name'        => __( 'Name only', 'acf-wizard' ),
					'number'      => __( 'Number only', 'acf-wizard' ),
				],
				'conditions'   => [
					'field'    => 'endpoint',
					'operator' => '==',
					'value'    => 0,
				],
			]
		);

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Show Stepper','acf-wizard' ),
				'instructions' => '',
				'type'         => 'true_false',
				'name'         => 'show_stepper',
				'ui'           => 1,
			]
		);

	}


	/**
	 * @inheritdoc
	 */
	public function field_group_admin_enqueue_scripts() {

		Asset\Asset::get('css/admin/acf-wizard-settings.css')->enqueue();
		Asset\Asset::get('js/admin/acf-wizard-settings.js')->enqueue();

	}

	/**
	 * @inheritdoc
	 */
	public function input_admin_enqueue_scripts() {

		Asset\Asset::get('css/admin/acf-wizard.css')->enqueue();
		Asset\Asset::get('js/admin/acf-wizard.js')->enqueue();

	}


	/**
	 * @inheritdoc
	 */
	function load_field( $field ) {

		// remove name to avoid caching issue
		$field['name'] = '';

		// remove instructions
		$field['instructions'] = '';

		// remove required to avoid JS issues
		$field['required'] = 0;

		// set value other than 'null' to avoid ACF loading / caching issue
		$field['value'] = false;

		// return
		return $field;

	}


}
