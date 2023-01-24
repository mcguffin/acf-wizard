<?php
/**
 *	@package ACFWizard\Core
 *	@version 1.0.1
 *	2018-09-22
 */

namespace ACFWizard\Core;

use ACFWizard\Asset;
use ACFWizard\ACF\Form;

class Core extends Plugin implements CoreInterface {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'acf/include_field_types', [ $this, 'register_field_types' ] );
		add_action( 'acf/include_location_rules', [ $this, 'register_location_rules' ] );

		Form\WPDashboard::instance();

		$args = func_get_args();
		parent::__construct( ...$args );
	}

	/**
	 *	Init hook.
	 *
	 *  @action acf/init
	 */
	public function register_field_types() {

		acf_register_field_type( 'ACFWizard\ACF\Field\WizardProceed' );
		acf_register_field_type( 'ACFWizard\ACF\Field\WizardStep' );

	}

	public function register_location_rules() {

		acf_register_location_rule( 'ACFWizard\ACF\Location\WPDashboard' );

	}

}
