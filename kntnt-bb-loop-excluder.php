<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Kntnt's Exclude Duplicated Posts for Beaver Builder
 * Plugin URI:        https://github.com/Kntnt/kntnt-bb-loop-excluder
 * Description:       Makes it possible to avoid duplicated posts when using Beaver Builder custom query.
 * Version:           1.0.0
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       kntnt-bb-loop-excluder
 * Domain Path:       /languages
 */

namespace Kntnt\BB_Loop_Excluder;

defined( 'WPINC' ) && new Plugin();

final class Plugin {

	private $pids = [];

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'run' ] );
	}

	public function run() {
		load_plugin_textdomain( 'kntnt-bb-loop-excluder', false, basename( dirname( __FILE__ ) ) . '/languages' );
		add_filter( 'fl_builder_render_settings_field', [ $this, 'builder_render_settings_field' ], 10, 3 );
		add_filter( 'fl_builder_loop_query_args', [ $this, 'builder_loop_query_args' ] );
		add_filter( 'fl_builder_loop_query', [ $this, 'builder_loop_query' ], 10, 2 );
	}

	public function builder_render_settings_field( $field, $name, $settings ) {

		if ( 'exclude_self' == $name ) {
			$field['label'] = __( 'Exclude duplicates', 'kntnt-bb-loop-excluder' );
			$field['help'] = __( 'Exclude all previous returned posts from this query.', 'kntnt-bb-loop-excluder' );
		}

		return $field;

	}

	public function builder_loop_query_args( $args ) {

		if ( $this->pids && isset( $args['settings']->exclude_self ) && 'yes' == $args['settings']->exclude_self ) {
			// Current post id is added to $args['post__not_in'] before this filter is called.
			$args['post__not_in'] = array_merge( $this->pids, $args['post__not_in'] );
		}

		return $args;

	}

	public function builder_loop_query( $query, $settings ) {

		foreach ( $query->posts as $post ) {
			$this->pids[] = $post->ID;
		}

		return $query;

	}

}
