<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles Simple Form AJAX submissions independently of Elementor widget construction.
 * Widget constructors do not always run on admin-ajax.php, so handlers live here.
 */
class EMHA_Form_Handler {

	public function __construct() {
		add_action( 'wp_ajax_emha_submit_form', [ $this, 'handle_form_submission' ] );
		add_action( 'wp_ajax_nopriv_emha_submit_form', [ $this, 'handle_form_submission' ] );
	}

	/**
	 * Persist form settings so submissions work without parsing Elementor document tree.
	 *
	 * @param string $form_id Widget ID.
	 * @param array  $settings Widget settings.
	 */
	public static function save_form_config( $form_id, $settings ) {
		if ( empty( $form_id ) || ! is_array( $settings ) ) {
			return;
		}

		$configs = get_option( 'emha_form_configs', [] );
		if ( ! is_array( $configs ) ) {
			$configs = [];
		}

		$form_mode = isset( $settings['form_mode'] ) ? sanitize_text_field( $settings['form_mode'] ) : 'contact';

		// Registration mode uses a fixed success message (email activation flow).
		$success_message = isset( $settings['success_message'] ) ? sanitize_text_field( $settings['success_message'] ) : 'Your submission was sent successfully!';
		if ( 'register' === $form_mode ) {
			$success_message = ! empty( $settings['register_success_message'] )
				? sanitize_textarea_field( $settings['register_success_message'] )
				: __( 'Signup successful! Please check your email for a link to set your password and log in.', 'elementor-must-have-addons' );
		}

		$configs[ $form_id ] = [
			'form_name'             => isset( $settings['form_name'] ) ? sanitize_text_field( $settings['form_name'] ) : 'Contact Form',
			'admin_email'           => isset( $settings['admin_email'] ) ? sanitize_email( $settings['admin_email'] ) : get_option( 'admin_email' ),
			'email_subject'         => isset( $settings['email_subject'] ) ? sanitize_text_field( $settings['email_subject'] ) : 'New Form Submission',
			'success_message'       => $success_message,
			'form_mode'             => $form_mode,
			'multisite_signup_type' => isset( $settings['multisite_signup_type'] ) ? sanitize_text_field( $settings['multisite_signup_type'] ) : 'user',
			'form_fields'           => isset( $settings['form_fields'] ) ? $settings['form_fields'] : [],
			'updated_at'            => current_time( 'mysql' ),
		];

		update_option( 'emha_form_configs', $configs, false );
	}

	/**
	 * Ensure submissions table exists (covers missing activation hook runs).
	 */
	public static function maybe_create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'emha_submissions';
		$like       = $wpdb->esc_like( $table_name );
		$found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

		if ( $found === $table_name ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			form_id varchar(100) NOT NULL,
			form_name varchar(255) NOT NULL,
			fields text NOT NULL,
			user_ip varchar(100) DEFAULT '' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public function handle_form_submission() {
		// Nonce can arrive as _wpnonce (localized / form field).
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'emha_form_nonce' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed. Please refresh the page and try again.', 'elementor-must-have-addons' ) ] );
		}

		$form_id    = isset( $_POST['form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) : '';
		$form_name  = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : 'Simple Form';
		$raw_fields = isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : [];

		if ( empty( $raw_fields ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please fill out the form.', 'elementor-must-have-addons' ) ] );
		}

		$fields = [];
		foreach ( $raw_fields as $key => $val ) {
			$fields[ sanitize_text_field( $key ) ] = sanitize_textarea_field( $val );
		}

		// Defaults.
		$recipient   = get_option( 'admin_email' );
		$subject     = __( 'New Form Submission', 'elementor-must-have-addons' );
		$success_msg = __( 'Your submission was sent successfully!', 'elementor-must-have-addons' );

		// Preferred: config saved when the widget was rendered.
		$configs = get_option( 'emha_form_configs', [] );
		$config  = [];
		if ( $form_id && is_array( $configs ) && ! empty( $configs[ $form_id ] ) ) {
			$config = $configs[ $form_id ];
		} elseif ( ! empty( $_POST['post_id'] ) && class_exists( '\Elementor\Plugin' ) ) {
			// Fallback: walk Elementor document data.
			$post_id  = intval( $_POST['post_id'] );
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$widget_data = self::find_widget_data( $document->get_elements_data(), $form_id );
				if ( $widget_data && ! empty( $widget_data['settings'] ) ) {
					$settings = $widget_data['settings'];
					$form_mode_fb = isset( $settings['form_mode'] ) ? sanitize_text_field( $settings['form_mode'] ) : 'contact';
					$success_fb   = isset( $settings['success_message'] ) ? sanitize_text_field( $settings['success_message'] ) : 'Your submission was sent successfully!';
					if ( 'register' === $form_mode_fb ) {
						$success_fb = ! empty( $settings['register_success_message'] )
							? sanitize_textarea_field( $settings['register_success_message'] )
							: __( 'Signup successful! Please check your email for a link to set your password and log in.', 'elementor-must-have-addons' );
					}
					$config = [
						'form_name'             => isset( $settings['form_name'] ) ? sanitize_text_field( $settings['form_name'] ) : 'Contact Form',
						'admin_email'           => isset( $settings['admin_email'] ) ? sanitize_email( $settings['admin_email'] ) : get_option( 'admin_email' ),
						'email_subject'         => isset( $settings['email_subject'] ) ? sanitize_text_field( $settings['email_subject'] ) : 'New Form Submission',
						'success_message'       => $success_fb,
						'form_mode'             => $form_mode_fb,
						'multisite_signup_type' => isset( $settings['multisite_signup_type'] ) ? sanitize_text_field( $settings['multisite_signup_type'] ) : 'user',
						'form_fields'           => isset( $settings['form_fields'] ) ? $settings['form_fields'] : [],
					];
				}
			}
		}

		$form_mode             = isset( $config['form_mode'] ) ? $config['form_mode'] : 'contact';
		$multisite_signup_type = isset( $config['multisite_signup_type'] ) ? $config['multisite_signup_type'] : 'user';

		if ( ! empty( $config['admin_email'] ) && is_email( $config['admin_email'] ) ) {
			$recipient = $config['admin_email'];
		}
		if ( ! empty( $config['email_subject'] ) ) {
			$subject = $config['email_subject'];
		}
		if ( ! empty( $config['success_message'] ) ) {
			$success_msg = $config['success_message'];
		}
		if ( ! empty( $config['form_name'] ) ) {
			$form_name = $config['form_name'];
		}

		// User Registration Mode — WordPress default fields only (no custom extras).
		if ( 'register' === $form_mode ) {
			self::handle_registration_submission( $fields, $form_id, $form_name, $success_msg, $multisite_signup_type );
		}

		// Contact Form Mode (Original Code)
		self::maybe_create_table();

		global $wpdb;
		$table_name = $wpdb->prefix . 'emha_submissions';
		$user_ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		$inserted = $wpdb->insert(
			$table_name,
			[
				'form_id'   => $form_id,
				'form_name' => $form_name,
				'fields'    => wp_json_encode( $fields ),
				'user_ip'   => $user_ip,
			],
			[ '%s', '%s', '%s', '%s' ]
		);

		// Send email alert.
		$email_content  = "New submission from: {$form_name}\n\n";
		foreach ( $fields as $label => $value ) {
			$email_content .= ucfirst( str_replace( [ '-', '_' ], ' ', $label ) ) . ': ' . $value . "\n";
		}
		$email_content .= "\nSubmitted on: " . current_time( 'mysql' ) . "\n";
		$email_content .= 'IP Address: ' . $user_ip . "\n";

		$headers   = [ 'Content-Type: text/plain; charset=UTF-8' ];
		$mail_sent = false;
		if ( is_email( $recipient ) ) {
			$mail_sent = wp_mail( $recipient, $subject, $email_content, $headers );
		}

		if ( $inserted || $mail_sent ) {
			wp_send_json_success( [ 'message' => $success_msg ] );
		}

		$db_error = $wpdb->last_error ? $wpdb->last_error : __( 'Unknown database error', 'elementor-must-have-addons' );
		wp_send_json_error(
			[
				'message' => esc_html__( 'Failed to process submission. Please try again.', 'elementor-must-have-addons' ),
				'debug'   => defined( 'WP_DEBUG' ) && WP_DEBUG ? $db_error : '',
			]
		);
	}

	/**
	 * Handle User Registration using WordPress default fields only.
	 *
	 * Multisite uses the same flow as Network Admin → Add User:
	 * create account immediately, then email a "set your password" link
	 * (wp-login.php?action=rp). This avoids the public signup activation page
	 * (wp-activate.php) which shows username/password and often has broken layout.
	 *
	 * @param array  $fields                Submitted fields.
	 * @param string $form_id               Widget form ID.
	 * @param string $form_name             Form name for logging.
	 * @param string $success_msg           Success message.
	 * @param string $multisite_signup_type user|blog|both.
	 */
	private static function handle_registration_submission( $fields, $form_id, $form_name, $success_msg, $multisite_signup_type = 'user' ) {
		// Fixed WordPress signup keys only — ignore any extra posted keys.
		$user_login = isset( $fields['user_login'] ) ? sanitize_user( $fields['user_login'], true ) : '';
		$user_email = isset( $fields['user_email'] ) ? sanitize_email( $fields['user_email'] ) : '';
		$signup_for = isset( $fields['signup_for'] ) ? sanitize_text_field( $fields['signup_for'] ) : 'user';
		$blogname   = isset( $fields['blogname'] ) ? sanitize_text_field( $fields['blogname'] ) : '';
		$blog_title = isset( $fields['blog_title'] ) ? sanitize_text_field( $fields['blog_title'] ) : '';

		$user_login = strtolower( $user_login );
		$blogname   = strtolower( preg_replace( '/[^a-z0-9\-]/', '', strtolower( $blogname ) ) );

		if ( empty( $user_login ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Username is required.', 'elementor-must-have-addons' ) ] );
		}
		if ( empty( $user_email ) || ! is_email( $user_email ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please enter a valid email address.', 'elementor-must-have-addons' ) ] );
		}

		$default_success = __( 'Signup successful! Please check your email for a link to set your password and log in.', 'elementor-must-have-addons' );
		if ( empty( $success_msg ) || 'Your submission was sent successfully!' === $success_msg ) {
			$success_msg = $default_success;
		}

		// ——— Multisite: create user (and optional site) like Network Admin → Add User ———
		if ( is_multisite() ) {
			if ( ! function_exists( 'wpmu_validate_user_signup' ) || ! function_exists( 'wpmu_create_user' ) ) {
				require_once ABSPATH . WPINC . '/ms-functions.php';
			}

			$active_signup = get_site_option( 'registration', 'none' );
			if ( 'none' === $active_signup ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Registration is currently disabled on this network.', 'elementor-must-have-addons' ) ] );
			}

			// Resolve signup type vs network policy (same rules as wp-signup.php).
			// user = new accounts only | blog = logged-in users create sites | all = both
			$network_allows_blog = in_array( $active_signup, [ 'blog', 'all' ], true );
			$network_allows_user = in_array( $active_signup, [ 'user', 'all' ], true );

			if ( 'blog' === $active_signup && ! is_user_logged_in() ) {
				wp_send_json_error( [ 'message' => esc_html__( 'You must be logged in to create a new site on this network.', 'elementor-must-have-addons' ) ] );
			}

			if ( 'all' !== $active_signup && 'user' === $active_signup ) {
				$signup_for = 'user';
			} elseif ( 'blog' === $active_signup ) {
				$signup_for = 'blog';
			} elseif ( 'blog' === $multisite_signup_type ) {
				$signup_for = 'blog';
			} elseif ( 'user' === $multisite_signup_type ) {
				$signup_for = 'user';
			} elseif ( ! in_array( $signup_for, [ 'user', 'blog' ], true ) ) {
				$signup_for = 'user';
			}

			if ( 'blog' === $signup_for && ! $network_allows_blog ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Site registration is not allowed on this network.', 'elementor-must-have-addons' ) ] );
			}
			if ( 'user' === $signup_for && ! $network_allows_user ) {
				wp_send_json_error( [ 'message' => esc_html__( 'User registration is not allowed on this network.', 'elementor-must-have-addons' ) ] );
			}

			// Validate username + email with core Multisite rules.
			$result = wpmu_validate_user_signup( $user_login, $user_email );
			if ( is_wp_error( $result['errors'] ) && $result['errors']->has_errors() ) {
				wp_send_json_error( [ 'message' => $result['errors']->get_error_message() ] );
			}
			$user_login = $result['user_name'];
			$user_email = $result['user_email'];

			$domain = '';
			$path   = '';
			$title  = '';

			if ( 'blog' === $signup_for ) {
				if ( empty( $blogname ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Site name is required.', 'elementor-must-have-addons' ) ] );
				}
				if ( empty( $blog_title ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Site title is required.', 'elementor-must-have-addons' ) ] );
				}

				$blog_result = wpmu_validate_blog_signup( $blogname, $blog_title );
				if ( is_wp_error( $blog_result['errors'] ) && $blog_result['errors']->has_errors() ) {
					wp_send_json_error( [ 'message' => $blog_result['errors']->get_error_message() ] );
				}

				$domain = $blog_result['domain'];
				$path   = $blog_result['path'];
				$title  = $blog_result['blog_title'];
			}

			// Create user immediately (admin-style), not pending signup activation.
			$random_password = wp_generate_password( 24, true, true );
			$user_id         = wpmu_create_user( $user_login, $random_password, $user_email );

			if ( ! $user_id ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Could not create user. Username or email may already be taken.', 'elementor-must-have-addons' ) ] );
			}

			// Optional site creation (same idea as admin creating a site for a user).
			if ( 'blog' === $signup_for ) {
				if ( ! function_exists( 'wpmu_create_blog' ) ) {
					require_once ABSPATH . WPINC . '/ms-functions.php';
				}

				$blog_id = wpmu_create_blog(
					$domain,
					$path,
					$title,
					$user_id,
					[ 'public' => 1 ],
					get_current_network_id()
				);

				if ( is_wp_error( $blog_id ) ) {
					// Keep the user account; report site error clearly.
					wp_send_json_error(
						[
							'message' => sprintf(
								/* translators: %s: error message */
								esc_html__( 'User was created, but site creation failed: %s. They can still set a password from the email.', 'elementor-must-have-addons' ),
								$blog_id->get_error_message()
							),
						]
					);
				}
			}

			// Same email type as Network Admin → Users → Add New User (set password link).
			self::send_set_password_notification( $user_id );

			$logged = [
				'user_login' => $user_login,
				'user_email' => $user_email,
				'signup_for' => $signup_for,
			];
			if ( 'blog' === $signup_for ) {
				$logged['blogname']   = $blogname;
				$logged['blog_title'] = $blog_title;
			}

			self::log_submission( $form_id, $form_name, $logged );
			wp_send_json_success( [ 'message' => $success_msg ] );
		}

		// ——— Single site: native register_new_user (email with set-password link) ———
		if ( ! get_option( 'users_can_register' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Registration is currently disabled.', 'elementor-must-have-addons' ) ] );
		}

		if ( ! function_exists( 'register_new_user' ) ) {
			require_once ABSPATH . 'wp-includes/user.php';
		}

		$user_id = register_new_user( $user_login, $user_email );
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( [ 'message' => $user_id->get_error_message() ] );
		}

		self::log_submission(
			$form_id,
			$form_name,
			[
				'user_login' => $user_login,
				'user_email' => $user_email,
			]
		);

		wp_send_json_success( [ 'message' => $success_msg ] );
	}

	/**
	 * Email the user a "set your password" link (wp-login.php?action=rp),
	 * matching Network Admin → Add User behavior — not the Multisite activation page.
	 *
	 * @param int $user_id User ID.
	 */
	private static function send_set_password_notification( $user_id ) {
		$user_id = (int) $user_id;
		if ( $user_id <= 0 ) {
			return;
		}

		// Prefer core helper used by admin "Send User Notification".
		if ( function_exists( 'wp_send_new_user_notifications' ) ) {
			wp_send_new_user_notifications( $user_id, 'user' );
			return;
		}

		// Fallback for older WP.
		if ( function_exists( 'wp_new_user_notification' ) ) {
			wp_new_user_notification( $user_id, null, 'user' );
		}
	}

	/**
	 * Log a form submission row.
	 *
	 * @param string $form_id   Form ID.
	 * @param string $form_name Form name.
	 * @param array  $fields    Fields to store.
	 */
	private static function log_submission( $form_id, $form_name, $fields ) {
		self::maybe_create_table();
		global $wpdb;
		$table_name = $wpdb->prefix . 'emha_submissions';
		$user_ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		$wpdb->insert(
			$table_name,
			[
				'form_id'   => $form_id,
				'form_name' => $form_name,
				'fields'    => wp_json_encode( $fields ),
				'user_ip'   => $user_ip,
			],
			[ '%s', '%s', '%s', '%s' ]
		);
	}

	/**
	 * Recursively find a widget by Elementor element ID.
	 *
	 * @param array  $elements Elements tree.
	 * @param string $form_id  Widget ID.
	 * @return array|null
	 */
	private static function find_widget_data( $elements, $form_id ) {
		if ( empty( $elements ) || ! is_array( $elements ) ) {
			return null;
		}

		foreach ( $elements as $element ) {
			if ( isset( $element['elType'], $element['id'] ) && 'widget' === $element['elType'] && $element['id'] === $form_id ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) ) {
				$found = self::find_widget_data( $element['elements'], $form_id );
				if ( $found ) {
					return $found;
				}
			}
		}

		return null;
	}
}

new EMHA_Form_Handler();
