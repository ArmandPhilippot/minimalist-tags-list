<?php
/**
 * Minimalist-Tags-List
 *
 * The Minimalist-Tags-List is a plugin to display list or dropdown of tags.
 *
 * @package     Minimalist_Tags_List
 * @link        https://github.com/ArmandPhilippot/minimalist-tags-list
 * @author      Armand Philippot <contact@armandphilippot.com>
 * @see       https://www.armandphilippot.com
 *
 * @copyright   2020 Armand Philippot
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Tags List
 * Plugin URI:  https://github.com/ArmandPhilippot/minimalist-tags-list
 * Description: Display a list or a dropdown of tags as widget.
 * Version:     1.0.0
 * Author:      Armand Philippot
 * Author URI:  https://www.armandphilippot.com
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Tags:        widget, tags
 * Text Domain: Minimalist-Tags-List
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MINIMALIST_TAGS_LIST_VERSION', '1.0.0' );

/**
 * Load text domain files
 *
 * @since 1.0.0
 */
function minimalist_tags_list_load_plugin_textdomain() {
	load_plugin_textdomain( 'Minimalist-Tags-List', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'minimalist_tags_list_load_plugin_textdomain' );

/**
 * Class used to implement a Minimalist-Tags-List Widget.
 *
 * @since 1.0.0
 */
class Minimalist_Tags_List extends WP_Widget {

	/**
	 * Set up a new Minimalist-Tags-List widget instance with id, name & description.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_options = array(
			'classname'                   => 'widget_minimalist_tags_list',
			'description'                 => __( 'Display a list or a dropdown of tags as widget.', 'Minimalist-Tags-List' ),
		);
		parent::__construct(
			'minimalist-tags-list',
			__( 'Tags List', 'Minimalist-Tags-List' ),
			$widget_options
		);

		add_action(
			'widgets_init',
			function() {
				register_widget( 'Minimalist_Tags_List' );
			}
		);
	}

	/**
	 * Output the content for the current instance
	 *
	 * @since 1.0.0
	 *
	 * @param array $args HTML to display the widget title class and widget content class.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		static $first_dropdown = true;

		$title        = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Tags', 'Minimalist-Tags-List' );
		$title        = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$count        = ! empty( $instance['count'] ) ? '1' : '0';
		$hierarchical = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$dropdown     = ! empty( $instance['dropdown'] ) ? '1' : '0';
		$tags_args    = array(
			'orderby'      => 'name',
			'show_count'   => $count,
			'hierarchical' => $hierarchical,
		);
		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}
		if ( $dropdown ) {
			echo sprintf( '<form action="%s" method="get">', esc_url( home_url() ) );
			$dropdown_id    = ( $first_dropdown ) ? 'tag' : "{$this->id_base}-dropdown-{$this->number}";
			$first_dropdown = false;

			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . esc_html( $title ) . '</label>';

			$tags_args['show_option_none'] = __( 'Select Tags', 'Minimalist-Tags-List' );
			$tags_args['id']               = $dropdown_id;
			$tags_args['taxonomy']         = 'post_tag';
			$tags_args['hide_if_empty']    = true;
			/**
			 * Filters the categories arguments for the Minimalist-Tags-List widget drop-down.
			 *
			 * @since 1.0.0
			 *
			 * @see https://developer.wordpress.org/reference/functions/wp_dropdown_categories/
			 *
			 * @param array $tags_args An array of Minimalist-Tags-List widget drop-down arguments.
			 * @param array $instance Array of settings for the current widget.
			 */
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $tags_args, $instance ) );

			echo '</form>';
		} else {
			echo '<ul>';
			$tags_args['title_li']      = '';
			$tags_args['taxonomy']      = 'post_tag';
			$tags_args['hide_if_empty'] = true;
			/**
			 * Filters the categories arguments for the Minimalist-Tags-List widget.
			 *
			 * @since 1.0.0
			 *
			 * @see https://developer.wordpress.org/reference/functions/wp_list_categories/
			 *
			 * @param array $cat_args An array of Minimalist-Tags-List widget options.
			 * @param array $instance Array of settings for the current widget.
			 */
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			wp_list_categories( apply_filters( 'widget_categories_args', $tags_args, $instance ) );
			echo '</ul>';
		}
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the options form in the admin
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {
		$title        = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$count        = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown     = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		echo '<p>';
		echo '<label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">';
		echo esc_html__( 'Title:', 'Minimalist-Tags-List' );
		echo '</label>';
		echo '<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '" />';
		echo '</p>';
		echo '<p>';
		echo '<input class="checkbox" id="' . esc_attr( $this->get_field_id( 'dropdown' ) ) . '" name="' . esc_attr( $this->get_field_name( 'dropdown' ) ) . '" type="checkbox" ';
		checked( $dropdown );
		echo ' />';
		echo '<label for="' . esc_attr( $this->get_field_id( 'dropdown' ) ) . '">';
		echo esc_html__( 'Display as dropdown', 'Minimalist-Tags-List' );
		echo '</label><br />';
		echo '<input class="checkbox" id="' . esc_attr( $this->get_field_id( 'count' ) ) . '" name="' . esc_attr( $this->get_field_name( 'count' ) ) . '" type="checkbox" ';
		checked( $count );
		echo ' />';
		echo '<label for="' . esc_attr( $this->get_field_id( 'count' ) ) . '">';
		echo esc_html__( 'Show post counts', 'Minimalist-Tags-List' );
		echo '</label><br />';
		echo '<input class="checkbox" id="' . esc_attr( $this->get_field_id( 'hierarchical' ) ) . '" name="' . esc_attr( $this->get_field_name( 'hierarchical' ) ) . '" type="checkbox" ';
		checked( $hierarchical );
		echo ' />';
		echo '<label for="' . esc_attr( $this->get_field_id( 'hierarchical' ) ) . '">';
		echo esc_html__( 'Show hierarchy', 'Minimalist-Tags-List' );
		echo '</label>';
		echo '</p>';
	}

	/**
	 * Processing widget options on save
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = sanitize_text_field( $new_instance['title'] );
		$instance['count']        = ! empty( $new_instance['count'] ) ? 1 : 0;
		$instance['hierarchical'] = ! empty( $new_instance['hierarchical'] ) ? 1 : 0;
		$instance['dropdown']     = ! empty( $new_instance['dropdown'] ) ? 1 : 0;

		return $instance;
	}
}
$minimalist_tags_list = new Minimalist_Tags_List();
