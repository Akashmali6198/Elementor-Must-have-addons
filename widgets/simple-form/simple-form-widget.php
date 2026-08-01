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

		// ——— Form mode & name (always visible) ———
		$this->start_controls_section(
			'section_form_settings',
			[
				'label' => esc_html__( 'Form Settings', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'form_mode',
			[
				'label'   => esc_html__( 'Form Mode', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'contact',
				'options' => [
					'contact'  => esc_html__( 'Contact Form', 'elementor-must-have-addons' ),
					'register' => esc_html__( 'User Registration', 'elementor-must-have-addons' ),
				],
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

		$this->end_controls_section();

		// ——— Contact-only custom fields (hidden entirely in Registration mode) ———
		$this->start_controls_section(
			'section_form_fields',
			[
				'label'     => esc_html__( 'Form Fields', 'elementor-must-have-addons' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'form_mode' => 'contact',
				],
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
					'password' => esc_html__( 'Password', 'elementor-must-have-addons' ),
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

		// ——— Registration settings (WordPress default fields only — no custom field UI) ———
		$this->start_controls_section(
			'section_registration_settings',
			[
				'label'     => esc_html__( 'User Registration Settings', 'elementor-must-have-addons' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'form_mode' => 'register',
				],
			]
		);

		$this->add_control(
			'registration_fields_notice',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<div class="elementor-control-field-description" style="padding:10px 12px;background:#f0f6fc;border-left:3px solid #2271b1;border-radius:2px;line-height:1.45;">' .
					'<strong>' . esc_html__( 'Fixed WordPress signup fields', 'elementor-must-have-addons' ) . '</strong><br>' .
					esc_html__( 'Username and Email are built-in (plus site options on Multisite). Custom fields cannot be added. After signup the user gets the same “set your password” email as when an admin adds a user.', 'elementor-must-have-addons' ) .
					'</div>',
			]
		);

		if ( is_multisite() ) {
			$this->add_control(
				'multisite_signup_type',
				[
					'label'       => esc_html__( 'Signup Type', 'elementor-must-have-addons' ),
					'type'        => \Elementor\Controls_Manager::SELECT,
					'default'     => 'both',
					'options'     => [
						'user' => esc_html__( 'Username only (no site)', 'elementor-must-have-addons' ),
						'blog' => esc_html__( 'Create a site (like wp-signup.php)', 'elementor-must-have-addons' ),
						'both' => esc_html__( 'Let visitor choose (user or site)', 'elementor-must-have-addons' ),
					],
					'description' => esc_html__( 'Uses WordPress Multisite signup (activation email). Always respects Network Admin → Settings → Network Settings → Allow new registrations.', 'elementor-must-have-addons' ),
				]
			);
		}

		$this->add_control(
			'register_success_message',
			[
				'label'       => esc_html__( 'Success Message', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Signup successful! Please check your email for a link to set your password and log in.', 'elementor-must-have-addons' ),
				'description' => esc_html__( 'Shown after signup. Email uses the same “set your password” link as when an admin adds a user (not the Multisite activation page).', 'elementor-must-have-addons' ),
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
				'condition'   => [
					'form_mode' => 'contact',
				],
			]
		);

		$this->add_control(
			'email_subject',
			[
				'label'     => esc_html__( 'Email Subject', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'New Form Submission', 'elementor-must-have-addons' ),
				'condition' => [
					'form_mode' => 'contact',
				],
			]
		);

		$this->add_control(
			'success_message',
			[
				'label'     => esc_html__( 'Success Message', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Your submission was sent successfully!', 'elementor-must-have-addons' ),
				'condition' => [
					'form_mode' => 'contact',
				],
			]
		);

		$this->end_controls_section();

		// Styling Options - Container
		$this->start_controls_section(
			'section_form_style',
			[
				'label' => esc_html__( 'Form Container Styling', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'form_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range'      => [
					'px' => [
						'min'  => 100,
						'max'  => 1600,
						'step' => 10,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'form_padding',
			[
				'label'      => esc_html__( 'Padding', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default'    => [
					'top'      => '16',
					'right'    => '16',
					'bottom'   => '16',
					'left'     => '16',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'form_bg',
			[
				'label'     => esc_html__( 'Form Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .emha-form-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'form_border',
				'selector' => '{{WRAPPER}} .emha-form-wrapper',
			]
		);

		$this->add_responsive_control(
			'form_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'form_box_shadow',
				'selector' => '{{WRAPPER}} .emha-form-wrapper',
			]
		);

		$this->add_control(
			'form_backdrop_blur',
			[
				'label'      => esc_html__( 'Backdrop Blur (px)', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-wrapper' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$this->end_controls_section();

		// Styling Options - Labels
		$this->start_controls_section(
			'section_label_style',
			[
				'label' => esc_html__( 'Label Styling', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'label_color',
			[
				'label'     => esc_html__( 'Text Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1d2327',
				'selectors' => [
					'{{WRAPPER}} .emha-form-label' => 'color: {{VALUE}};',
					'{{WRAPPER}} .emha-radio-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .emha-form-label',
			]
		);

		$this->add_responsive_control(
			'label_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'asterisk_color',
			[
				'label'     => esc_html__( 'Required Asterisk Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#d63638',
				'selectors' => [
					'{{WRAPPER}} .emha-required-asterisk' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Styling Options - Input Fields
		$this->start_controls_section(
			'section_input_style',
			[
				'label' => esc_html__( 'Input & Fields Styling', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'input_bg',
			[
				'label'     => esc_html__( 'Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .emha-form-control' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_color',
			[
				'label'     => esc_html__( 'Text Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1d2327',
				'selectors' => [
					'{{WRAPPER}} .emha-form-control' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'placeholder_color',
			[
				'label'     => esc_html__( 'Placeholder Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#757575',
				'selectors' => [
					'{{WRAPPER}} .emha-form-control::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'input_typography',
				'selector' => '{{WRAPPER}} .emha-form-control',
			]
		);

		$this->add_responsive_control(
			'input_padding',
			[
				'label'      => esc_html__( 'Padding', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-control' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'input_border',
				'selector' => '{{WRAPPER}} .emha-form-control',
			]
		);

		$this->add_responsive_control(
			'input_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-control' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_spacing',
			[
				'label'      => esc_html__( 'Fields Spacing', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [
					'unit' => 'px',
					'size' => 12,
				],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'input_border_color_default',
			[
				'label'     => esc_html__( 'Border Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#8c8f94',
				'selectors' => [
					'{{WRAPPER}} .emha-form-control' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_input_focus',
			[
				'label'     => esc_html__( 'Focus State', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'input_focus_bg',
			[
				'label'     => esc_html__( 'Focus Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-control:focus' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_focus_border_color',
			[
				'label'     => esc_html__( 'Focus Border Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-control:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'input_focus_box_shadow',
				'selector' => '{{WRAPPER}} .emha-form-control:focus',
			]
		);

		$this->end_controls_section();

		// Styling Options - Submit Button
		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Button Styling', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_alignment',
			[
				'label'     => esc_html__( 'Alignment', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'elementor-must-have-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'elementor-must-have-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'elementor-must-have-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-container' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_width',
			[
				'label'     => esc_html__( 'Width', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'full',
				'options'   => [
					'full' => esc_html__( 'Full Width', 'elementor-must-have-addons' ),
					'auto' => esc_html__( 'Auto / Custom', 'elementor-must-have-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-btn' => 'width: {{VALUE === "full" ? "100%" : "auto"}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .emha-form-submit-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'elementor-must-have-addons' ),
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'elementor-must-have-addons' ),
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
				'label'     => esc_html__( 'Text Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .emha-form-submit-btn',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-submit-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .emha-form-submit-btn',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'elementor-must-have-addons' ),
			]
		);

		$this->add_control(
			'button_bg_hover_color',
			[
				'label'     => esc_html__( 'Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#dfa84f',
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_text_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_hover_color',
			[
				'label'     => esc_html__( 'Border Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-submit-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} .emha-form-submit-btn:hover',
			]
		);

		$this->add_responsive_control(
			'button_hover_transition',
			[
				'label'      => esc_html__( 'Transition Duration (s)', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					's' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-submit-btn' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Padding', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-submit-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'button_spacing',
			[
				'label'      => esc_html__( 'Button Spacing (Top)', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-submit-container' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Styling Options - Response Messages
		$this->start_controls_section(
			'section_response_style',
			[
				'label' => esc_html__( 'Response Messages Styling', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'msg_typography',
				'selector' => '{{WRAPPER}} .emha-form-response-msg',
			]
		);

		$this->add_control(
			'heading_success_msg',
			[
				'label'     => esc_html__( 'Success Message Styling', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'success_msg_color',
			[
				'label'     => esc_html__( 'Text Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-response-msg.emha-success' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'success_msg_bg',
			[
				'label'     => esc_html__( 'Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-response-msg.emha-success' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'success_msg_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-response-msg.emha-success' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'success_msg_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-response-msg.emha-success' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'success_msg_padding',
			[
				'label'      => esc_html__( 'Padding', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-response-msg.emha-success' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_error_msg',
			[
				'label'     => esc_html__( 'Error Message Styling', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'error_msg_color',
			[
				'label'     => esc_html__( 'Text Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-response-msg.emha-error' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'error_msg_bg',
			[
				'label'     => esc_html__( 'Background Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-response-msg.emha-error' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'error_msg_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .emha-form-response-msg.emha-error' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'error_msg_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-response-msg.emha-error' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'error_msg_padding',
			[
				'label'      => esc_html__( 'Padding', 'elementor-must-have-addons' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .emha-form-response-msg.emha-error' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Whether Multisite network allows blog (site) signups.
	 *
	 * @return bool
	 */
	protected function network_allows_blog_signup() {
		if ( ! is_multisite() ) {
			return false;
		}
		$active = get_site_option( 'registration', 'none' );
		return in_array( $active, [ 'blog', 'all' ], true );
	}

	/**
	 * Whether Multisite network allows user-only signups.
	 *
	 * @return bool
	 */
	protected function network_allows_user_signup() {
		if ( ! is_multisite() ) {
			return (bool) get_option( 'users_can_register' );
		}
		$active = get_site_option( 'registration', 'none' );
		return in_array( $active, [ 'user', 'all' ], true );
	}

	/**
	 * Render fixed WordPress registration / Multisite signup fields.
	 *
	 * @param string $widget_id Widget ID.
	 * @param array  $settings  Widget settings.
	 */
	protected function render_registration_fields( $widget_id, $settings ) {
		$signup_type = ! empty( $settings['multisite_signup_type'] ) ? $settings['multisite_signup_type'] : 'user';
		$is_ms       = is_multisite();
		$allow_blog  = $is_ms && $this->network_allows_blog_signup();
		$allow_user  = $this->network_allows_user_signup();

		// Respect network policy: force blog-only or user-only when needed.
		if ( $is_ms ) {
			$active = get_site_option( 'registration', 'none' );
			if ( 'blog' === $active ) {
				$signup_type = 'blog';
			} elseif ( 'user' === $active && 'blog' === $signup_type ) {
				$signup_type = 'user';
			}
		}

		$show_blog_fields = $is_ms && $allow_blog && in_array( $signup_type, [ 'blog', 'both' ], true );
		$show_signup_for  = $is_ms && $allow_blog && $allow_user && 'both' === $signup_type;
		$blog_always_on   = $is_ms && $allow_blog && 'blog' === $signup_type;

		$uid = 'field_' . $widget_id;
		?>
		<div class="emha-form-group">
			<label class="emha-form-label" for="<?php echo esc_attr( $uid . '_user_login' ); ?>">
				<?php echo esc_html__( 'Username', 'elementor-must-have-addons' ); ?>
				<span class="emha-required-asterisk">*</span>
			</label>
			<input class="emha-form-control"
				type="text"
				name="fields[user_login]"
				id="<?php echo esc_attr( $uid . '_user_login' ); ?>"
				placeholder="<?php echo esc_attr__( 'username', 'elementor-must-have-addons' ); ?>"
				autocomplete="username"
				required
				minlength="4"
				pattern="[a-z0-9]+"
				title="<?php echo esc_attr__( 'Must be at least 4 characters, lowercase letters and numbers only.', 'elementor-must-have-addons' ); ?>">
			<p class="emha-field-description"><?php echo esc_html__( 'Must be at least 4 characters, lowercase letters and numbers only.', 'elementor-must-have-addons' ); ?></p>
		</div>

		<div class="emha-form-group">
			<label class="emha-form-label" for="<?php echo esc_attr( $uid . '_user_email' ); ?>">
				<?php echo esc_html__( 'Email Address', 'elementor-must-have-addons' ); ?>
				<span class="emha-required-asterisk">*</span>
			</label>
			<input class="emha-form-control"
				type="email"
				name="fields[user_email]"
				id="<?php echo esc_attr( $uid . '_user_email' ); ?>"
				placeholder="<?php echo esc_attr__( 'you@example.com', 'elementor-must-have-addons' ); ?>"
				autocomplete="email"
				required>
			<p class="emha-field-description"><?php echo esc_html__( 'Your registration email is sent to this address. Double-check before continuing.', 'elementor-must-have-addons' ); ?></p>
		</div>

		<?php if ( $show_signup_for ) : ?>
			<div class="emha-form-group emha-signup-for-group">
				<span class="emha-form-label"><?php echo esc_html__( 'Create a site or only a username:', 'elementor-must-have-addons' ); ?></span>
				<label class="emha-radio-label">
					<input type="radio" name="fields[signup_for]" value="blog" checked class="emha-signup-for-radio">
					<?php echo esc_html__( 'Gimme a site!', 'elementor-must-have-addons' ); ?>
				</label>
				<label class="emha-radio-label">
					<input type="radio" name="fields[signup_for]" value="user" class="emha-signup-for-radio">
					<?php echo esc_html__( 'Just a username, please.', 'elementor-must-have-addons' ); ?>
				</label>
			</div>
		<?php elseif ( $blog_always_on ) : ?>
			<input type="hidden" name="fields[signup_for]" value="blog">
		<?php else : ?>
			<input type="hidden" name="fields[signup_for]" value="user">
		<?php endif; ?>

		<?php if ( $show_blog_fields ) :
			$current_network = get_network();
			$is_subdomain    = function_exists( 'is_subdomain_install' ) ? is_subdomain_install() : false;
			$site_domain     = $current_network ? $current_network->domain : '';
			?>
			<div class="emha-form-group emha-blog-fields" data-emha-blog-fields="1">
				<label class="emha-form-label" for="<?php echo esc_attr( $uid . '_blogname' ); ?>">
					<?php echo esc_html__( 'Site Name', 'elementor-must-have-addons' ); ?>
					<span class="emha-required-asterisk">*</span>
				</label>
				<?php if ( $is_subdomain ) : ?>
					<div class="emha-site-address-wrap">
						<input class="emha-form-control"
							type="text"
							name="fields[blogname]"
							id="<?php echo esc_attr( $uid . '_blogname' ); ?>"
							placeholder="<?php echo esc_attr__( 'sitename', 'elementor-must-have-addons' ); ?>"
							minlength="4"
							pattern="[a-z0-9\-]+"
							<?php echo $blog_always_on ? 'required' : ''; ?>>
						<span class="emha-site-address-suffix">.<?php echo esc_html( $site_domain ); ?></span>
					</div>
				<?php else : ?>
					<div class="emha-site-address-wrap">
						<span class="emha-site-address-prefix"><?php echo esc_html( untrailingslashit( network_home_url() ) ); ?>/</span>
						<input class="emha-form-control"
							type="text"
							name="fields[blogname]"
							id="<?php echo esc_attr( $uid . '_blogname' ); ?>"
							placeholder="<?php echo esc_attr__( 'sitename', 'elementor-must-have-addons' ); ?>"
							minlength="4"
							pattern="[a-z0-9\-]+"
							<?php echo $blog_always_on ? 'required' : ''; ?>>
					</div>
				<?php endif; ?>
				<p class="emha-field-description"><?php echo esc_html__( 'Must be at least 4 characters, letters and numbers only. Hyphens allowed.', 'elementor-must-have-addons' ); ?></p>
			</div>

			<div class="emha-form-group emha-blog-fields" data-emha-blog-fields="1">
				<label class="emha-form-label" for="<?php echo esc_attr( $uid . '_blog_title' ); ?>">
					<?php echo esc_html__( 'Site Title', 'elementor-must-have-addons' ); ?>
					<span class="emha-required-asterisk">*</span>
				</label>
				<input class="emha-form-control"
					type="text"
					name="fields[blog_title]"
					id="<?php echo esc_attr( $uid . '_blog_title' ); ?>"
					placeholder="<?php echo esc_attr__( 'My Site', 'elementor-must-have-addons' ); ?>"
					<?php echo $blog_always_on ? 'required' : ''; ?>>
			</div>
		<?php endif; ?>
		<?php
	}

	protected function render() {
		// Ensure assets load even if Elementor dependency resolution is delayed.
		wp_enqueue_script( 'emha-simple-form-script' );
		wp_enqueue_style( 'emha-simple-form-style' );

		$settings    = $this->get_settings_for_display();
		$fields      = ! empty( $settings['form_fields'] ) ? $settings['form_fields'] : [];
		$widget_id   = $this->get_id();
		$form_dom_id = 'emha-form-' . $widget_id;
		$post_id     = get_the_ID();
		$form_mode   = ! empty( $settings['form_mode'] ) ? $settings['form_mode'] : 'contact';
		$is_register = ( 'register' === $form_mode );

		// Cache form config for reliable AJAX handling (email, subject, messages, registration, multisite options).
		if ( class_exists( 'EMHA_Form_Handler' ) ) {
			EMHA_Form_Handler::save_form_config( $widget_id, $settings );
		}

		$submit_text = ! empty( $settings['submit_btn_text'] ) ? $settings['submit_btn_text'] : '';
		if ( $is_register && ( empty( $submit_text ) || 'Send Message' === $submit_text ) ) {
			$submit_text = esc_html__( 'Sign Up', 'elementor-must-have-addons' );
		}
		?>
		<div class="emha-form-wrapper<?php echo $is_register ? ' emha-form-mode-register' : ' emha-form-mode-contact'; ?>">
			<form id="<?php echo esc_attr( $form_dom_id ); ?>"
				class="emha-ajax-form"
				method="post"
				action=""
				novalidate="novalidate"
				data-form-mode="<?php echo esc_attr( $form_mode ); ?>">
				<input type="hidden" name="action" value="emha_submit_form">
				<input type="hidden" name="form_id" value="<?php echo esc_attr( $widget_id ); ?>">
				<input type="hidden" name="form_name" value="<?php echo esc_attr( $settings['form_name'] ); ?>">
				<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ? $post_id : 0 ); ?>">
				<?php wp_nonce_field( 'emha_form_nonce', '_wpnonce', false ); ?>

				<div class="emha-form-fields-container">
					<?php if ( $is_register ) : ?>
						<?php $this->render_registration_fields( $widget_id, $settings ); ?>
					<?php else : ?>
						<?php foreach ( $fields as $index => $field ) :
							$field_id    = 'field_' . $widget_id . '_' . $index;
							$is_required = ( isset( $field['field_required'] ) && 'yes' === $field['field_required'] );
							$field_key   = sanitize_title( ! empty( $field['field_label'] ) ? $field['field_label'] : 'field_' . $index );
							$field_type  = ! empty( $field['field_type'] ) ? $field['field_type'] : 'text';
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
					<?php endif; ?>
				</div>

				<div class="emha-form-submit-container">
					<button type="submit" class="emha-form-submit-btn">
						<span class="emha-submit-text"><?php echo esc_html( $submit_text ); ?></span>
						<span class="emha-submit-loader" aria-hidden="true"></span>
					</button>
				</div>

				<div class="emha-form-response-msg" role="status" aria-live="polite"></div>
			</form>
		</div>
		<?php
	}
}
