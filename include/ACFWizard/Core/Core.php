<?php
/**
 *	@package ACFWizard\Core
 */

namespace ACFWizard\Core;

use ACFWizard\Asset;
use ACFWizard\ACF\Form;

class Core extends Plugin implements CoreInterface {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'acf/init', [ $this, 'acf_init' ] );
		add_action( 'acf/include_field_types', [ $this, 'register_field_types' ] );
		add_action( 'acf/include_location_rules', [ $this, 'register_location_rules' ] );

		$args = func_get_args();
		parent::__construct( ...$args );
	}

	/**
	 *	Init hook.
	 *
	 *  @action acf/init
	 */
	public function acf_init() {

		Form\WPDashboard::instance();

	}

	/**
	 *	Register Field types
	 *
	 *  @action acf/include_field_types
	 */
	public function register_field_types() {

		acf_register_field_type( 'ACFWizard\ACF\Field\WizardProceed' );
		acf_register_field_type( 'ACFWizard\ACF\Field\WizardStep' );

	}

	/**
	 *	Register Location
	 *
	 *  @action acf/register_location_rules
	 */
	public function register_location_rules() {

		acf_register_location_rule( 'ACFWizard\ACF\Location\WPDashboard' );

	}

}
