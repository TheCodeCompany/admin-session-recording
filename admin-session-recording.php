<?php
/**
 * Plugin Name: Admin Session Recording
 * Plugin URI: https://github.com/TheCodeCompany/admin-session-recording
 * Description: Allows Hotjar and Microsoft Clarity to be enabled within the admin area, with front-end being optional.
 * Version: 0.1.0
 * Author: The Code Company
 * Author URI: https://thecode.co
 * License: ISC
 * License URI: https://opensource.org/licenses/ISC
 *
 * @package Admin_Session_Recording
 */

declare(strict_types=1);

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin version.
define( 'ADMIN_SESSION_RECORDING_VERSION', '0.1.0' );

/**
 * Main plugin class.
 */
class Admin_Session_Recording {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private array $options;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->options = get_option(
			'admin_session_recording_options',
			array(
				'service'        => 'disabled',
				'hotjar_id'      => '',
				'clarity_id'     => '',
				'roles'          => array( 'administrator' ),
				'frontend_paths' => array(),
			)
		);

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_head', array( $this, 'add_tracking_scripts' ) );
		add_action( 'wp_head', array( $this, 'maybe_add_frontend_tracking_scripts' ) );
	}

	/**
	 * Initialize admin settings.
	 */
	public function admin_init(): void {
		register_setting(
			'admin_session_recording_options',
			'admin_session_recording_options',
			array( $this, 'validate_options' )
		);
	}

	/**
	 * Add options page to the admin menu.
	 */
	public function add_options_page(): void {
		add_options_page(
			__( 'Admin Session Recording Settings', 'admin-session-recording' ),
			__( 'Session Recording', 'admin-session-recording' ),
			'manage_options',
			'admin-session-recording',
			array( $this, 'render_options_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Only load on our options page and if user has administrator permissions.
		if ( 'settings_page_admin-session-recording' !== $hook || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$plugin_url = plugin_dir_url( __FILE__ );
		$version    = ADMIN_SESSION_RECORDING_VERSION;

		// Enqueue CSS.
		wp_enqueue_style(
			'admin-session-recording-admin',
			$plugin_url . 'assets/css/admin.css',
			array(),
			$version
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			'admin-session-recording-admin',
			$plugin_url . 'assets/js/admin.js',
			array(),
			$version,
			true
		);
	}

	/**
	 * Render the options page.
	 */
	public function render_options_page(): void {
		// Check if user has administrator role.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'admin-session-recording' ) );
		}
		?>
		<div class="wrap">
			<header class="admin-header">
				<h1><?php esc_html_e( 'Admin Session Recording', 'admin-session-recording' ); ?></h1>
				<p class="description"><?php esc_html_e( 'This plugin enables either Hotjar or Microsoft Clarity session recording, primary for administrative/debugging purposes.', 'admin-session-recording' ); ?></p>
			</header>
			
			<form method="post" action="options.php" class="admin-form">
				<?php
				settings_fields( 'admin_session_recording_options' );
				do_settings_sections( 'admin-session-recording' );
				?>
				
				<div class="form-section">
					<div class="form-group">
						<label for="tracking-service-select" class="form-label">
							<?php esc_html_e( 'Tracking Service', 'admin-session-recording' ); ?>
						</label>
						<select name="admin_session_recording_options[service]" id="tracking-service-select" class="form-select">
							<option value="disabled" <?php selected( $this->options['service'], 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'admin-session-recording' ); ?></option>
							<option value="hotjar" <?php selected( $this->options['service'], 'hotjar' ); ?>><?php esc_html_e( 'Hotjar', 'admin-session-recording' ); ?></option>
							<option value="clarity" <?php selected( $this->options['service'], 'clarity' ); ?>><?php esc_html_e( 'Microsoft Clarity', 'admin-session-recording' ); ?></option>
						</select>
						<p class="form-description">
							<?php esc_html_e( 'Select which tracking service to enable.', 'admin-session-recording' ); ?>
						</p>
					</div>
					
					<!-- Service-specific configuration -->
					<div id="service-config" style="<?php echo 'disabled' === $this->options['service'] ? 'display: none;' : ''; ?>">
						<div class="form-group" id="hotjar-id-group" style="<?php echo 'hotjar' === $this->options['service'] ? '' : 'display: none;'; ?>">
							<label for="hotjar-id" class="form-label">
								<?php esc_html_e( 'Hotjar Site ID', 'admin-session-recording' ); ?>
							</label>
							<input type="text" name="admin_session_recording_options[hotjar_id]" id="hotjar-id" value="<?php echo esc_attr( $this->options['hotjar_id'] ); ?>" class="form-input" />
							<p class="form-description">
								<?php esc_html_e( 'Enter your Hotjar Site ID (found in your Hotjar dashboard).', 'admin-session-recording' ); ?>
							</p>
						</div>
						
						<div class="form-group" id="clarity-id-group" style="<?php echo 'clarity' === $this->options['service'] ? '' : 'display: none;'; ?>">
							<label for="clarity-id" class="form-label">
								<?php esc_html_e( 'Microsoft Clarity Project ID', 'admin-session-recording' ); ?>
							</label>
							<input type="text" name="admin_session_recording_options[clarity_id]" id="clarity-id" value="<?php echo esc_attr( $this->options['clarity_id'] ); ?>" class="form-input" />
							<p class="form-description">
								<?php esc_html_e( 'Enter your Microsoft Clarity Project ID (found in your Clarity dashboard).', 'admin-session-recording' ); ?>
							</p>
						</div>
						
						<div class="form-group">
							<fieldset class="form-fieldset">
								<legend class="form-label"><?php esc_html_e( 'Session recording enabled roles', 'admin-session-recording' ); ?></legend>
								<div class="checkbox-group">
									<?php
									$roles = wp_roles()->get_names();
									foreach ( $roles as $role_value => $role_name ) {
										$checked = in_array( $role_value, $this->options['roles'], true ) ? 'checked' : '';
										echo '<label class="checkbox-label"><input type="checkbox" name="admin_session_recording_options[roles][]" value="' . esc_attr( $role_value ) . '" ' . esc_attr( $checked ) . ' /> ' . esc_html( $role_name ) . '</label>';
									}
									?>
								</div>
							</fieldset>
						</div>
						
						<div class="form-group">
							<label class="form-label">
								<?php esc_html_e( 'Frontend Paths', 'admin-session-recording' ); ?>
							</label>
							<div id="frontend-paths-container">
								<?php
								$frontend_paths = $this->options['frontend_paths'];
								if ( empty( $frontend_paths ) ) {
									$frontend_paths = array(
										array(
											'path'       => '',
											'match_type' => 'contains',
										),
									);
								}
								foreach ( $frontend_paths as $index => $path_config ) :
									$path       = $path_config['path'] ?? '';
									$match_type = $path_config['match_type'] ?? 'contains';
									?>
								<div class="path-entry" data-index="<?php echo esc_attr( $index ); ?>">
									<div class="path-input-group">
										<input type="text" 
												name="admin_session_recording_options[frontend_paths][<?php echo esc_attr( $index ); ?>][path]" 
												value="<?php echo esc_attr( $path ); ?>" 
												placeholder="<?php esc_attr_e( 'Enter path pattern (e.g., /products/, /about/)', 'admin-session-recording' ); ?>" 
												class="form-input path-input" />
										<select name="admin_session_recording_options[frontend_paths][<?php echo esc_attr( $index ); ?>][match_type]" class="form-select match-type-select">
											<option value="contains" <?php selected( $match_type, 'contains' ); ?>><?php esc_html_e( 'Contains', 'admin-session-recording' ); ?></option>
											<option value="starts_with" <?php selected( $match_type, 'starts_with' ); ?>><?php esc_html_e( 'Starts with', 'admin-session-recording' ); ?></option>
											<option value="ends_with" <?php selected( $match_type, 'ends_with' ); ?>><?php esc_html_e( 'Ends with', 'admin-session-recording' ); ?></option>
											<option value="regex" <?php selected( $match_type, 'regex' ); ?>><?php esc_html_e( 'Regex', 'admin-session-recording' ); ?></option>
										</select>
										<button type="button" class="button remove-path" <?php echo count( $frontend_paths ) === 1 ? 'style="display: none;"' : ''; ?>>
											<?php esc_html_e( 'Remove', 'admin-session-recording' ); ?>
										</button>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
							<button type="button" id="add-path" class="button button-secondary">
								<?php esc_html_e( 'Add Path', 'admin-session-recording' ); ?>
							</button>
							<p class="form-description">
								<?php esc_html_e( 'Configure URL paths where session recording should be loaded on the frontend. Use different match types for flexible pattern matching.', 'admin-session-recording' ); ?>
								<br>
								<strong><?php esc_html_e( 'Regex examples:', 'admin-session-recording' ); ?></strong>
								<code>^/products/.*</code> <?php esc_html_e( '(starts with /products/)', 'admin-session-recording' ); ?>,
								<code>.*\.html$</code> <?php esc_html_e( '(ends with .html)', 'admin-session-recording' ); ?>,
								<code>/blog/\d+</code> <?php esc_html_e( '(blog with numbers)', 'admin-session-recording' ); ?>,
								<code>.*preview.*</code> <?php esc_html_e( '(preview pages)', 'admin-session-recording' ); ?>,
								<code>.*\?preview=.*</code> <?php esc_html_e( '(preview URLs)', 'admin-session-recording' ); ?>
							</p>
						</div>
						
					</div>
				</div>
				
				<div class="form-actions">
					<?php submit_button(); ?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Validate options before saving.
	 *
	 * @param array $input The input array to validate.
	 * @return array The validated options array.
	 */
	public function validate_options( array $input ): array {
		// Only allow administrators to save settings.
		if ( ! current_user_can( 'manage_options' ) ) {
			// Return current options unchanged if user doesn't have permission.
			return $this->options;
		}

		$service = sanitize_text_field( $input['service'] ?? 'disabled' );

		// Ensure service is one of the allowed values.
		if ( ! in_array( $service, array( 'disabled', 'hotjar', 'clarity' ), true ) ) {
			$service = 'disabled';
		}

		// Process frontend paths.
		$frontend_paths = array();
		if ( isset( $input['frontend_paths'] ) && is_array( $input['frontend_paths'] ) ) {
			foreach ( $input['frontend_paths'] as $path_data ) {
				if ( ! empty( $path_data['path'] ) && ! empty( $path_data['match_type'] ) ) {
					$frontend_paths[] = array(
						'path'       => sanitize_text_field( $path_data['path'] ),
						'match_type' => sanitize_text_field( $path_data['match_type'] ),
					);
				}
			}
		}

		return array(
			'service'        => $service,
			'hotjar_id'      => sanitize_text_field( $input['hotjar_id'] ?? '' ),
			'clarity_id'     => sanitize_text_field( $input['clarity_id'] ?? '' ),
			'roles'          => isset( $input['roles'] ) ? array_map( 'sanitize_text_field', $input['roles'] ) : array(),
			'frontend_paths' => $frontend_paths,
		);
	}

	/**
	 * Add tracking scripts to admin head.
	 */
	public function add_tracking_scripts(): void {
		$user = wp_get_current_user();
		if ( array_intersect( $this->options['roles'], $user->roles ) ) {
			$this->output_tracking_scripts( true, $user );
		}
	}

	/**
	 * Maybe add tracking scripts to frontend based on current URL path.
	 */
	public function maybe_add_frontend_tracking_scripts(): void {
		if ( is_admin() ) {
			return;
		}

		// Check configured frontend paths.
		$current_path   = wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH );
		$frontend_paths = $this->options['frontend_paths'];

		foreach ( $frontend_paths as $path_config ) {
			if ( $this->path_matches( $current_path, $path_config['path'], $path_config['match_type'] ) ) {
				$this->output_tracking_scripts( false );
				break;
			}
		}
	}

	/**
	 * Check if a path matches the given pattern based on match type.
	 *
	 * @param string $current_path The current URL path.
	 * @param string $pattern The pattern to match against.
	 * @param string $match_type The type of matching to perform.
	 * @return bool True if the path matches, false otherwise.
	 */
	private function path_matches( string $current_path, string $pattern, string $match_type ): bool {
		switch ( $match_type ) {
			case 'starts_with':
				return strpos( $current_path, $pattern ) === 0;
			case 'ends_with':
				return substr( $current_path, -strlen( $pattern ) ) === $pattern;
			case 'contains':
				return strpos( $current_path, $pattern ) !== false;
			case 'regex':
				$pattern = '/' . addcslashes( $pattern, '/\\' ) . '/i';
				return preg_match( $pattern, $current_path ) === 1;
			default:
				return false;
		}
	}

	/**
	 * Output the tracking scripts for enabled services.
	 *
	 * @param bool         $is_admin Whether this is being output in the admin area.
	 * @param WP_User|null $user   Optional. The current user object for admin tracking.
	 */
	private function output_tracking_scripts( bool $is_admin, ?WP_User $user = null ): void {
		$service = $this->options['service'];

		if ( 'hotjar' === $service && ! empty( $this->options['hotjar_id'] ) ) {
			$this->output_hotjar_script( $is_admin, $user );
		} elseif ( 'clarity' === $service && ! empty( $this->options['clarity_id'] ) ) {
			$this->output_clarity_script( $is_admin, $user );
		}
	}

	/**
	 * Output the Hotjar tracking script.
	 *
	 * @param bool         $is_admin Whether this is being output in the admin area.
	 * @param WP_User|null $user   Optional. The current user object for admin tracking.
	 */
	private function output_hotjar_script( bool $is_admin, ?WP_User $user = null ): void {
		$hjid = esc_js( $this->options['hotjar_id'] );
		?>
		<script>
			(function(h,o,t,j,a,r){
				h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
				h._hjSettings={hjid:<?php echo $hjid; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>,hjsv:6};
				a=o.getElementsByTagName('head')[0];
				r=o.createElement('script');r.async=1;
				r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
				a.appendChild(r);
			})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
			<?php if ( $is_admin && $user instanceof WP_User ) : ?>
			window.hj('identify', <?php echo esc_js( $user->ID ); ?>, {
				'Email': '<?php echo esc_js( $user->user_email ); ?>',
				'Role': '<?php echo esc_js( $user->roles[0] ); ?>',
			});
			<?php endif; ?>
		</script>
		<?php
	}

	/**
	 * Output the Microsoft Clarity tracking script.
	 *
	 * @param bool         $is_admin Whether this is being output in the admin area.
	 * @param WP_User|null $user   Optional. The current user object for admin tracking.
	 */
	private function output_clarity_script( bool $is_admin, ?WP_User $user = null ): void {
		$clarity_id = esc_js( $this->options['clarity_id'] );
		?>
		<script>
			(function(c,l,a,r,i,t,y){
				c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
				t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
				y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
				<?php if ( $is_admin && $user instanceof WP_User ) : ?>
				clarity('identify', '<?php echo esc_js( $user->user_email ); ?>', null, null, '<?php echo esc_js( $user->user_login ); ?>');
				<?php endif; ?>
			})(window, document, "clarity", "script", "<?php echo $clarity_id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>");
		</script>
		<?php
	}
}

new Admin_Session_Recording();