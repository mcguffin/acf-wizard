<?php

namespace ACFWizard\ACF\Field;

/**
 * PREFIX_acf_field_FIELD_NAME class.
 */
class AbstractWizardField extends \acf_field {
	/**
	 * @var bool
	 */
	public $show_in_rest = false;


	// /**
	//  * @inheritdoc
	//  */
	// public function render_field_settings( $field ) {
	//
	//
	// }
	//
	// /**
	//  * @inheritdoc
	//  */
	// public function render_field_general_settings( $field ) {
	//
	// }
	//
	// /**
	//  * @inheritdoc
	//  */
	// public function render_field_validation_settings( $field ) {
	// }
	//
	// /**
	//  * @inheritdoc
	//  */
	// public function render_field_presentation_settings( $field ) {
	// }
	//
	// /**
	//  * @inheritdoc
	//  */
	// public function render_field_conditional_logic_settings( $field ) {
	// }


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
