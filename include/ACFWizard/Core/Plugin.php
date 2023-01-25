<?php
/**
 *	@package ACFWizard\Core
 *	@version 1.0.0
 *	2018-09-22
 */

namespace ACFWizard\Core;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

class Plugin extends Singleton {

	/** @var string plugin prefix */
	private $plugin_prefix = 'acf_wizard';

	/** @var string plugin main file */
	private $plugin_file;

	/** @var array metadata from plugin file */
	private $plugin_meta;

	/** @var string plugin version */
	private $_version;

	/**
	 *	@inheritdoc
	 */
	protected function __construct( $file ) {

		$this->plugin_file = $file;

		add_action( 'plugins_loaded' , [ $this, 'load_textdomain' ] );

		parent::__construct();
	}

	/**
	 *	@return string full plugin file path
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 *	@return string full plugin file path
	 */
	public function get_plugin_dir() {
		return plugin_dir_path( $this->get_plugin_file() );
	}

	/**
	 *	@return string full plugin url path
	 */
	public function get_plugin_url() {
		return plugin_dir_url( $this->get_plugin_file() );
	}



	/**
	 *	@inheritdoc
	 */
	public function get_asset_roots() {
		return [
			$this->get_plugin_dir() => $this->get_plugin_url(),
		];
	}


	/**
	 *	@return string plugin slug
	 */
	public function get_slug() {
		return basename( $this->get_plugin_dir() );
	}


	/**
	 *	@return string plugin prefix
	 */
	public function get_prefix() {
		return $this->plugin_prefix;
	}

	/**
	 *	@return string Path to the main plugin file from plugins directory
	 */
	public function get_wp_plugin() {
		return plugin_basename( $this->get_plugin_file() );
	}

	/**
	 *	@return string current plugin version
	 */
	public function version() {
		if ( is_null( $this->_version ) ) {
			$this->_version = include_once $this->get_plugin_dir() . '/include/version.php';
		}
		return $this->_version;
	}

	/**
	 *	@param string $which Which plugin meta to get. NUll
	 *	@return string|array plugin meta
	 */
	public function get_plugin_meta( $which = null ) {
		if ( ! isset( $this->plugin_meta ) ) {
			$this->plugin_meta = get_plugin_data( $this->get_plugin_file() );
		}
		if ( isset( $this->plugin_meta[ $which ] ) ) {
			return $this->plugin_meta[ $which ];
		}
		return $this->plugin_meta;
	}

	/**
	 *	Load text domain
	 *
	 *  @action plugins_loaded
	 */
	public function load_textdomain() {
		$path = pathinfo( $this->get_wp_plugin(), PATHINFO_DIRNAME );
		load_plugin_textdomain( 'acf-wizard', false, $path . '/languages' );
	}

}
