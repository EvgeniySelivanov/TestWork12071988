<?php
/* Template Name: Countries and Cities */
get_header();

// get all cities
global $wpdb;
$cities = $wpdb->get_results("
    SELECT p.ID, p.post_title, t.name as country
    FROM {$wpdb->posts} p
    JOIN {$wpdb->prefix}term_relationships tr ON p.ID = tr.object_id
    JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
    JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE p.post_type = 'cities' AND tt.taxonomy = 'countries'
");
// Custom hook_1 before the table
do_action('before_cities_table', $cities);
?>

<div class="countries-cities-table">
    <h1><?php echo esc_html(get_the_title()); ?></h1>

    <div>
        <label for="city-search"><?php echo __('Search City:', 'textdomain'); ?></label>
        <input type="text" id="city-search" placeholder="Enter city name">
    </div>

    <table class="countries-cities-table" id="cities-table">
        <thead>
            <tr>
                <th>Country</th>
                <th>City</th>
                <th>Temperature</th>
            </tr>
        </thead>
        <tbody id="cities-table-body">
            <?php
            //output all cities
            if ($cities) {
                foreach ($cities as $city) {
                    // get the coordinates of the city (latitude and longitude)
                    $latitude = get_post_meta($city->ID, '_city_latitude', true); 
                    $longitude = get_post_meta($city->ID, '_city_longitude', true); 

                   
                    $temperature = get_city_weather($latitude, $longitude); //  get the temperature by coordinates,used function from functions.php

                    echo '<tr>';
                    echo '<td>' . esc_html($city->country) . '</td>';
                    echo '<td>' . esc_html($city->post_title) . '</td>';
                    echo '<td>' . ($temperature ? esc_html($temperature) . '°C' : 'No data') . '</td>'; // If there is a temperature,  display it
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="3">No cities found.</td></tr>';
            }
            ?>
        </tbody>
    </table>

</div>

<?php
// custom hook_2 after the table
do_action('after_cities_table', $cities);
get_footer(); ?>
