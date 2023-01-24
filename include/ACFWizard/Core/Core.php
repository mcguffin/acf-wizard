<?php
/**
 *	@package ACFWizard\Core
 *	@version 1.0.1
 *	2018-09-22
 */

namespace ACFWizard\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}
use ACFWizard\Asset;

class Core extends Plugin implements CoreInterface {

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'init' , array( $this , 'init' ) );

		add_action( 'wp_enqueue_scripts' , array( $this , 'enqueue_assets' ) );

		$args = func_get_args();
		parent::__construct( ...$args );
	}

	/**
	 *	Load frontend styles and scripts
	 *
	 *	@action wp_enqueue_scripts
	 */
	public function enqueue_assets() {
		Asset\Asset::get( 'css/main.css' )->enqueue();
		Asset\Asset::get( 'js/main.js' )
			->deps( ['jquery'] )
			->localize( array(
				/* Script localization */
			) )
			->enqueue();
	}






	/**
	 *	Init hook.
	 *
	 *  @action init
	 */
	public function init() {
	}


}
