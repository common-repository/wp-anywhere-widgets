<?php
/**
 * Plugin Name: WP Anywhere Widgets
 * Description: Get Shortcode for each widget used in sidebar area
 * Version: 2.0
 * Author URI: https://www.yudiz.com/
 * Author: Yudiz Solutions Ltd.
 */

define( 'WP_WIDGET_SHORTCODE_PLUGIN', __FILE__ );
define( 'WP_WIDGET_SHORTCODE_PLUGIN_DIR', untrailingslashit( dirname( WP_WIDGET_SHORTCODE_PLUGIN ) ) );
define( 'YSPL_WS_SHORTCODE', 'YSPL_WS_SHORTCODE' );

/*Create a bundle for creating and displaying widget shortcode */
Class YSPL_WS{

	private static $instance = null;

	public static function yspl_ws_object() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct(){
		
		add_action( 'in_widget_form', array( $this, 'yspl_ws_widget_form' ), 10, 3 );
		add_shortcode( YSPL_WS_SHORTCODE, array( $this, 'yspl_ws_shortcode' ) );
	}

	function yspl_ws_shortcode($atts){
		global $_wp_sidebars_widgets, $wp_registered_widgets, $wp_registered_sidebars;
		
		extract(shortcode_atts(array('id'=>''), $atts, YSPL_WS_SHORTCODE ));

		if( empty( $id ) || ! isset( $wp_registered_widgets[$id] ) )
		return;

		$widget_sidebar = array();
		foreach ($_wp_sidebars_widgets as $key => $sidebar) {
			if($key != 'wp_inactive_widgets' && $key != 'array_version'){
				foreach ($sidebar as $active_widget) {
					array_push($widget_sidebar, $active_widget);
				}				
			}
		}		

		if(!empty($id) && in_array($id, $widget_sidebar) &&  isset( $wp_registered_widgets[$id] ) ){			
			// get widget class
			$widgetClass = $wp_registered_widgets[$id]['classname'];
			preg_match( '/(\d+)/', $id, $id_number );
			$options = get_option( $wp_registered_widgets[$id]['callback'][0]->option_name );
			$instance = isset( $options[$id_number[0]] ) ? $options[$id_number[0]] : array();
			$class = get_class( $wp_registered_widgets[$id]['callback'][0] );

			/* build the widget args that needs to be filtered through dynamic_sidebar_params */
			$params = array(
				0 => array(
					'name' => '',
					'id' => '',
					'description' => '',
					'before_widget' => "<div>",
					'before_title' => "<h2>",
					'after_title' => "</h2>",
					'after_widget' => "</div>",
					'widget_id' => $id,
					'widget_name' => $wp_registered_widgets[$id]['name']
				),
				1 => array(
					'number' => $id_number[0]
				)
			);
			
			$params[0]['name'] = $wp_registered_widgets[$id]['name'];
			$params[0]['id'] = $wp_registered_widgets[$id]['name'];
			$params[0]['description'] = $wp_registered_widgets[$id]['description'];

			// render the widget
			ob_start();
			echo ('<!-- Widget Shortcode -->');
			the_widget( $class, $instance, $params[0] );
			echo ('<!-- /Widget Shortcode -->');
			$content = ob_get_clean();
			echo $content;
		}
		
	}

	function yspl_ws_widget_form( $widget, $return, $instance ) {

		echo ('<p>' . __( 'Shortcode', 'widget-shortcode' ) . ': ' . ( ( $widget->number == '__i__' ) ? __( 'Please save this first.', 'widget-shortcode' ) : '<input type="text" value="' . esc_attr( '['.YSPL_WS_SHORTCODE.' id="'. $widget->id .'"]' ) . '" readonly="readonly" class="widefat" onclick="this.select()" />' ) . '</p>');
	}
}
YSPL_WS::yspl_ws_object();