<?php
/**
 * Plugin Name:	 Events Manager – Google Maps Styling
 * Description:	 &#x1F4CD; Style your Google Maps within Events Manager the way YOU like them! Select Map Type, Zoom Level, Controls and more.
 * Plugin URI:	 https://wordpress.org/plugins/stonehenge-em-maps-styling/
 * Version:		 2.1
 * Author:		 Stonehenge Creations
 * Author URI:	 https://www.stonehengecreations.nl/
 * License:		 GPL-2.0+
 * License URI:	 http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:	 stonehenge-em-maps-styling
 * Domain Path:	 /languages
 * Copyright (C) 2018 Stonehenge Creations
 **/


// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

$em_maps = new Stonehenge_EM_Maps_Styling;
class Stonehenge_EM_Maps_Styling {

	public function __construct() {
		// Construct variables for this Class
		$this->version 		= '2.1';
		$this->full 		= 'Events Manager – Google Maps Styling (Add-on)';
		$this->short		= 'Maps Styling';
		$this->icon			= '&#x1F4CD; ';
		$this->section 		= 'section-maps';
		$this->slug 		= 'stonehenge_em_maps';
		$this->base			= 'stonehenge_em_maps_';
		$this->capab 		= 'manage_options';
		$this->parent_slug	= 'stonehenge_em_main';
		$this->fields		= $this->create_options_array();

		// Gather all actions right here...
		add_action('admin_init', array($this, 'update_this_plugin'));
		add_action('admin_menu', array($this, 'add_submenu'), 49, 2);
		add_action('admin_init', array($this, 'register_settings' ));
		add_action('admin_init', array($this, 'check_for_required_plugins'));
		add_action('plugins_loaded', array($this, 'load_textdomain'));
		add_filter('plugin_row_meta', array($this, 'create_additional_plugin_links'), 10, 2);

		// Actions for this specific Class
		add_action('wp_footer', array($this, 'create_maps_css'));
	}

	function update_this_plugin() {
		$version_option 	= $this->base . 'version';
		$version_value 		= $this->version;
		if( !get_option( $version_option ) ) {
			add_option( $version_option, $version_value, '', 'no' );
		}
		if(	get_option( $version_option ) < $version_value ) {
			update_option( $version_option, $version_value, '', 'no' );
		}
		if( get_option( $this->base . 'logo' ) ) {
			delete_option( $this->base . 'logo' );
			delete_option( $this->base . 'admin' );
		}
	}


	// Load the language files
	function load_textdomain() {
	    load_plugin_textdomain( 'stonehenge-em-maps-styling', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	// Create the Sub Menu Item
	public function add_submenu() {
		$plugin_page = add_submenu_page(
			'edit.php?post_type='.EM_POST_TYPE_EVENT,
			$this->full,
			$this->icon. $this->short,
			$this->capab,
			$this->slug,
			array($this, 'show_sub_page')
		);
	    add_action( 'admin_print_styles-'. $plugin_page, array( $this, 'load_css') );
	}

	// Load CSS stylesheet for this specific admin page only.
	function load_css() {
		wp_enqueue_style('stonehenge-toggles', esc_url(plugins_url('stonehenge-em-maps-styling.css', __FILE__)));
	}


	// Construct the array for the Option Fields
	private function create_options_array() {
		$fields = array(
			array(
				'id' => 'zoom',
				'label' => __('Set Zoom Level', 'stonehenge-em-maps-styling'),
				'type' => 'number',
				'attr' => 'required min="1" max="20"',
				'tip' => __('Set the Zoom level from 1 to 20. <br><br>The Events Manager default = 15.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'switch',
				'label' => __('Disable Map Type Switching', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('Select "yes" if you want to prevent your visitors from changing the map type.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'scroll',
				'label' => __('Disable Mouse Scrollwheel', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('Select "yes" if you want to disable the mouse scrollwheel.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'click',
				'label' => __('Disable double click to Zoom', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('Select "yes" if you want to disable zooming in and out by double clicking.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'controls',
				'label' => __('Hide Map Controls', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('Select "yes" if you want to hide the controls like street view, zoom controls etc.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'footer',
				'label' => __('Hide Footer clutter', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('Select "yes" if you want to hide the clutter in the footer that on-one ever uses.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'background',
				'label' => __('Hide Full Screen Layer', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('Select "yes" if you want to hide the Full Screen button.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'font',
				'label' => __('Block Roboto Font', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('By default the text in the balloon uses Roboto. You can prevent this online font from being loaded, saving you precious PageSpeed load.<br><br><strong>IMPORTANT:</strong><br>Only select "yes" if you are not using other Google Fonts.', 'stonehenge-em-maps-styling'),
			),

			array(
				'id' => 'local',
				'label' => __('Localize Map', 'stonehenge-em-maps-styling'),
				'type' => 'checkbox',
				'attr' => null,
				'tip' => __('By default the Google Map is displayed in the language of the user\'s browser. So, if your blog is in English, but the visitor\'s browser is set to Spanish, the map will be in Spanish.<br><br><em>A big benefit on public computers.</em>', 'stonehenge-em-maps-styling'),
			),

		);

		return $fields;
	}


	// Register & update to Wordpress
	public function register_settings() {
		foreach ($this->fields as $field => $key) {
			register_setting($this->section, $this->base.$key['id']);
		}
		register_setting($this->section, $this->base.'api');
		register_setting($this->section, $this->base.'type');
	}


	// Create the Options Page for this Class
	function show_sub_page() {
		?>
		<div class="wrap">
		<h1><?php echo $this->icon . $this->full; ?></h1>
		<?php
		if (isset($_GET['settings-updated'])) {
			echo '<div class="notice notice-success is-dismissible"><p>'. __('Your Google Maps have been beautified.', 'stonehenge-em-maps-styling'). '</p></div>';
		}

		echo '<p>'. __('Version') . ': ' . get_option($this->base .'version') .'<p>';
		?>
		<form method="post" action="options.php" autocomplete="off">
			<?php settings_fields($this->section); ?>
			<?php do_settings_sections($this->section); ?>
		<table>
			<tr valign="bottom"><td colspan="2"><h3><?php _e('Style your Event Maps', 'stonehenge-em-maps-styling'); ?>:</h3></td></tr>

<script language="JavaScript">
var DivTxt = new Array()
DivTxt[0] = "<strong><?php _e('Roadmap', 'stonehenge-em-maps-styling'); ?></strong> <?php _e('displays the default road map view. (This is the default map type)', 'stonehenge-em-maps-styling'); ?>"
DivTxt[1] = "<strong><?php _e('Hybrid', 'stonehenge-em-maps-styling'); ?></strong> <?php _e('displays a mixture of normal and satellite views.', 'stonehenge-em-maps-styling'); ?>"
DivTxt[2] = "<strong><?php _e('Satellite', 'stonehenge-em-maps-styling'); ?></strong> <?php _e('displays Google Earth satellite images.', 'stonehenge-em-maps-styling'); ?>"
DivTxt[3] = "<Strong><?php _e('Terrain', 'stonehenge-em-maps-styling'); ?></strong> <?php _e('displays a physical map based on terrain information.', 'stonehenge-em-maps-styling'); ?>"
	function getText(slction){
		txtSelected = slction.selectedIndex;
		document.getElementById('explainType').innerHTML = DivTxt[txtSelected];
	}
</script>

			<tr>
			<th scope="row"><?php echo _e('Google Maps API Key', 'stonehenge-em-maps-styling'); ?></th>
			<td colspan="2"><input type="text" name="<?php echo $this->base.'api'; ?>" id="<?php echo $this->base.'api'; ?>" value="<?php echo get_option($this->base.'api'); ?>" required class="large"></td></tr>

			<tr>
			<th scope="row"><?php _e('Select the Map Type', 'stonehenge-em-maps-styling'); ?></th>
			<td width="120px"><select name="<?php echo $this->base.'type'; ?>" id="<?php echo $this->base.'type'; ?>" onchange="getText(this)">
				<option value="ROADMAP" <?php selected(get_option($this->base.'type'), "ROADMAP"); ?>><?php _e('Roadmap', 'stonehenge-em-maps-styling'); ?></option>
				<option value="HYBRID" <?php selected(get_option($this->base.'type'), "HYBRID"); ?>><?php _e('Hybrid', 'stonehenge-em-maps-styling'); ?></option>
				<option value="SATELLITE" <?php selected(get_option($this->base.'type'), "SATELLITE"); ?>><?php _e('Satellite', 'stonehenge-em-maps-styling'); ?></option>
				<option value="TERRAIN" <?php selected(get_option($this->base.'type'), "TERRAIN"); ?>><?php _e('Terrain', 'stonehenge-em-maps-styling'); ?></option>
				</select></td>
			<td><span class="helper" id="explainType"></span></td></tr>

			<?php
		foreach ($this->fields as $field => $key) {
			$id		= $this->base.$key['id'];
			$tip	= $key['tip'];
			$expl 	= __('Explain', 'stonehenge-em-maps-styling');
			$no		= '<span>'. __("No", 'stonehenge-em-maps-styling') .'</span>';
			$yes	= '<span>'. __("Yes", 'stonehenge-em-maps-styling') .'</span>';

			if ($tip == '') { $tooltip = ''; }
			else { $tooltip = "<td><div class=\"tooltip\">". $expl ."<span class=\"tooltiptext\">". $key['tip'] ."</span></div></td>"; }

			if (get_option($id) == "YES") { $checked = ' checked'; } else { $checked = ''; }

			echo '<tr><th scope="row">'. $key['label'] .'</th>';

			switch ($key['type']) {
			case 'checkbox':
				$field = '<td width="120px"><label class="switch-light switch-candy" onclick="">
							<input type="'.$key['type'].'" name="'. $id .'" id="'. $id .'" value="YES" '.$key['attr']. $checked. ' />
							<span>'. $no . $yes .'<a></a></span></label></td>';
				break;

			case 'number':
				$field = '<td><input type="'.$key['type'].'" name="'.$id.'" id="'.$id.'" value="'.get_option($id).'" '.$key['attr'].' > ';
				break;

			default:
				$key['attr'] = '';
				$field = '<td colspan="2"><input type="'.$key['type'].'" name="'.$id. '" id="'.$id.'" value="'.get_option($id).' " '.$key['attr'].'></td>';
				break;
			}
			echo $field;
			echo $tooltip .'</tr>';
		}
?>
	</table>
			<?php submit_button(); ?>
	</form>
	<?php
	} // End of Page


	// Check for original Events Manager plugin
	function check_for_required_plugins() {
		if (!class_exists('EM_Object')) {
			deactivate_plugins(plugin_basename(__FILE__));
			add_action('admin_notices', array($this, 'error_for_missing_em'));
		}
	}


	// Create the error notice is Events Manager is missing
	function error_for_missing_em() {
		echo '<div class="error"><p><strong>'. $this->full. '</strong> requires the original <strong>Events Manager</strong> plugin by NetWebLogic.<br>Please install and activate <a href="https://wordpress.org/plugins/events-manager/" target="_blank">Events Manager</a> first.</p></div>';
	}


	// Create new CSS to handle the Maps Styling Options
	public function create_maps_css() {
		$footer 	= get_option($this->base.'footer');
		$controls 	= get_option($this->base.'controls');
		$background	= get_option($this->base.'background');

		if ($footer === "YES") { $footer = '.gmnoprint a, .gmnoprint span, .gm-style-cc {display:none !important;} '; } else { $footer = ''; }
		if ($controls === "YES") { $controls = '.gmnoprint div {display:none !important;} '; } else { $controls = ''; }
		if ($background === "YES") { $background = '.gmnoprint, .gm-fullscreen-control {display:none !important;} '; } else { $background = ''; }

		if (is_singular(array('event', 'location'))) {
			echo '<style type="text/css" name="stonehenge-em-maps-styling">' . $footer . $controls . $background . '</style>';
		}
	}


	// Create additional links to the Plugins Page
	public function create_additional_plugin_links($links, $file) {

		$plugin = plugin_basename(__FILE__);
		if ($file == $plugin) { // Only for this plugin

			$plugindir  = explode("/plugins/", dirname(__FILE__));
			$wpslug		  = $plugindir[1];
			$author		  = 'DuisterDenHaag';
			$donate		  = 'https://useplink.com/payment/VRR7Ty32FJ5mSJe8nFSx';

			return array_merge(
				$links,
				array('<a href="https://wordpress.org/support/plugin/'.$wpslug.'" target="_blank">' . __('Plug-in Support', 'stonehenge-em-maps-styling'). '</a>'),
				array('<a href="https://profiles.wordpress.org/'.$author.'" target="_blank">' . __('Wordpress Profile', 'stonehenge-em-maps-styling') . '</a>'),
				array('<a href="'.$donate.'" target="_blank">' . __('Donate', 'stonehenge-em-maps-styling') . '</a>')
			);
		}
		return $links;
	}

} // End class Stonehenge_EM_Addon_MapsStyling



// Hook into the original JavaScript (events-manager.js)
add_filter('em_location_output', function ($content) {
		if (is_singular(array('event', 'location'))) {
			add_action('wp_print_footer_scripts', 'stonehenge_em_maps_js_mapoptions');
		}
		return $content;
	});

function stonehenge_em_maps_js_mapoptions() {
	$em = 'stonehenge_em_maps_';
	$zoom	 = get_option($em.'zoom');
	$maptype  = 'google.maps.MapTypeId.'. get_option($em.'type');

	if (get_option($em.'click')  == "YES") { $click	 = 'true'; } else { $click   = 'false'; }
	if (get_option($em.'switch') == "YES") { $switch = 'false'; } else { $switch = 'true'; 	}
	if (get_option($em.'scroll') == "YES") { $scroll = 'false'; } else { $scroll = 'true'; 	}

	?>
	<script>
		jQuery(document).bind("em_maps_location_hook", function(event, map) {
		map.setOptions({
			zoom: <?php echo $zoom;?>,
			mapTypeId: <?php echo $maptype; ?>,
			mapTypeControl: <?php echo $switch; ?>,
			scrollwheel: <?php echo $scroll; ?>,
			disableDoubleClickZoom: <?php echo $click; ?>,
			tilt: 0
			});
		});
	</script>
	<?php
}
