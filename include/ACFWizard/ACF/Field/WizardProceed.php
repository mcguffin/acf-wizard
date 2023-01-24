<?php

namespace ACFWizard\ACF\Field;

use ACFWizard\Asset;

class WizardProceed extends AbstractWizardField {

	/**
	 *	@inheritdoc
	 */
	public function __construct() {

		$this->name = 'wizard_proceed';
		$this->label = __('Wizard Action', 'acf-wizard' );
		$this->category = 'layout';
		$this->defaults = [
			'wizard_action' => 'forward', // forward | back | goto
			'wizard_steps'  => 1, // # steps
			'wizard_target' => '', // wizard page field key
			'style'         => 'primary', // primary | secondary | link
			'button_label'  => __( 'Continue', 'acf-wizard' ),
			'prefill'       => [], // prefill current form. supports select, radio, checkbox, email, url, text, textarea, wysiwyg, color, date
		];

		parent::__construct();
	}

	/**
	 *	@inheritdoc
	 */
	public function render_field_settings( $field ) {
		/*
		# general
		label text
		wizard_action select
			forward
			back
			goto
		wizard_steps number
		wizard_goto select
			field_key => name
		*/

	}

	/**
	 * @inheritdoc
	 */
	public function render_field_general_settings( $field ) {

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Label','acf-wizard' ),
				'instructions' => '',
				'type'         => 'text',
				'name'         => 'button_label',
			]
		);

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Action', 'acf-wizard' ),
				'instructions' => '',
				'type'         => 'select',
				'name'         => 'wizard_action',
				'ui'           => 0,
				'multiple'     => 0,
				'wrapper'      => [ 'width' => 50, ],
				'choices'      => [
					'forward'=> __( 'Forward', 'acf-wizard' ),
					'back'   => __( 'Back', 'acf-wizard' ),
					'goto'   => __( 'Goto Page', 'acf-wizard' ),
				],
			]
		);

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Number of Steps','acf-wizard' ),
				'instructions' => '',
				'type'         => 'number',
				'name'         => 'wizard_steps',
				'wrapper'      => [ 'width' => 50, ],
				'min'          => 1,
				'max'          => 100,
				'step'         => 1,
				'append'       => __( 'Steps', 'acf-wizard' ),
				'conditions'   => [
					'field'    => 'wizard_action',
					'operator' => '!=',
					'value'    => 'goto',
				],
			]
		);

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Wizard Page', 'acf-wizard' ),
				'instructions' => '',
				'type'         => 'select',
				'name'         => 'wizard_target',
				'wrapper'      => [ 'width' => 50, ],
				'ui'           => 0,
				'multiple'     => 0,
				'choices'      => [],
				'conditions'   => [
					'field'    => 'wizard_action',
					'operator' => '==',
					'value'    => 'goto',
				],
			]
		);

	}

	/**
	 * @inheritdoc
	 */
	public function render_field_presentation_settings( $field ) {
		/*
		# presentation
		style select
			primary
			button
			link
		*/

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Style', 'acf-wizard' ),
				'instructions' => '',
				'type'         => 'select',
				'name'         => 'style',
				'ui'           => 0,
				'multiple'     => 0,
				'choices'      => [
					'primary'   => __('Primary Button', 'acf-wizard' ),
					'secondary' => __('Secondary Button', 'acf-wizard' ),
					'link'      => __('Link', 'acf-wizard' ),
				],
			]
		);
	}


	/**
	 *	@inheritdoc
	 */
	public function render_field( $field ) {

		$atts = [
			'type'               => 'button',
			'class'              => 'acf-wizard-btn button-'.$field['style'],
			'data-wizard-action' => $field['wizard_action'],
			'data-wizard-target' => $field['wizard_target'],
			'data-wizard-steps'  => $field['wizard_steps'],
		];
		?>
		<button <?php echo acf_esc_attrs( $atts ); ?>>
			<?php echo esc_html( $field['button_label'] ); ?>
		</button>
		<?php
	}

	/**
	 * Enqueues CSS and JavaScript needed by HTML in the render_field() method.
	 *
	 * Callback for admin_enqueue_script.
	 *
	 * @return void
	 */
	public function input_admin_enqueue_scripts() {

		Asset\Asset::get('css/admin/acf-wizard.css')->enqueue();
		Asset\Asset::get('js/admin/acf-wizard.js')
			->deps('acf-input')
			->enqueue();

	}
}
