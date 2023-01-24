<?php

namespace ACFWizard\ACF\Form;

use ACFWizard\Asset;
use ACFWizard\Core;

class WPDashboard extends Core\Singleton {

	private $welcome_field_groups = null;

	private $post_id = 'welcome_panel';

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

		// set post_id
		$this->post_id = apply_filters( 'acf_wpo_welcome_panel_form_post_id', 'welcome_panel');

		$this->welcome_field_groups = acf_get_field_groups( [ 'wp_dashboard' => 'welcome_panel' ] );

		if ( ! count( $this->welcome_field_groups ) ) {
			return;
		}

		// replace welcome panel
		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		add_action('welcome_panel', [ $this, 'render_form' ] );

		// assets
		acf_enqueue_scripts();

		Asset\Asset::get('css/admin/welcome-panel.css')->enqueue();

		// disallow hide
		add_filter('get_user_metadata', function( $meta, $user_id, $meta_key ) {
			if ( 'show_welcome_panel' === $meta_key ) {
				return true;
			}
			return $meta;
		}, 10, 3 );

		// process form
		add_action( 'load-index.php', [ $this, 'process_form' ] );

	}

	public function render_form() {

		?>
		<div class="postbox">
			<form class="acf-form" method="post">
				<?php

				acf_form_data([
					'screen'     => 'wp_dashboard',
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


	public function process_form() {

		if ( ! acf_validate_save_post() ) {
			return;
		}
		acf_save_post( $this->post_id );
	}
	// validate_form

}
