<?php
/**
 * Add post category support to Slack
 * 
 * -Davis Ford, 7/24/2015
 */
class WP_Slack_Add_Post_Categories {
	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'admin_init',     array( $this, 'register_settings' ) );
		add_action( 'admin_menu',     array( $this, 'add_submenu_page' ) );
		add_action( 'plugins_loaded', array( $this, 'set_post_categories' ) );
	}
	/*
	 * Custom Post Category support for WP-Slack
	 */
	public function set_post_categories() {
		add_filter(
			'slack_event_transition_post_status_post_categories',
			function( $post_category ) {
				$set_post_categories = get_option( 'slack-post-categories' );
				foreach( $set_post_categories as $post_categories => $on ) {
					$post_category[] = $post_categories;
				}
				return $post_category;
			}
		);
	}
	/**
	 * Init plugin options to white list our options
	 */
	public function register_settings(){
		// Register our option
		register_setting( 'slack-post-categories', 'slack-post-categories', array( $this, 'sanitize' ) );
		// Add post post-type as default
		//add_option( 'slack-post-categories', array( 'post' => 1 ) );
	}
	/**
	 * Add the menu page
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'edit.php?post_type=slack_integration',
			__( 'Post categories', 'slack-post-categories' ),
			__( 'Post categories', 'slack-post-categories' ),
			'manage_options',
			'slack-post-categories',
			array( $this, 'display_admin_page' )
		);
	}
	/**
	 * Output the admin page
	 */
	public function display_admin_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>

			<h2><?php _e( 'Slack post-categories', 'slack-post-categories' ); ?></h2>
			<p><?php _e( 'Set which post-categories are operational in Slack below.', 'slack-post-categories' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'slack-post-categories' ); ?>

				<table class="form-table"><?php
				foreach ( $this->get_categories() as $post_categories ) {
					// Grab existing setting
					$options = get_option( 'slack-post-categories' );
					if ( isset( $options[$post_categories] ) ) {
						$option = $options[$post_categories];
					} else {
						$option = '';
					}
					?>

					<tr valign="top">
						<td>
							<input type="checkbox" value="1" <?php checked( $option, 1 ); ?> id="<?php echo esc_attr( 'slack-post-categories-' . $post_categories ); ?>" name="<?php echo esc_attr( 'slack-post-categories[' . $post_categories . ']' ); ?>">
							<label class="hidden description" for="<?php echo esc_attr( 'slack-post-categories-' . $post_categories ); ?>"><?php _e( 'Include post-category', 'slack-post-categories' ); ?></label>
						</td>
					</tr><?php
				}
				?>

				</table>

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Settings', 'slack-post-categories' ); ?>" />
				</p>
			</form>
		</div><?php
	}
	/**
	 * Sanitize and validate protection level
	 *
	 * @param    array    $input   The array of allowed post-categories
	 * @return   array    The sanitized array of allowed post-categories
	 */
	public function sanitize( $input ) {
		$post_category = $this->get_categories(); 
		$output = array();
		// Iterate through possible post-categories
		foreach ( $post_category  as $post_categories ) {
			// Only set if post-type exists
			if ( isset( $input[$post_categories] ) ) {
				$output[$post_categories] = 1;
			}
		}
		return $output;
	}
	/**
	 * Get existing available public WordPress post-categories
	 *
	 * @access   private
	 * @return   array  The available public post-categories
	 */
	private function get_categories() {
		$output = 'names'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'
		// Collate builtin and custom post-categories
		$builtin_post_categories = get_categories( array( 'public'   => true, '_builtin' => true ), $output, $operator );
		$custom_post_categories = get_categories( array( 'public'   => true, '_builtin' => false ), $output, $operator );
		$post_category = array_merge( $builtin_post_categories, $custom_post_categories );
		return $post_category;
	}
}
