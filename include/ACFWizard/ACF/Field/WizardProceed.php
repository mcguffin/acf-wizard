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
			'wizard_action'  => 'forward', // forward | back | goto
			'wizard_steps'   => 1, // # steps
			'wizard_target'  => '', // wizard page field key
			'style'          => 'primary', // primary | secondary | link
			'size'           => '', // primary | secondary | link
			'button_label'   => __( 'Continue', 'acf-wizard' ),
			'enable_prefill' => 0,
			'auto_disable'   => 1,
			/**  @var array[] [ 'field_key' = 'field_...', 'value' => '...' ] */
			'prefill_values' => [], // prefill current form. supports select, radio, checkbox, email, url, text, textarea, wysiwyg, color, date
			'button_align'   => 'right',
			'hide_label'     => 1,
		];

		add_filter( 'acf/field_wrapper_attributes', [ $this, 'wrapper_attributes'], 9, 2 );

		add_action( 'acf/render_field/type=prefill_values', [ $this, 'render_prefill' ] );

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
					'submit' => __( 'Submit Form', 'acf-wizard' ),
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
					[
						'field'    => 'wizard_action',
						'operator' => '!=',
						'value'    => 'goto',
					],
					[
						'field'    => 'wizard_action',
						'operator' => '!=',
						'value'    => 'submit',
					],
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
				'choices'      => $this->get_wizard_step_choices( $field ),
				'conditions'   => [
					'field'    => 'wizard_action',
					'operator' => '==',
					'value'    => 'goto',
				],
			]
		);

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Auto Disable','acf-wizard' ),
				'instructions' => __( 'Disable button if target is not navigatable', 'acf-wizard' ),
				'type'         => 'true_false',
				'name'         => 'auto_disable',
				'ui'           => 1,
				'conditions'   => [
					[
						'field'    => 'wizard_action',
						'operator' => '!=',
						'value'    => 'submit',
					],
				],
			]
		);

		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Enable Prefill', 'acf-wizard' ),
				'instructions' => __( 'Fill out form values','acf-wizard' ),
				'type'         => 'true_false',
				'name'         => 'enable_prefill',
				'ui'           => 1,
			]
		);

		$this->render_prefill_values( $field );

	}

	/**
	 *	@param array $field
	 *	@return array
	 */
	private function get_wizard_step_choices( $field ) {

		// get field parent until fieldgroup or group with steps

		$fields = array_map(
			function( $field ) {
				return array_intersect_key( $field,
					[
						'key' => '',
						'label' => '',
					]
				);
			},
			array_filter(
				acf_get_fields( $field['parent'] ),
				function( $field ) {
					return $field['type'] === 'wizard_step';
				}
			)
		);
/* ?><pre><?php var_dump($field);?></pre><?php */
		if ( ! count( $fields ) && ! empty( $field['wizard_target'] ) ) {
			return [ $field['wizard_target'] => 'PLACEHOLDER' ];
		}
		return array_combine(
			array_map( function( $choice ) { return $choice['key']; }, $fields ),
			array_map( function( $choice ) { return $choice['label']; }, $fields )
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
				'wrapper'      => [ 'width' => '33' ],
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
				'label'        => __( 'Button Size', 'acf-wizard' ),
				'instructions' => '',
				'type'         => 'select',
				'name'         => 'size',
				'ui'           => 0,
				'multiple'     => 0,
				'wrapper'      => [ 'width' => '33' ],
				'choices'      => [
					'small'   => __( 'Small', 'acf-wizard' ),
					''        => __( 'Normal', 'acf-wizard' ),
					'hero'    => __( 'Large', 'acf-wizard' ),
				],
				'conditions'   => [
					[
						'field'    => 'style',
						'operator' => '!=',
						'value'    => 'link',
					],
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
				'wrapper'      => [ 'width' => '34' ],
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
			'type'                => 'submit' === $field['wizard_action']
				? 'submit'
				: 'button',
			'data-wizard-action'  => $field['wizard_action'],
			'data-wizard-target'  => $field['wizard_target'],
			'data-wizard-steps'   => $field['wizard_steps'],
			'data-wizard-disable' => $field['auto_disable'],
			'data-wizard-prefill' => $field['enable_prefill']
				? $this->sanitize_prefill_values( $field['prefill_values'] )
				: json_encode( false ),
			'class'              => sprintf( 'acf-wizard-btn button-%1$s', $field['style'] ),
		];

		if ( $field['style'] !== 'link' && ! empty( $field['size'] ) ) {
			$atts['class'] .= sprintf(' button button-%s', $field['size'] );
		}

		?>
		<button <?php echo acf_esc_attrs( $atts ); ?>>
			<?php echo esc_html( $field['button_label'] ); ?>
		</button>
		<?php
	}

	/**
	 *	@inheritdoc
	 */
	public function render_prefill_values( $field ) {

		$condition = [
			[
				'field'    => 'wizard_action',
				'operator' => '!=',
				'value'    => 'submit',
			],
			[
				'field' => 'enable_prefill',
				'operator' => '==',
				'value' => '1',
			],
		];

		?>
		<div class="acf-field acf-field-setting-prefill_values" data-type="prefill_values" data-key="prefill_values" data-name="prefill_values" data-setting="wizard_proceed" data-conditions="<?php echo esc_attr( json_encode( $condition ) ) ?>">
			<div class="acf-label">
				<label><?php esc_html_e( 'Prefill Fields', 'acf-wizard' ); ?></label>
			</div>
			<?php

			$tblAttr = [
				'class' => 'acf-table -clear acf-wizard-prefill-table',
			];
			$prefill_values = $this->sanitize_prefill_values( $field['prefill_values'] );
			/* <pre><?php var_dump($prefill_values);?></pre> */
			?>
			<input type="hidden" name="<?php echo esc_attr( $field['prefix'] ); ?>[prefill_values]" value="0">
			<table <?php echo acf_esc_attrs( $tblAttr ); ?>>
				<tbody>
					<?php
					foreach ( $prefill_values as $i => $prefill_value )	{
						$prefill_field = get_field_object( $prefill_value['field_key'] );
						?>
						<tr class="prefill" data-index="<?php echo esc_attr($i); ?>">
							<td class="field">
								<select name="<?php echo esc_attr( $field['prefix'] ); ?>[prefill_values][<?php echo esc_attr( $i ); ?>][field_key]">
									<option value="" selected><?php esc_html_e( '- none -', 'acf-wizard' ); ?></option>
									<option value="<?php echo $prefill_value['field_key']; ?>" selected><?php  ?></option>
								</select>
							</td>
							<td class="value">
								<?php

								$tpl = '<input type="hidden" name="%1$s[prefill_values][%2$d][value]%4$s" value="%3$s" />';

								if ( is_array( $prefill_value['val'] ) ) {
									array_map( function ( $val ) use ( $tpl, $field, $i ) {
										printf(
											$tpl,
											esc_attr( $field['prefix'] ),
											esc_attr( $i ),
											esc_attr( $val ),
											'[]'
										);
									}, $prefill_value['val'] );
								} else {
									printf(
										$tpl,
										esc_attr( $field['prefix'] ),
										esc_attr( $i ),
										esc_attr( $prefill_value['val'] ),
										''
									);
								}
								?>
							</td>
							<td class="remove">
								<a href="#" class="acf-icon -minus remove-prefill-value"></a>
							</td>
						</tr>
						<?php
					}
					?>
					<tr class="prefill acf-wizard-prefill-template acf-hidden" data-index="__idx__" >
						<td class="field">
							<select></select>
						</td>
						<td class="value"><input type="hidden" /></td>
						<td class="remove">
							<a href="#" class="acf-icon -minus remove-prefill-value"></a>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
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
	 *	@param mixed $prefill
	 *	@return array
	 */
	public function sanitize_prefill_values( $prefill ) {
		return array_map(
			function( $item ) {
				$item['val'] = is_array( $item['val'] )
					? array_map( 'trim', $item['val'] )
					: trim($item['val']);
				$item['field_key'] = trim($item['field_key']);
				return $item;
			},
			array_values(
				array_filter( (array) $prefill, function($val) {
					return is_array($val) && isset( $val['field_key'], $val['val'] ) && ! empty( $val['field_key'] );
				} )
			)
		);
	}

	/**
	 *	@inheritdoc
	 */
	public function update_field( $field ) {
		// sanitize steps
		$field['prefill_values'] = $this->sanitize_prefill_values( $field['prefill_values'] );

		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function load_field( $field ) {

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
