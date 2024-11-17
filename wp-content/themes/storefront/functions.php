<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];
define('OPENWEATHERMAP_API_KEY', '1a675dcee0d0ab4f5b3d52977d27c413');
/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */


show_admin_bar(false);
function my_theme_enqueue_styles()
{
  wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap', false);
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

/**include custom css */
function getCssVersion($filename)
{
  $file_path = get_stylesheet_directory() . '/assets/css/custom-css/' . $filename;
  $timestamp = filemtime($file_path);
  return $timestamp;
}
function custom_css()
{
  wp_enqueue_style('city-weather', get_stylesheet_directory_uri() . '/assets/css/custom-css/city-weather.css', array(), getCssVersion('city-weather.css'));
}
add_action('wp_enqueue_scripts', 'custom_css');
/**include js */
function getJSVersion($filename)
{
  $file_path = get_stylesheet_directory() . '/assets/js/' . $filename;
  $timestamp = filemtime($file_path);
  return $timestamp;
}
function city_weather_script() {
	
	wp_enqueue_script('city-weather-script', get_template_directory_uri() . '/assets/js/city-weather.js', array(), getJSVersion('city-weather.js'), true);
	wp_enqueue_script('city-search', get_template_directory_uri() . '/assets/js/city-search.js', array(), getJSVersion('city-search.js'), true);
	
	wp_localize_script('city-weather-script', 'cityWeatherAjax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'security' => wp_create_nonce('city_weather_nonce')
	));
	wp_localize_script('city-search', 'cityWeatherAjaxSearch', array(
		'ajax_url' => admin_url('admin-ajax.php'),  // URL для AJAX-запросов
		'security' => wp_create_nonce('city_weather_search_nonce')  // Генерация nonce для безопасности
));
}
add_action('wp_enqueue_scripts', 'city_weather_script');



/**Add Post Type Cities */
function register_cpt_cities() {
	$labels = array(
			'name'               => _x('Cities', 'post type general name', 'textdomain'),
			'singular_name'      => _x('City', 'post type singular name', 'textdomain'),
			'menu_name'          => _x('Cities', 'admin menu', 'textdomain'),
			'name_admin_bar'     => _x('City', 'add new on admin bar', 'textdomain'),
			'add_new'            => _x('Add New', 'city', 'textdomain'),
			'add_new_item'       => __('Add New City', 'textdomain'),
			'new_item'           => __('New City', 'textdomain'),
			'edit_item'          => __('Edit City', 'textdomain'),
			'view_item'          => __('View City', 'textdomain'),
			'all_items'          => __('All Cities', 'textdomain'),
			'search_items'       => __('Search Cities', 'textdomain'),
			'parent_item_colon'  => __('Parent Cities:', 'textdomain'),
			'not_found'          => __('No cities found.', 'textdomain'),
			'not_found_in_trash' => __('No cities found in Trash.', 'textdomain'),
	);

	$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'cities'),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-location-alt', // icon
			'supports'           => array('title', 'editor', 'thumbnail'), // supports fields
	);

	register_post_type('cities', $args);
}

add_action('init', 'register_cpt_cities');

/**add metabox */
function add_cities_meta_box() {
	add_meta_box(
			'cities_location_meta',          // ID 
			__('City Location', 'textdomain'), // Title
			'render_cities_meta_box',        // fields render
			'cities',                        // CPT, where show metabox
			'normal',                        // location: normal, side, advanced
			'default'                        // priority: default, high, low
	);
}
add_action('add_meta_boxes', 'add_cities_meta_box');


/**create metaboxes form*/
function render_cities_meta_box($post) {

	$latitude = get_post_meta($post->ID, '_city_latitude', true);
	$longitude = get_post_meta($post->ID, '_city_longitude', true);

	// add nonce for safety(security)
	wp_nonce_field('save_cities_location_meta', 'cities_location_nonce');

	echo '<p>';
	echo '<label for="city_latitude">' . __('Latitude:', 'textdomain') . '</label><br>';
	echo '<input type="text" id="city_latitude" name="city_latitude" value="' . esc_attr($latitude) . '" size="25" />';
	echo '</p>';
	echo '<p>';
	echo '<label for="city_longitude">' . __('Longitude:', 'textdomain') . '</label><br>';
	echo '<input type="text" id="city_longitude" name="city_longitude" value="' . esc_attr($longitude) . '" size="25" />';
	echo '</p>';
}



/**save metaboxes data */
function save_cities_location_meta($post_id) {
	// check nonce
	if (!isset($_POST['cities_location_nonce']) || 
			!wp_verify_nonce($_POST['cities_location_nonce'], 'save_cities_location_meta')) {
			return;
	}

	// autosave check
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
	}

	// checking access rights
	if (!current_user_can('edit_post', $post_id)) {
			return;
	}

	// save latitude
	if (isset($_POST['city_latitude'])) {
			update_post_meta($post_id, '_city_latitude', sanitize_text_field($_POST['city_latitude']));
	}

	// save longitude
	if (isset($_POST['city_longitude'])) {
			update_post_meta($post_id, '_city_longitude', sanitize_text_field($_POST['city_longitude']));
	}
}
add_action('save_post', 'save_cities_location_meta');

/**add taxonomy */
function register_taxonomy_countries() {
	$labels = array(
			'name'                       => _x('Countries', 'taxonomy general name', 'textdomain'),
			'singular_name'              => _x('Country', 'taxonomy singular name', 'textdomain'),
			'search_items'               => __('Search Countries', 'textdomain'),
			'all_items'                  => __('All Countries', 'textdomain'),
			'parent_item'                => __('Parent Country', 'textdomain'),
			'parent_item_colon'          => __('Parent Country:', 'textdomain'),
			'edit_item'                  => __('Edit Country', 'textdomain'),
			'update_item'                => __('Update Country', 'textdomain'),
			'add_new_item'               => __('Add New Country', 'textdomain'),
			'new_item_name'              => __('New Country Name', 'textdomain'),
			'menu_name'                  => __('Countries', 'textdomain'),
	);

	$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true, // If a hierarchy is required, as in categories
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true, // Show in the admin table
			'query_var'                  => true,
			'rewrite'                    => array('slug' => 'countries'),
	);

	register_taxonomy('countries', array('cities'), $args);
}
add_action('init', 'register_taxonomy_countries');





/**add shortcode */
function city_weather_selector_shortcode() {
	// Get all cities with CPT "Cities"
	$cities = get_posts(array(
			'post_type'   => 'cities',
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
	));

	ob_start();
	?>
	<div id="city-weather-widget">
		<h3>Weather my custom widget</h3>
			<label class="city-weather-selector-label" for="city-selector"><?php echo __('Select City:', 'textdomain'); ?></label>
			<select id="city-selector">
					<option value=""><?php echo __('-- Select a City --', 'textdomain'); ?></option>
					<?php foreach ($cities as $city): ?>
							<option value="<?php echo esc_attr($city->ID); ?>"><?php echo esc_html($city->post_title); ?></option>
					<?php endforeach; ?>
			</select>
			<div id="weather-output">
					<!-- Weather information will be displayed here -->
			</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode('city_weather_selector', 'city_weather_selector_shortcode');

/**get weather */
function get_city_weather($latitude, $longitude) {
	if (empty($latitude) || empty($longitude)) {
			return null; // If there is no latitude or longitude, we return null
	}

	$api_key = OPENWEATHERMAP_API_KEY;
	$url = sprintf(
			'https://api.openweathermap.org/data/2.5/weather?lat=%s&lon=%s&units=metric&appid=%s',
			urlencode($latitude),
			urlencode($longitude),
			urlencode($api_key)
	);

	// API request
	$response = wp_remote_get($url);

	if (is_wp_error($response)) {
			return null; // If there is an error, we return null
	}

	$data = json_decode(wp_remote_retrieve_body($response), true);

	// We return the temperature or "N/A" if it was not possible to get the temperature
	return isset($data['main']['temp']) ? $data['main']['temp'] : 'N/A';
}





function fetch_city_weather() {
	// Nonce validation for security
	check_ajax_referer('city_weather_nonce', 'security');

	$city_id = intval($_POST['city_id']);

	// We get the latitude and longitude of the city
	$latitude = get_post_meta($city_id, '_city_latitude', true);
	$longitude = get_post_meta($city_id, '_city_longitude', true);

	if (!$latitude || !$longitude) {
			wp_send_json_error(__('Invalid city data.', 'textdomain'));
	}

	$temperature = get_city_weather($latitude, $longitude);

	if ($temperature === null) {
			wp_send_json_error(__('Weather data could not be fetched.', 'textdomain'));
	}

	// We return the temperature
	wp_send_json_success(array('temperature' => $temperature));
}
add_action('wp_ajax_fetch_city_weather', 'fetch_city_weather');
add_action('wp_ajax_nopriv_fetch_city_weather', 'fetch_city_weather');



function fetch_city_search_results() {
	//Check nonce for security
	check_ajax_referer('city_weather_search_nonce', 'security');

	// We get the search parameter
	$search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';

	global $wpdb;

	// Query the database to get cities that match the search query
	$query = "
			SELECT c.ID, c.post_title, t.name AS country_name
			FROM {$wpdb->posts} c
			LEFT JOIN {$wpdb->prefix}term_relationships tr ON c.ID = tr.object_id
			LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
			WHERE c.post_type = 'cities'
			AND tt.taxonomy = 'countries'
			AND c.post_title LIKE %s
	";

	//  query with a search parameter
	$cities = $wpdb->get_results($wpdb->prepare($query, '%' . $wpdb->esc_like($search_term) . '%'));

	//output
	$output = '';
	if (!empty($cities)) {
			foreach ($cities as $city) {
					$latitude = get_post_meta($city->ID, '_city_latitude', true);
					$longitude = get_post_meta($city->ID, '_city_longitude', true);
				//	Getting temperature from global function
					$temperature = get_city_weather($latitude, $longitude);

					//table
					$output .= '<tr>';
					$output .= '<td>' . esc_html($city->country_name) . '</td>';
					$output .= '<td>' . esc_html($city->post_title) . '</td>';
					$output .= '<td>' . esc_html($temperature) . '°C</td>';
					$output .= '</tr>';
			}
	}

	if ($output) {
			wp_send_json_success(array('html' => $output));
	} else {
			wp_send_json_error(array('message' => 'No results found.'));
	}
}
add_action('wp_ajax_fetch_city_search_results', 'fetch_city_search_results');
add_action('wp_ajax_nopriv_fetch_city_search_results', 'fetch_city_search_results');



add_action('before_cities_table', function($cities) {
	// Get the current timestamp taking into account the WordPress time zone
	$timestamp = current_time('timestamp');
	
	// Formatting date and time taking into account localization
	$formatted_time = date_i18n('Y-m-d H:i:s', $timestamp);
	
	echo '<p>Current date and time: ' . esc_html($formatted_time) . '</p>';
});


add_action('after_cities_table', function($cities) {
	$city_count = is_array($cities) ? count($cities) : 0;
	echo '<p>Total cities in the table: ' . esc_html($city_count) . '</p>';
});
