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

		$configs[ $form_id ] = [
			'form_name'             => isset( $settings['form_name'] ) ? sanitize_text_field( $settings['form_name'] ) : 'Contact Form',
			'admin_email'           => isset( $settings['admin_email'] ) ? sanitize_email( $settings['admin_email'] ) : get_option( 'admin_email' ),
			'email_subject'         => isset( $settings['email_subject'] ) ? sanitize_text_field( $settings['email_subject'] ) : 'New Form Submission',
			'success_message'       => isset( $settings['success_message'] ) ? sanitize_text_field( $settings['success_message'] ) : 'Your submission was sent successfully!',
			'form_mode'             => isset( $settings['form_mode'] ) ? sanitize_text_field( $settings['form_mode'] ) : 'contact',
			'user_role'             => isset( $settings['user_role'] ) ? sanitize_text_field( $settings['user_role'] ) : 'subscriber',
			'auto_login'            => isset( $settings['auto_login'] ) ? sanitize_text_field( $settings['auto_login'] ) : 'yes',
			'multisite_create_site' => isset( $settings['multisite_create_site'] ) ? sanitize_text_field( $settings['multisite_create_site'] ) : 'no',
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
					$config = [
						'form_name'             => isset( $settings['form_name'] ) ? sanitize_text_field( $settings['form_name'] ) : 'Contact Form',
						'admin_email'           => isset( $settings['admin_email'] ) ? sanitize_email( $settings['admin_email'] ) : get_option( 'admin_email' ),
						'email_subject'         => isset( $settings['email_subject'] ) ? sanitize_text_field( $settings['email_subject'] ) : 'New Form Submission',
						'success_message'       => isset( $settings['success_message'] ) ? sanitize_text_field( $settings['success_message'] ) : 'Your submission was sent successfully!',
						'form_mode'             => isset( $settings['form_mode'] ) ? sanitize_text_field( $settings['form_mode'] ) : 'contact',
						'user_role'             => isset( $settings['user_role'] ) ? sanitize_text_field( $settings['user_role'] ) : 'subscriber',
						'auto_login'            => isset( $settings['auto_login'] ) ? sanitize_text_field( $settings['auto_login'] ) : 'yes',
						'multisite_create_site' => isset( $settings['multisite_create_site'] ) ? sanitize_text_field( $settings['multisite_create_site'] ) : 'no',
						'form_fields'           => isset( $settings['form_fields'] ) ? $settings['form_fields'] : [],
					];
				}
			}
		}

		$form_mode             = isset( $config['form_mode'] ) ? $config['form_mode'] : 'contact';
		$user_role             = isset( $config['user_role'] ) ? $config['user_role'] : 'subscriber';
		$auto_login            = isset( $config['auto_login'] ) ? $config['auto_login'] : 'yes';
		$multisite_create_site = isset( $config['multisite_create_site'] ) ? $config['multisite_create_site'] : 'no';
		$form_fields           = isset( $config['form_fields'] ) ? $config['form_fields'] : [];

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

		$fields_map = [];
		if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
			foreach ( $form_fields as $index => $field ) {
				$field_key = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
				$map_to    = ! empty( $field['map_to'] ) ? $field['map_to'] : 'none';
				if ( $map_to !== 'none' ) {
					$fields_map[ $map_to ] = $field_key;
				}
			}
		}

		// User Registration Mode
		if ( 'register' === $form_mode ) {
			$user_login = '';
			$user_email = '';
			$user_pass  = '';
			$first_name = '';
			$last_name  = '';
			$site_path  = '';
			$site_title = '';

			if ( isset( $fields_map['user_login'] ) && isset( $fields[ $fields_map['user_login'] ] ) ) {
				$user_login = sanitize_user( $fields[ $fields_map['user_login'] ] );
			}
			if ( isset( $fields_map['user_email'] ) && isset( $fields[ $fields_map['user_email'] ] ) ) {
				$user_email = sanitize_email( $fields[ $fields_map['user_email'] ] );
			}
			if ( isset( $fields_map['user_pass'] ) && isset( $fields[ $fields_map['user_pass'] ] ) ) {
				$user_pass = $fields[ $fields_map['user_pass'] ];
			}
			if ( isset( $fields_map['first_name'] ) && isset( $fields[ $fields_map['first_name'] ] ) ) {
				$first_name = sanitize_text_field( $fields[ $fields_map['first_name'] ] );
			}
			if ( isset( $fields_map['last_name'] ) && isset( $fields[ $fields_map['last_name'] ] ) ) {
				$last_name = sanitize_text_field( $fields[ $fields_map['last_name'] ] );
			}
			if ( isset( $fields_map['site_path'] ) && isset( $fields[ $fields_map['site_path'] ] ) ) {
				$site_path = sanitize_title( $fields[ $fields_map['site_path'] ] );
			}
			if ( isset( $fields_map['site_title'] ) && isset( $fields[ $fields_map['site_title'] ] ) ) {
				$site_title = sanitize_text_field( $fields[ $fields_map['site_title'] ] );
			}

			// Smart Fallbacks
			if ( empty( $user_email ) ) {
				if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
					foreach ( $form_fields as $index => $field ) {
						$field_key = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
						$field_type = ! empty( $field['field_type'] ) ? $field['field_type'] : 'text';
						if ( 'email' === $field_type && ! empty( $fields[ $field_key ] ) ) {
							$user_email = sanitize_email( $fields[ $field_key ] );
							break;
						}
					}
				}
			}

			if ( empty( $user_login ) && ! empty( $user_email ) ) {
				if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
					foreach ( $form_fields as $index => $field ) {
						$field_key = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
						$label_lower = strtolower( ! empty( $field['field_label'] ) ? $field['field_label'] : '' );
						if ( ( strpos( $label_lower, 'username' ) !== false || strpos( $label_lower, 'user name' ) !== false ) && ! empty( $fields[ $field_key ] ) ) {
							$user_login = sanitize_user( $fields[ $field_key ] );
							break;
						}
					}
				}
				if ( empty( $user_login ) ) {
					$parts = explode( '@', $user_email );
					$user_login = sanitize_user( $parts[0] );
				}
			}

			if ( empty( $user_pass ) ) {
				if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
					foreach ( $form_fields as $index => $field ) {
						$field_key = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
						$field_type = ! empty( $field['field_type'] ) ? $field['field_type'] : 'text';
						$label_lower = strtolower( ! empty( $field['field_label'] ) ? $field['field_label'] : '' );
						if ( ( 'password' === $field_type || strpos( $label_lower, 'password' ) !== false ) && ! empty( $fields[ $field_key ] ) ) {
							$user_pass = $fields[ $field_key ];
							break;
						}
					}
				}
				if ( empty( $user_pass ) ) {
					$user_pass = wp_generate_password( 12, false );
				}
			}

			if ( empty( $site_path ) && is_multisite() && 'yes' === $multisite_create_site ) {
				if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
					foreach ( $form_fields as $index => $field ) {
						$field_key = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
						$label_lower = strtolower( ! empty( $field['field_label'] ) ? $field['field_label'] : '' );
						if ( ( strpos( $label_lower, 'site path' ) !== false || strpos( $label_lower, 'domain' ) !== false || strpos( $label_lower, 'subdomain' ) !== false ) && ! empty( $fields[ $field_key ] ) ) {
							$site_path = sanitize_title( $fields[ $field_key ] );
							break;
						}
					}
				}
				if ( empty( $site_path ) ) {
					$site_path = $user_login;
				}
			}

			if ( empty( $site_title ) && is_multisite() && 'yes' === $multisite_create_site ) {
				if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
					foreach ( $form_fields as $index => $field ) {
						$field_key = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
						$label_lower = strtolower( ! empty( $field['field_label'] ) ? $field['field_label'] : '' );
						if ( ( strpos( $label_lower, 'site title' ) !== false || strpos( $label_lower, 'website title' ) !== false ) && ! empty( $fields[ $field_key ] ) ) {
							$site_title = sanitize_text_field( $fields[ $field_key ] );
							break;
						}
					}
				}
				if ( empty( $site_title ) ) {
					$site_title = ucfirst( $user_login ) . "'s Site";
				}
			}

			// Validate inputs
			if ( empty( $user_email ) || ! is_email( $user_email ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Please enter a valid email address.', 'elementor-must-have-addons' ) ] );
			}
			if ( email_exists( $user_email ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'This email address is already registered.', 'elementor-must-have-addons' ) ] );
			}

			if ( empty( $user_login ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Username is required.', 'elementor-must-have-addons' ) ] );
			}
			if ( ! validate_username( $user_login ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Please enter a valid username.', 'elementor-must-have-addons' ) ] );
			}
			if ( username_exists( $user_login ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'This username is already taken.', 'elementor-must-have-addons' ) ] );
			}

			if ( strlen( $user_pass ) < 6 ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Password must be at least 6 characters.', 'elementor-must-have-addons' ) ] );
			}

			// Multisite validation
			$new_domain = '';
			$new_path   = '';
			if ( is_multisite() && 'yes' === $multisite_create_site ) {
				if ( empty( $site_path ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Site path is required for site creation.', 'elementor-must-have-addons' ) ] );
				}

				$site_path = preg_replace( '/[^a-z0-9\-]/', '', strtolower( $site_path ) );
				if ( strlen( $site_path ) < 4 ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Site path must be at least 4 characters long and contain only alphanumeric characters or hyphens.', 'elementor-must-have-addons' ) ] );
				}

				$reserved_paths = [ 'main', 'wp-admin', 'wp-content', 'wp-includes', 'files' ];
				if ( in_array( $site_path, $reserved_paths, true ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'The site path is reserved.', 'elementor-must-have-addons' ) ] );
				}

				if ( ! function_exists( 'is_subdomain_install' ) ) {
					require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				}

				$current_network = get_network();
				$domain          = $current_network->domain;
				$path            = $current_network->path;

				if ( is_subdomain_install() ) {
					$new_domain = $site_path . '.' . $domain;
					$new_path   = '/';
				} else {
					$new_domain = $domain;
					$new_path   = $path . $site_path . '/';
				}

				if ( domain_exists( $new_domain, $new_path ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'The site path or domain already exists.', 'elementor-must-have-addons' ) ] );
				}
			}

			// Create User
			$user_data = [
				'user_login' => $user_login,
				'user_email' => $user_email,
				'user_pass'  => $user_pass,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => $user_role,
			];

			$user_id = wp_insert_user( $user_data );

			if ( is_wp_error( $user_id ) ) {
				wp_send_json_error( [ 'message' => $user_id->get_error_message() ] );
			}

			// Save custom meta fields
			foreach ( $fields as $key => $val ) {
				if ( isset( $fields_map['user_login'] ) && $key === $fields_map['user_login'] ) {
					continue;
				}
				if ( isset( $fields_map['user_email'] ) && $key === $fields_map['user_email'] ) {
					continue;
				}
				if ( isset( $fields_map['user_pass'] ) && $key === $fields_map['user_pass'] ) {
					continue;
				}
				if ( isset( $fields_map['first_name'] ) && $key === $fields_map['first_name'] ) {
					continue;
				}
				if ( isset( $fields_map['last_name'] ) && $key === $fields_map['last_name'] ) {
					continue;
				}
				if ( isset( $fields_map['site_path'] ) && $key === $fields_map['site_path'] ) {
					continue;
				}
				if ( isset( $fields_map['site_title'] ) && $key === $fields_map['site_title'] ) {
					continue;
				}

				update_user_meta( $user_id, 'emha_field_' . $key, $val );
			}

			// Create Site if enabled
			$site_created_message = '';
			if ( is_multisite() && 'yes' === $multisite_create_site ) {
				if ( ! function_exists( 'wpmu_create_blog' ) ) {
					require_once ABSPATH . 'wp-includes/ms-functions.php';
				}

				$blog_id = wpmu_create_blog( $new_domain, $new_path, $site_title, $user_id, [ 'public' => 1 ] );

				if ( is_wp_error( $blog_id ) ) {
					wp_delete_user( $user_id );
					wp_send_json_error( [ 'message' => sprintf( __( 'User created but site creation failed: %s', 'elementor-must-have-addons' ), $blog_id->get_error_message() ) ] );
				} else {
					$site_created_message = sprintf( __( ' and site created at %s', 'elementor-must-have-addons' ), esc_url( get_home_url( $blog_id ) ) );
				}
			}

			// Auto login
			if ( 'yes' === $auto_login ) {
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id, true );
				$user_obj = get_user_by( 'id', $user_id );
				if ( $user_obj ) {
					do_action( 'wp_login', $user_obj->user_login, $user_obj );
				}
			}

			// Mask password for logged fields
			$logged_fields = $fields;
			if ( isset( $fields_map['user_pass'] ) && isset( $logged_fields[ $fields_map['user_pass'] ] ) ) {
				$logged_fields[ $fields_map['user_pass'] ] = '********';
			}

			// Log registration submission
			self::maybe_create_table();
			global $wpdb;
			$table_name = $wpdb->prefix . 'emha_submissions';
			$user_ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			$wpdb->insert(
				$table_name,
				[
					'form_id'   => $form_id,
					'form_name' => $form_name,
					'fields'    => wp_json_encode( $logged_fields ),
					'user_ip'   => $user_ip,
				],
				[ '%s', '%s', '%s', '%s' ]
			);

			wp_send_json_success( [ 'message' => $success_msg . $site_created_message ] );
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
