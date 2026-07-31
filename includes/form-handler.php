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
			'form_name'       => isset( $settings['form_name'] ) ? sanitize_text_field( $settings['form_name'] ) : 'Contact Form',
			'admin_email'     => isset( $settings['admin_email'] ) ? sanitize_email( $settings['admin_email'] ) : get_option( 'admin_email' ),
			'email_subject'   => isset( $settings['email_subject'] ) ? sanitize_text_field( $settings['email_subject'] ) : 'New Form Submission',
			'success_message' => isset( $settings['success_message'] ) ? sanitize_text_field( $settings['success_message'] ) : 'Your submission was sent successfully!',
			'updated_at'      => current_time( 'mysql' ),
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
		if ( $form_id && is_array( $configs ) && ! empty( $configs[ $form_id ] ) ) {
			$config = $configs[ $form_id ];
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
		} elseif ( ! empty( $_POST['post_id'] ) && class_exists( '\Elementor\Plugin' ) ) {
			// Fallback: walk Elementor document data.
			$post_id  = intval( $_POST['post_id'] );
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$widget_data = self::find_widget_data( $document->get_elements_data(), $form_id );
				if ( $widget_data && ! empty( $widget_data['settings'] ) ) {
					$settings = $widget_data['settings'];
					if ( ! empty( $settings['admin_email'] ) && is_email( $settings['admin_email'] ) ) {
						$recipient = sanitize_email( $settings['admin_email'] );
					}
					if ( ! empty( $settings['email_subject'] ) ) {
						$subject = sanitize_text_field( $settings['email_subject'] );
					}
					if ( ! empty( $settings['success_message'] ) ) {
						$success_msg = sanitize_text_field( $settings['success_message'] );
					}
				}
			}
		}

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
