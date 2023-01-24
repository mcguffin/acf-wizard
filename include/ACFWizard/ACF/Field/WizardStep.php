<?php

namespace ACFWizard\ACF\Field;

use ACFWizard\Asset;

class WizardStep extends AbstractWizardField {

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
		$this->category = 'layout'; // basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		$this->defaults = [
			// 'show_steps' => 1,
			// 'show_title' => 1,
			'endpoint'          => 0,
			'navigation_style'  => 'name_number',
		];

		add_filter('acf/field_wrapper_attributes', [ $this, 'wrapper_attributes'], 9, 2 );

		parent::__construct();
	}

	public function wrapper_attributes( $wrapper, $field ) {
		if ( $this->name === $field['type'] ) {

			$wrapper['data-wizard-nav'] = $field['navigation_style'];
			$wrapper['data-wizard-end'] = $field['endpoint'];

		}
		return $wrapper;
	}

	/**
	 * @inheritdoc
	 */
	public function render_field_settings( $field ) {


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
				'label'        => __( 'Navigation style', 'acf-wizard' ),
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

	}

	// /**
	//  * @inheritdoc
	//  */
	// public function render_field_conditional_logic_settings( $field ) {
	// }


	/**
	 * @inheritdoc
	 */
	public function render_field( $field ) {
		$atts = [
			'class'            => 'acf-wizard-step',
			'data-key'         => $field['key'],
			'data-stepper-key' => $field['navigation_style'],
			'data-step-name'   => $field['name'],
			'data-step-number' => -1, // counter? reset if endpoint...
		];
		foreach ( explode( '_', $field['navigation_style'] ) as $nav_el ) {
			$atts['class'] .= " acf-wizard-nav-{$nav_el}";
		}

		if ( $field['endpoint'] ) {
			$atts['class'] .= ' acf-wizard-end';
		}

		?>
		<div <?php echo acf_esc_attrs( $atts ); ?>></div>
		<?php
	}

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
}
