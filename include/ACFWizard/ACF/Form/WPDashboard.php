<?php

namespace ACFWizard\ACF\Form;

use ACFWizard\Asset;
use ACFWizard\Core;

class WPDashboard extends Core\Singleton {

	/** @var Array */
	private $welcome_field_groups = null;

	/** @var int|string */
	private $post_id = 'welcome_panel';

	/** @var string */
	private $capability = 'edit_theme_options';

	/**
	 *	@inheritdoc
	 */
	protected function __construct() {

		add_action( 'admin_init', [ $this, 'admin_init' ] );

	}

	/**
	 *	@action admin_init
	 */
	public function admin_init() {

		/**
		 *	ACF-Post ID for welcome screen values.
		 *
		 *	@param int|string Post ID. Default `welcome_panel`
		 */
		$this->post_id = apply_filters( 'acf_wizard/welcome_panel_form_post_id', $this->post_id );

		/**
		 *	Required capability for showing a form in the welcome screen.
		 *	Additionally WordPress requires the `edit_theme_options` capability
		 *	to show the welcome screen, regardless of what your filter hook returns.
		 *
		 *	@param string Capability Default `edit_theme_options`
		 */
		if ( ! current_user_can( apply_filters( 'acf_wizard/welcome_panel_capability', $this->capability ) ) ) {
			return;
		}

		$this->welcome_field_groups = acf_get_field_groups( [ 'wp_dashboard' => 'welcome_panel' ] );

		if ( ! count( $this->welcome_field_groups ) ) {
			return;
		}

		// replace welcome panel
		remove_action( 'welcome_panel', 'wp_welcome_panel' );
		add_action( 'welcome_panel', [ $this, 'render_form' ] );

		// assets
		acf_enqueue_scripts();

		$css = Asset\Asset::get('css/admin/welcome-panel.css')->enqueue();

		/**
		 *	Allow to dismiss the welcome screen.
		 *
		 *	@param boolean Whether the user is allowed to dismiss the ACF welcome screen. Default `false`
		 */
		if ( ! apply_filters( 'acf_wizard/welcome_dismissable', false ) ) {

			wp_add_inline_style($css->handle, '.welcome-panel-close, label[for="wp_welcome_panel-hide"] { display:none; }' );

			// disallow hide
			add_filter('get_user_metadata', function( $meta, $user_id, $meta_key ) {
				if ( 'show_welcome_panel' === $meta_key ) {
					return true;
				}
				return $meta;
			}, 10, 3 );
		}

		// process form
		add_action( 'load-index.php', [ $this, 'process_form' ] );

	}

	/**
	 *	@action welcome_panel
	 */
	public function render_form() {

		?>
		<div class="postbox">
			<form class="acf-form" method="post">
				<?php

				acf_form_data([
					'screen'     => 'welcome_panel',
					'post_id'    => $this->post_id,
					'validation' => true,
				]);

				foreach ( $this->welcome_field_groups as $field_group ) {

					$fields = acf_get_fields( $field_group );

					?>
					<div class="inside acf-fields acf-welcome-panel-fields -<?php echo esc_attr( $field_group['label_placement'] ); ?>">
						<?php

						acf_render_fields( $fields, $this->post_id, 'div', $field_group['instruction_placement'] );

						?>
					</div>
					<?php

				}
				?>
				<div class="acf-form-submit">
					<span class="acf-spinner spinner"></span>
					<button type="submit" class="acf-button button button-primary button-large">
						<?php esc_html_e( 'Submit', 'acf-wizard' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 *	@action load-index.php
	 */
	public function process_form() {

		if ( ! acf_verify_nonce( 'welcome_panel' ) || ! acf_validate_save_post() ) {
			return;
		}
		acf_save_post( $this->post_id );
	}
	// validate_form

}
