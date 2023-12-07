<?php

namespace BeycanPress\YAIA\Integrations;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \BeycanPress\YAIA\PluginHero\Helpers;

/**
 * Elementor short code selector
 */
class Elementor extends Widget_Base
{
    use Helpers;

	/**
	 * Retrieve the widget name.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'yaiaService';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__('YAIA Content generator', 'yaia');
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-edit';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return ['general'];
	}
	
	public function get_script_depends() {
		return ['yaiaElementor'];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.0
	 *
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'title',
			[
				'label' => esc_html__('Please enter prompt', 'yaia'),
			]
		);

		$this->add_control(
			'prompt',
			[
				'label'       => esc_html__('Prompt', 'yaia'),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'dynamic' => [
					'active' => false,
				],
			]
		);

		$this->add_control(
			'content',
			[
				'label'       => esc_html__('Content', 'yaia'),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'create',
			[
				'type'        => Controls_Manager::BUTTON,
				'text'        => esc_html__('Create', 'yaia'),
				'event'       => 'BeycanPress:YAIA:Create'
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 2.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		echo wp_kses_post($this->get_settings_for_display('content'));
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.0.0
	 *
	 * @access protected
	 */
	protected function content_template() {
		?> 
			{{{ settings.content }}} 
		<?php
		
	}
}
