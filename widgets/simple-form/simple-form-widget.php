<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Simple_Form_Widget extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
		
		// Register AJAX handler for submissions
		add_action( 'wp_ajax_emha_submit_form', [ $this, 'handle_form_submission' ] );
		add_action( 'wp_ajax_nopriv_emha_submit_form', [ $this, 'handle_form_submission' ] );
	}

	public function get_name() {
		return 'emha-simple-form';
	}

	public function get_title() {
		return esc_html__( 'Simple Submission Form', 'elementor-must-have-addons' );
	}

	public function get_icon() {
		return 'eicon-mail';
	}

	public function get_categories() {
		return [ 'emha-category' ];
	}

	public function get_script_depends() {
		return [ 'emha-simple-form-script' ];
	}

	public function get_style_depends() {
		return [ 'emha-simple-form-style' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_form_fields',
			[
				'label' => esc_html__( 'Form Fields', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'form_name',
			[
				'label'   => esc_html__( 'Form Name', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Contact Form', 'elementor-must-have-addons' ),
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'field_type',
			[
				'label'   => esc_html__( 'Field Type', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'text',
				'options' => [
					'text'     => esc_html__( 'Text', 'elementor-must-have-addons' ),
					'email'    => esc_html__( 'Email', 'elementor-must-have-addons' ),
					'textarea' => esc_html__( 'Textarea', 'elementor-must-have-addons' ),
					'tel'      => esc_html__( 'Tel', 'elementor-must-have-addons' ),
				],
			]
		);

		$repeater->add_control(
			'field_label',
			[
				'label'   => esc_html__( 'Label', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Field Label', 'elementor-must-have-addons' ),
			]
		);

		$repeater->add_control(
			'field_placeholder',
			[
				'label'   => esc_html__( 'Placeholder', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Enter value...', 'elementor-must-have-addons' ),
			]
		);

		$repeater->add_control(
			'field_required',
			[
				'label'     => esc_html__( 'Required', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'elementor-must-have-addons' ),
				'label_off' => esc_html__( 'No', 'elementor-must-have-addons' ),
				'return_value' => 'yes',
				'default'   => 'no',
			]
		);

		$this->add_control(
			'form_fields',
			[
				'label'       => esc_html__( 'Fields List', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'field_type'  => 'text',
						'field_label' => esc_html__( 'Full Name', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'John Doe', 'elementor-must-have-addons' ),
						'field_required' => 'yes',
					],
					[
						'field_type'  => 'email',
						'field_label' => esc_html__( 'Email Address', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'john@example.com', 'elementor-must-have-addons' ),
						'field_required' => 'yes',
					],
					[
						'field_type'  => 'textarea',
						'field_label' => esc_html__( 'Message', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'How can we help you?', 'elementor-must-have-addons' ),
						'field_required' => 'yes',
					],
				],
				'title_field' => '{{{ field_label }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_submit_settings',
			[
				'label' => esc_html__( 'Submission Actions', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'submit_btn_text',
			[
				'label'   => esc_html__( 'Button Text', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Send Message', 'elementor-must-have-addons' ),
			]
		);

		$this->add_control(
			'admin_email',
			[
				'label'       => esc_html__( 'Recipient Email', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => get_option( 'admin_email' ),
				'description' => esc_html__( 'Submissions will be sent to this email address.', 'elementor-must-have-addons' ),
			]
		);

		$this->add_control(
			'email_subject',
			[
				'label'   => esc_html__( 'Email Subject', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'New Form Submission', 'elementor-must-have-addons' ),
			]
		);

		$this->add_control(
			'success_message',
			[
				'label'   => esc_html__( 'Success Message', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Your submission was sent successfully!', 'elementor-must-have-addons' ),
			]
		);

		$this->end_controls_section();

		// Styling Options
		$this->start_controls_section(
			'section_form_style',
			[
				'label' => esc_html__( 'Form & Button Styling', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'form_bg',
			[
				'label'     => esc_html__( 'Form Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.03)',
				'selectors' => [
					'{{WRAPPER}} .emha-form-wrapper' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg',
			[
				'label'     => esc_html__( 'Button Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#c99642',
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => esc_html__( 'Button Text Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$fields   = $settings['form_fields'];
		$form_id  = 'emha-form-' . $this->get_id();
		?>
		<div class="emha-form-wrapper">
			<form id="<?php echo esc_attr( $form_id ); ?>" class="emha-ajax-form" method="post">
				<input type="hidden" name="action" value="emha_submit_form">
				<input type="hidden" name="form_id" value="<?php echo esc_attr( $this->get_id() ); ?>">
				<input type="hidden" name="form_name" value="<?php echo esc_attr( $settings['form_name'] ); ?>">

				<div class="emha-form-fields-container">
					<?php foreach ( $fields as $index => $field ) : 
						$field_id = 'field_' . $index;
						$required = ( 'yes' === $field['field_required'] ) ? 'required' : '';
						?>
						<div class="emha-form-group">
							<label class="emha-form-label" for="<?php echo esc_attr( $field_id ); ?>">
								<?php echo esc_html( $field['field_label'] ); ?>
								<?php if ( $required ) : ?><span class="emha-required-asterisk">*</span><?php endif; ?>
							</label>

							<?php if ( 'textarea' === $field['field_type'] ) : ?>
								<textarea class="emha-form-control" 
									name="fields[<?php echo esc_attr( sanitize_title( $field['field_label'] ) ); ?>]"
									id="<?php echo esc_attr( $field_id ); ?>"
									placeholder="<?php echo esc_attr( $field['field_placeholder'] ); ?>"
									<?php echo esc_attr( $required ); ?>></textarea>
							<?php else : ?>
								<input class="emha-form-control" 
									type="<?php echo esc_attr( $field['field_type'] ); ?>"
									name="fields[<?php echo esc_attr( sanitize_title( $field['field_label'] ) ); ?>]"
									id="<?php echo esc_attr( $field_id ); ?>"
									placeholder="<?php echo esc_attr( $field['field_placeholder'] ); ?>"
									<?php echo esc_attr( $required ); ?>>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="emha-form-submit-container">
					<button type="submit" class="emha-form-submit-btn">
						<span class="emha-submit-text"><?php echo esc_html( $settings['submit_btn_text'] ); ?></span>
						<span class="emha-submit-loader"></span>
					</button>
				</div>

				<div class="emha-form-response-msg"></div>
			</form>
		</div>
		<?php
	}

	public function handle_form_submission() {
		// Verify nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'emha_form_nonce' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'elementor-must-have-addons' ) ] );
		}

		$form_id   = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : '';
		$form_name = isset( $_POST['form_name'] ) ? sanitize_text_field( $_POST['form_name'] ) : 'Simple Form';
		$raw_fields = isset( $_POST['fields'] ) ? $_POST['fields'] : [];

		if ( empty( $raw_fields ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please fill out the form.', 'elementor-must-have-addons' ) ] );
		}

		$fields = [];
		foreach ( $raw_fields as $key => $val ) {
			$fields[ sanitize_text_field( $key ) ] = sanitize_textarea_field( $val );
		}

		// Retrieve widget settings to find recipient email & subject
		$recipient = get_option( 'admin_email' );
		$subject   = esc_html__( 'New Form Submission', 'elementor-must-have-addons' );
		$success_msg = esc_html__( 'Your submission was sent successfully!', 'elementor-must-have-addons' );

		// Query Elementor meta data for custom settings if available
		if ( ! empty( $_POST['post_id'] ) ) {
			$post_id = intval( $_POST['post_id'] );
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$elements_data = $document->get_elements_data();
				$widget_data = $this->find_widget_data( $elements_data, $form_id );
				if ( $widget_data && isset( $widget_data['settings'] ) ) {
					$settings = $widget_data['settings'];
					if ( ! empty( $settings['admin_email'] ) ) {
						$recipient = $settings['admin_email'];
					}
					if ( ! empty( $settings['email_subject'] ) ) {
						$subject = $settings['email_subject'];
					}
					if ( ! empty( $settings['success_message'] ) ) {
						$success_msg = $settings['success_message'];
					}
				}
			}
		}

		// Log to Database
		global $wpdb;
		$table_name = $wpdb->prefix . 'emha_submissions';
		$inserted = $wpdb->insert(
			$table_name,
			[
				'form_id'   => $form_id,
				'form_name' => $form_name,
				'fields'    => json_encode( $fields ),
				'user_ip'   => $_SERVER['REMOTE_ADDR'],
			],
			[ '%s', '%s', '%s', '%s' ]
		);

		// Send Email
		$email_content = "New submission from: " . $form_name . "\n\n";
		foreach ( $fields as $label => $value ) {
			$email_content .= ucfirst( $label ) . ": " . $value . "\n";
		}
		$email_content .= "\nSubmitted on: " . current_time( 'mysql' ) . "\n";
		$email_content .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";

		$headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
		$mail_sent = wp_mail( $recipient, $subject, $email_content, $headers );

		if ( $inserted || $mail_sent ) {
			wp_send_json_success( [ 'message' => $success_msg ] );
		} else {
			wp_send_json_error( [ 'message' => esc_html__( 'Failed to process submission. Please try again.', 'elementor-must-have-addons' ) ] );
		}
	}

	private function find_widget_data( $elements, $form_id ) {
		foreach ( $elements as $element ) {
			if ( isset( $element['elType'] ) && 'widget' === $element['elType'] && isset( $element['id'] ) && $element['id'] === $form_id ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) ) {
				$found = $this->find_widget_data( $element['elements'], $form_id );
				if ( $found ) {
					return $found;
				}
			}
		}
		return null;
	}
}
