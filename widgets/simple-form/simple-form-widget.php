<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Simple_Form_Widget extends \Elementor\Widget_Base {

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
				'label'        => esc_html__( 'Required', 'elementor-must-have-addons' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elementor-must-have-addons' ),
				'label_off'    => esc_html__( 'No', 'elementor-must-have-addons' ),
				'return_value' => 'yes',
				'default'      => 'no',
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
						'field_type'        => 'text',
						'field_label'       => esc_html__( 'Full Name', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'John Doe', 'elementor-must-have-addons' ),
						'field_required'    => 'yes',
					],
					[
						'field_type'        => 'email',
						'field_label'       => esc_html__( 'Email Address', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'john@example.com', 'elementor-must-have-addons' ),
						'field_required'    => 'yes',
					],
					[
						'field_type'        => 'textarea',
						'field_label'       => esc_html__( 'Message', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'How can we help you?', 'elementor-must-have-addons' ),
						'field_required'    => 'yes',
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
		// Ensure assets load even if Elementor dependency resolution is delayed.
		wp_enqueue_script( 'emha-simple-form-script' );
		wp_enqueue_style( 'emha-simple-form-style' );

		$settings = $this->get_settings_for_display();
		$fields   = ! empty( $settings['form_fields'] ) ? $settings['form_fields'] : [];
		$widget_id = $this->get_id();
		$form_dom_id = 'emha-form-' . $widget_id;
		$post_id = get_the_ID();

		// Cache form config for reliable AJAX handling (email, subject, messages).
		if ( class_exists( 'EMHA_Form_Handler' ) ) {
			EMHA_Form_Handler::save_form_config( $widget_id, $settings );
		}
		?>
		<div class="emha-form-wrapper">
			<form id="<?php echo esc_attr( $form_dom_id ); ?>"
				class="emha-ajax-form"
				method="post"
				action=""
				novalidate="novalidate">
				<input type="hidden" name="action" value="emha_submit_form">
				<input type="hidden" name="form_id" value="<?php echo esc_attr( $widget_id ); ?>">
				<input type="hidden" name="form_name" value="<?php echo esc_attr( $settings['form_name'] ); ?>">
				<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ? $post_id : 0 ); ?>">
				<?php wp_nonce_field( 'emha_form_nonce', '_wpnonce', false ); ?>

				<div class="emha-form-fields-container">
					<?php foreach ( $fields as $index => $field ) :
						$field_id   = 'field_' . $widget_id . '_' . $index;
						$is_required = ( isset( $field['field_required'] ) && 'yes' === $field['field_required'] );
						$field_key  = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
						$field_type = ! empty( $field['field_type'] ) ? $field['field_type'] : 'text';
						?>
						<div class="emha-form-group">
							<label class="emha-form-label" for="<?php echo esc_attr( $field_id ); ?>">
								<?php echo esc_html( $field['field_label'] ); ?>
								<?php if ( $is_required ) : ?><span class="emha-required-asterisk">*</span><?php endif; ?>
							</label>

							<?php if ( 'textarea' === $field_type ) : ?>
								<textarea class="emha-form-control"
									name="fields[<?php echo esc_attr( $field_key ); ?>]"
									id="<?php echo esc_attr( $field_id ); ?>"
									placeholder="<?php echo esc_attr( $field['field_placeholder'] ); ?>"
									<?php echo $is_required ? 'required' : ''; ?>></textarea>
							<?php else : ?>
								<input class="emha-form-control"
									type="<?php echo esc_attr( $field_type ); ?>"
									name="fields[<?php echo esc_attr( $field_key ); ?>]"
									id="<?php echo esc_attr( $field_id ); ?>"
									placeholder="<?php echo esc_attr( $field['field_placeholder'] ); ?>"
									<?php echo $is_required ? 'required' : ''; ?>>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="emha-form-submit-container">
					<button type="submit" class="emha-form-submit-btn">
						<span class="emha-submit-text"><?php echo esc_html( $settings['submit_btn_text'] ); ?></span>
						<span class="emha-submit-loader" aria-hidden="true"></span>
					</button>
				</div>

				<div class="emha-form-response-msg" role="status" aria-live="polite"></div>
			</form>
		</div>
		<?php
	}
}
