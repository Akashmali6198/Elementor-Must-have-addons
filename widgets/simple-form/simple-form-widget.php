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

		$repeater->add_control(
			'map_to',
			[
				'label'       => esc_html__( 'Map to Registration Field', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'none',
				'options'     => [
					'none'       => esc_html__( 'None (Save as User Meta)', 'elementor-must-have-addons' ),
					'user_login' => esc_html__( 'Username', 'elementor-must-have-addons' ),
					'user_email' => esc_html__( 'Email', 'elementor-must-have-addons' ),
					'user_pass'  => esc_html__( 'Password', 'elementor-must-have-addons' ),
					'first_name' => esc_html__( 'First Name', 'elementor-must-have-addons' ),
					'last_name'  => esc_html__( 'Last Name', 'elementor-must-have-addons' ),
					'site_path'  => esc_html__( 'Site Path (Multisite)', 'elementor-must-have-addons' ),
					'site_title' => esc_html__( 'Site Title (Multisite)', 'elementor-must-have-addons' ),
				],
				'description' => esc_html__( 'Only maps if Form Mode is User Registration.', 'elementor-must-have-addons' ),
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
						'map_to'            => 'none',
					],
					[
						'field_type'        => 'email',
						'field_label'       => esc_html__( 'Email Address', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'john@example.com', 'elementor-must-have-addons' ),
						'field_required'    => 'yes',
						'map_to'            => 'user_email',
					],
					[
						'field_type'        => 'textarea',
						'field_label'       => esc_html__( 'Message', 'elementor-must-have-addons' ),
						'field_placeholder' => esc_html__( 'How can we help you?', 'elementor-must-have-addons' ),
						'field_required'    => 'yes',
						'map_to'            => 'none',
					],
				],
				'title_field' => '{{{ field_label }}}',
			]
		);

		$this->end_controls_section();

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
			'user_role',
			[
				'label'   => esc_html__( 'Default User Role', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'subscriber',
				'options' => [
					'subscriber'    => esc_html__( 'Subscriber', 'elementor-must-have-addons' ),
					'contributor'   => esc_html__( 'Contributor', 'elementor-must-have-addons' ),
					'author'        => esc_html__( 'Author', 'elementor-must-have-addons' ),
					'editor'        => esc_html__( 'Editor', 'elementor-must-have-addons' ),
					'administrator' => esc_html__( 'Administrator', 'elementor-must-have-addons' ),
				],
			]
		);

		$this->add_control(
			'auto_login',
			[
				'label'        => esc_html__( 'Auto Log In After Sign Up', 'elementor-must-have-addons' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'elementor-must-have-addons' ),
				'label_off'    => esc_html__( 'No', 'elementor-must-have-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		if ( is_multisite() ) {
			$this->add_control(
				'multisite_create_site',
				[
					'label'        => esc_html__( 'Create User\'s Site', 'elementor-must-have-addons' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'elementor-must-have-addons' ),
					'label_off'    => esc_html__( 'No', 'elementor-must-have-addons' ),
					'return_value' => 'yes',
					'default'      => 'no',
					'description'  => esc_html__( 'Automatically creates a blog/site for the user in this WordPress Multisite network.', 'elementor-must-have-addons' ),
				]
			);
		}

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
				'label'   => esc_html__( 'Success Message', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Your submission was sent successfully!', 'elementor-must-have-addons' ),
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
				'default'   => 'rgba(255, 255, 255, 0.03)',
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
				'selectors' => [
					'{{WRAPPER}} .emha-form-label' => 'color: {{VALUE}};',
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

	protected function render() {
		// Ensure assets load even if Elementor dependency resolution is delayed.
		wp_enqueue_script( 'emha-simple-form-script' );
		wp_enqueue_style( 'emha-simple-form-style' );

		$settings = $this->get_settings_for_display();
		$fields   = ! empty( $settings['form_fields'] ) ? $settings['form_fields'] : [];
		$widget_id = $this->get_id();
		$form_dom_id = 'emha-form-' . $widget_id;
		$post_id = get_the_ID();

		// Cache form config for reliable AJAX handling (email, subject, messages, registration, multisite options).
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
