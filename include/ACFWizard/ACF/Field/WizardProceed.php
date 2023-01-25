<?php

namespace ACFWizard\ACF\Field;

use ACFWizard\Asset;

class WizardProceed extends \acf_field {

	/**
	 * @var bool
	 */
	public $show_in_rest = false;

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
			'button_align'  => 'right',
			'hide_label'    => 1,
		];

		add_filter( 'acf/field_wrapper_attributes', [ $this, 'wrapper_attributes'], 9, 2 );

		parent::__construct();
	}

	/**
	 *	@filter acf/field_wrapper_attributes
	 */
	public function wrapper_attributes( $wrapper, $field ) {

		if ( $this->name === $field['type'] ) {

			$wrapper['class'] .= sprintf(' button-align-%s', $field['button_align']);
			if ( $field['hide_label'] ) {
				$wrapper['class'] .= ' no-label';
			}
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
				'label'        => __( 'Hide Label', 'acf-wizard' ),
				'instructions' => __( 'Hide Label and Instructions','acf-wizard' ),
				'type'         => 'true_false',
				'name'         => 'hide_label',
				'ui'           => 1,
			]
		);
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

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Button Alignment', 'acf-wizard' ),
				'instructions' => '',
				'type'         => 'select',
				'name'         => 'button_align',
				'ui'           => 0,
				'multiple'     => 0,
				'choices'      => [
					'left'   => __( 'Left', 'acf-wizard' ),
					'center' => __( 'Center', 'acf-wizard' ),
					'right'  => __( 'Right', 'acf-wizard' ),
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


	/**
	 * @inheritdoc
	 */
	function load_field( $field ) {

		// remove name to avoid caching issue
		$field['name'] = '';

		// remove required to avoid JS issues
		$field['required'] = 0;

		// set value other than 'null' to avoid ACF loading / caching issue
		$field['value'] = false;

		// return
		return $field;

	}


}
