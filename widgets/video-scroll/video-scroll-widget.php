<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Video_Scroll_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'emha-video-scroll';
	}

	public function get_title() {
		return esc_html__( '3D Video Scroll', 'elementor-must-have-addons' );
	}

	public function get_icon() {
		return 'eicon-play';
	}

	public function get_categories() {
		return [ 'emha-category' ];
	}

	public function get_script_depends() {
		return [ 'emha-video-scroll-script' ];
	}

	public function get_style_depends() {
		return [ 'emha-video-scroll-style' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_video',
			[
				'label' => esc_html__( 'Video Settings', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'video_url',
			[
				'label'       => esc_html__( 'Video URL', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::MEDIA,
				'default'     => [
					'url' => 'http://akashmali.info/wp-content/uploads/2026/07/new-realestate-scroll-30mb.mp4',
				],
				'media_types' => [ 'video' ],
				'description' => esc_html__( 'Note: A 15FPS video is recommended for optimal smooth scrolling (maximum file size around 30MB).', 'elementor-must-have-addons' ),
			]
		);

		$this->add_control(
			'poster_url',
			[
				'label'   => esc_html__( 'Poster Image', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1800&q=85',
				],
			]
		);

		$this->add_control(
			'seo_title',
			[
				'label'       => esc_html__( 'SEO/Accessibility Title', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Premium Multifamily Properties for Sale and Rent', 'elementor-must-have-addons' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'video_duration',
			[
				'label'   => esc_html__( 'Video Duration (seconds)', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 30,
				'min'     => 1,
			]
		);

		$this->add_control(
			'video_fps',
			[
				'label'   => esc_html__( 'Video Source FPS', 'elementor-must-have-addons' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 15,
				'min'     => 1,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_scenes',
			[
				'label' => esc_html__( 'Scenes/Text Layers', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'scene_kicker',
			[
				'label'       => esc_html__( 'Kicker Text', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'A NEW PERSPECTIVE', 'elementor-must-have-addons' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'scene_title',
			[
				'label'       => esc_html__( 'Title Text', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Multifamily Living, Elevated.', 'elementor-must-have-addons' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'scene_time',
			[
				'label'       => esc_html__( 'Start Time (seconds)', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'step'        => 0.1,
				'description' => esc_html__( 'The time offset in seconds when this scene text highlights.', 'elementor-must-have-addons' ),
			]
		);

		$repeater->add_control(
			'scene_cta_text',
			[
				'label' => esc_html__( 'CTA Link Text (Only visible on last scene)', 'elementor-must-have-addons' ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'scene_cta_link',
			[
				'label'       => esc_html__( 'CTA Link URL (Only visible on last scene)', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'elementor-must-have-addons' ),
			]
		);

		$this->add_control(
			'scenes_list',
			[
				'label'       => esc_html__( 'Scenes List', 'elementor-must-have-addons' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'scene_kicker' => esc_html__( 'A NEW PERSPECTIVE', 'elementor-must-have-addons' ),
						'scene_title'  => esc_html__( 'Multifamily Living, Elevated.', 'elementor-must-have-addons' ),
						'scene_time'   => 0,
					],
					[
						'scene_kicker' => esc_html__( 'ARRIVE DIFFERENTLY', 'elementor-must-have-addons' ),
						'scene_title'  => esc_html__( 'From Skyline to Front Door.', 'elementor-must-have-addons' ),
						'scene_time'   => 5.8,
					],
					[
						'scene_kicker' => esc_html__( 'OPEN-PLAN LIVING', 'elementor-must-have-addons' ),
						'scene_title'  => esc_html__( 'Space That Moves With You.', 'elementor-must-have-addons' ),
						'scene_time'   => 11.7,
					],
					[
						'scene_kicker' => esc_html__( 'CRAFTED FOR EVERY DAY', 'elementor-must-have-addons' ),
						'scene_title'  => esc_html__( 'Where Design Meets Daily Life.', 'elementor-must-have-addons' ),
						'scene_time'   => 17.6,
					],
					[
						'scene_kicker' => esc_html__( 'WELCOME HOME', 'elementor-must-have-addons' ),
						'scene_title'  => esc_html__( 'Comfort in Every Detail.', 'elementor-must-have-addons' ),
						'scene_time'   => 23.4,
						'scene_cta_text' => esc_html__( 'Book a Private Tour', 'elementor-must-have-addons' ),
						'scene_cta_link' => [
							'url' => '#book-tour',
						],
					],
				],
				'title_field' => '{{{ scene_kicker }}}',
			]
		);

		$this->end_controls_section();

		// Styling Options
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Styling', 'elementor-must-have-addons' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'gold_color',
			[
				'label'     => esc_html__( 'Gold Accent Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#c99642',
				'selectors' => [
					'{{WRAPPER}} .rs-scroll-stage' => '--rs-gold: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'gold2_color',
			[
				'label'     => esc_html__( 'Gold Highlight Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#dfa84f',
				'selectors' => [
					'{{WRAPPER}} .rs-scroll-stage' => '--rs-gold2: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'deep_color',
			[
				'label'     => esc_html__( 'Background Dark Color', 'elementor-must-have-addons' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#050505',
				'selectors' => [
					'{{WRAPPER}} .rs-scroll-hero, {{WRAPPER}} .rs-scroll-stage, {{WRAPPER}} .rs-scroll-video' => 'background: {{VALUE}};',
					'{{WRAPPER}} .rs-scroll-stage' => '--rs-deep: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$video_url  = ! empty( $settings['video_url']['url'] ) ? esc_url( $settings['video_url']['url'] ) : '';
		$poster_url = ! empty( $settings['poster_url']['url'] ) ? esc_url( $settings['poster_url']['url'] ) : '';
		$seo_title  = ! empty( $settings['seo_title'] ) ? esc_html( $settings['seo_title'] ) : '';

		$scenes = $settings['scenes_list'];
		$scene_times = [];

		foreach ( $scenes as $index => $scene ) {
			$scene_times[] = isset( $scene['scene_time'] ) ? floatval( $scene['scene_time'] ) : 0;
		}

		$config = [
			'sceneTimes' => $scene_times,
			'duration'   => isset( $settings['video_duration'] ) ? floatval( $settings['video_duration'] ) : 30,
			'fps'        => isset( $settings['video_fps'] ) ? intval( $settings['video_fps'] ) : 15,
		];
		?>
		<section class="rs-scroll-hero" data-emha-config="<?php echo esc_attr( json_encode( $config ) ); ?>">
			<div class="rs-scroll-stage">
				<?php if ( $video_url ) : ?>
					<video class="rs-scroll-video"
						src="<?php echo esc_url( $video_url ); ?>" 
						preload="auto" 
						muted
						playsinline 
						webkit-playsinline 
						autoplay 
						type="video/mp4"
						poster="<?php echo esc_url( $poster_url ); ?>"
						aria-label="<?php echo esc_attr( $seo_title ); ?>">
					</video>
				<?php endif; ?>

				<div class="rs-scroll-shade"></div>
				<?php if ( $seo_title ) : ?>
					<h1 class="rs-scroll-seo"><?php echo esc_html( $seo_title ); ?></h1>
				<?php endif; ?>

				<div class="rs-scroll-copy">
					<?php foreach ( $scenes as $index => $scene ) : 
						$this->add_render_attribute( 'scene_' . $index, 'class', 'rs-scroll-scene' );
						if ( $index === 0 ) {
							$this->add_render_attribute( 'scene_' . $index, 'class', 'rs-scene-active' );
						}
						
						$cta_url = ! empty( $scene['scene_cta_link']['url'] ) ? esc_url( $scene['scene_cta_link']['url'] ) : '';
						?>
						<article <?php $this->print_render_attribute_string( 'scene_' . $index ); ?>>
							<?php if ( ! empty( $scene['scene_kicker'] ) ) : ?>
								<p class="rs-scene-kicker"><?php echo esc_html( $scene['scene_kicker'] ); ?></p>
							<?php endif; ?>
							
							<?php if ( ! empty( $scene['scene_title'] ) ) : ?>
								<h2 class="rs-scene-title"><?php echo wp_kses_post( nl2br( $scene['scene_title'] ) ); ?></h2>
							<?php endif; ?>
							
							<p class="rs-scene-copy"></p>
							
							<?php if ( ! empty( $scene['scene_cta_text'] ) && $cta_url ) : ?>
								<a class="rs-scene-cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $scene['scene_cta_text'] ); ?></a>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
