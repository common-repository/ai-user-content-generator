<?php
/*
Plugin Name: AI User Content Generator
Description: Let your website users generate content with OpenAI.
Author: Elyseh Biagini
Version: 1.1
Author URI: https://elysehbiagini.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


// Enqueue styles and scripts for the frontend
// function ucgaip_generator_enqueue_scripts() {
//     wp_enqueue_style('ucgaip-generator-styles', plugins_url('/css/styles.css', __FILE__));
//    wp_enqueue_script('ucgaip-generator-script', plugins_url('/js/script.js', __FILE__), array('jquery'), time(), true);
// }
function ucgaip_generator_enqueue_scripts() {
    wp_enqueue_style('ucgaip-generator-styles', plugin_dir_url(__FILE__) . 'css/styles.css', array(), '1.0.0', 'all');
    wp_enqueue_script('ucgaip-generator-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0.0', true);
}

add_action('wp_enqueue_scripts', 'ucgaip_generator_enqueue_scripts');


// Enqueue scripts and styles for the admin settings page
function ucgaip_generator_admin_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_ucgaip_content_generator' && $hook !== 'ucgaip-content-generator_page_Ai_manage_data') {
        return;
    }
    wp_enqueue_style('ucgaip-generator-styles', plugin_dir_url(__FILE__) . 'css/styles.css', array(), '1.0.0', 'all');
    wp_enqueue_script('clipboard', plugin_dir_url(__FILE__) . 'js/clipboard.min.js', array(), '1.0.0', true);
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    // wp_enqueue_script('jquery');

    wp_add_inline_script('wp-color-picker', '
        jQuery(document).ready(function($){
            $(".ucgaip-color-picker").wpColorPicker();
        });
    ');
}
add_action('admin_enqueue_scripts', 'ucgaip_generator_admin_enqueue_scripts');


// Localization for JavaScript
function ucgaip_generator_localize_scripts() {
    wp_localize_script('ucgaip-generator-script', 'AiGeneratorAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ucgaip_generate_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'ucgaip_generator_localize_scripts');

// Shortcode to render content generation tool
function ucgaip_generator_shortcode() {
    ob_start();
    include(plugin_dir_path(__FILE__) . 'content-generator-template.php');
    return ob_get_clean();
}
add_shortcode('ucgaip-generator', 'ucgaip_generator_shortcode');

function ucgaip_create_database_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'ai_content_data';

    // SQL to create the table
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_input text NOT NULL,
        ucgaip_response text NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Load the upgrade class if it's not already loaded
    if (!class_exists('WP_Upgrader')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    }

    // Ensure that dbDelta() is available
    if (!function_exists('dbDelta')) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }

    dbDelta($sql);
}

register_activation_hook(__FILE__, 'ucgaip_content_activate');

function ucgaip_content_activate() {
    // Check if the other plugin is active
    if (is_plugin_active('ucgaip-content-generator-pro/ucgaip-content-generator.php')) {
        // Deactivate the other plugin
        deactivate_plugins('ucgaip-content-generator-pro/ucgaip-content-generator.php');
        
    }
}

function ucgaip_generate_text() {
    // Nonce Verification
    check_ajax_referer('ucgaip_generate_nonce', 'nonce');

    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ucgaip_generate_nonce')) {
        wp_send_json_error("Nonce verification failed.");
        wp_die();
    }

    // Check if the current user has the capability to generate text
    // if ( ! current_user_can( 'generate_text_capability' ) ) {
    //     wp_send_json_error( 'You do not have permission to generate text.' );
    //     wp_die();
    // }
    if (!current_user_can('manage_options')) { 
        throw new Exception("You don't have permission to perform this action.");
    }

    $prompt = sanitize_text_field($_POST['prompt']);
    $api_key = sanitize_text_field(get_option('ucgaip_api_key'));
    $api_url = 'https://api.openai.com/v1/chat/completions';

    $headers = array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key
    );

    $body = array(
        'model' => 'gpt-3.5-turbo',
        'messages' => array(array('role' => 'user', 'content' => $prompt)),
        'temperature' => 0.7
    );

    $args = array(
        'method' => 'POST',
        'headers' => $headers,
        'body' => wp_json_encode($body),
        'timeout' => 120
    );

    $response = wp_remote_request($api_url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json_error("Something went wrong: $error_message");
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON in API response');
        } elseif (!isset($data['choices'])) {
            wp_send_json_error('API request failed. Response: ' . $body);
        } else {
            $ucgaip_response = $data['choices'][0]['message']['content'];

            // Only store the user input and AI response if ucgaip_store_inputs is enabled
            $ucgaip_store_inputs = sanitize_text_field(get_option('ucgaip_store_inputs'));
            if ($ucgaip_store_inputs == "1") {
                $base_prompt = sanitize_textarea_field(get_option('ucgaip_prompt'));
                $topic = str_replace($base_prompt, '', $prompt);  // Extracting the topic

                $post_data = array(
                    'post_title'    => trim($topic),
                    'post_content'  => $ucgaip_response,
                    'post_status'   => 'publish',
                    'post_type'     => 'ucgaip_input',
                );

                $post_id = wp_insert_post($post_data);

                if ($post_id === 0 || is_wp_error($post_id)) {
                    wp_send_json_error('Failed to save user input and AI response.');
                } else {
                    update_post_meta($post_id, 'user_input', $prompt);
                    update_post_meta($post_id, 'ucgaip_response', $ucgaip_response);
                    update_post_meta($post_id, 'timestamp', current_time('mysql'));
                }
            }

            wp_send_json_success($data);
        }
    }
    wp_die();
}


add_action('wp_ajax_ucgaip_generate_text', 'ucgaip_generate_text');
add_action('wp_ajax_nopriv_ucgaip_generate_text', 'ucgaip_generate_text');


function ucgaip_generate_content() {

    check_ajax_referer('ucgaip_generate_nonce', 'nonce');

    $prompt_text = sanitize_text_field(wp_unslash($_POST['prompt']));
    $response = "Your response from Ai API";  // Modify this to get the actual response

    // Save user input and response as a custom post
    $post_data = array(
        'post_title'    => sanitize_text_field($prompt_text),
        'post_content'  => wp_kses_post($response),
        'post_status'   => 'publish',
        'post_type'     => 'ucgaip_input',
    );
    wp_insert_post($post_data);
    
    wp_send_json_success(array('response' => esc_html($response)));

    // echo $response;
    // wp_die();
}

function ucgaip_register_ai_settings() {
    register_setting('ucgaip-settings-group', 'ucgaip_api_key');
    register_setting('ucgaip-settings-group', 'ucgaip_prompt');
    register_setting('ucgaip-settings-group', 'ucgaip_button_text');
    register_setting('ucgaip-settings-group', 'ucgaip_button_color');
    register_setting('ucgaip-settings-group', 'ucgaip_border_radius');
    register_setting('ucgaip-settings-group', 'ucgaip_prompt_text');
    register_setting('ucgaip-settings-group', 'ucgaip_placeholder_text');
    register_setting('ucgaip-settings-group', 'ucgaip_store_inputs');
}
add_action('admin_init', 'ucgaip_register_ai_settings');

function ucgaip_settings_display() {
    ?>
    <div class="wrap">
        <h2>AI Plugin Settings</h2>

        <form method="post" action="options.php">
            <?php settings_fields('ucgaip-settings-group'); ?>
            <?php do_settings_sections('ucgaip-settings-group'); ?>
            <table class="form-table">
                <!-- Table content here -->

                <!-- Shortcode display -->
                <div class="shortcode-display">
                    <p>Use the following shortcode to display the AI Generator:</p>
                    <input id="shortcode" readonly type="text" value="[ucgaip-generator]" />
                </div>

                <!-- Settings fields -->
                <tr valign="top">
                    <th scope="row">OpenAi API Key</th>
                    <td>
                        <input type="text" name="ucgaip_api_key" value="<?php echo esc_attr(get_option('ucgaip_api_key')); ?>" />
                        <a href="https://elysehbiagini.com/how-to-get-an-openai-api-key//" target="_blank">How to get an OpenAi key?</a>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Prompt</th>
                    <td>
                        <textarea id="ucgaip-prompt-text" name="ucgaip_prompt" rows="5" cols="23" style="resize: both; overflow: auto;"><?php echo esc_attr(get_option('ucgaip_prompt')); ?></textarea>
                    </td>
                </tr>
                
                <!-- Placeholder -->
                <tr valign="top">
                    <th scope="row">Placeholder</th>
                    <td>
                        <textarea id="ucgaip-placeholder-text" name="ucgaip_placeholder_text" rows="2" cols="23" style="resize: both; overflow: auto;"><?php echo esc_attr(get_option('ucgaip_placeholder_text')); ?></textarea>
                    </td>
                </tr>

                <!-- Button Text -->
                <tr valign="top">
                    <th scope="row">Button Text</th>
                    <td>
                        <input type="text" name="ucgaip_button_text" value="<?php echo esc_attr(get_option('ucgaip_button_text')); ?>" />
                    </td>
                </tr>

                <!-- Button Color -->
                <tr valign="top">
                    <th scope="row">Button Color</th>
                    <td>
                        <input type="text" name="ucgaip_button_color" value="<?php echo esc_attr(get_option('ucgaip_button_color')); ?>" class="ucgaip-color-picker" />
                    </td>
                </tr>

                <!-- Border Radius -->
                <?php
                $border_radius_value = esc_attr(get_option('ucgaip_border_radius'));

                // Check if the value ends with any common CSS units. If not, append 'px'.
                if (!preg_match("/(px|em|%|rem|pt|vw|vh|vmin|vmax)$/", $border_radius_value) && is_numeric(trim($border_radius_value))) {
                    $border_radius_value .= 'px';
                }
                ?>
                <tr valign="top">
                    <th scope="row">Border Radius</th>
                    <td>
                        <input type="text" name="ucgaip_border_radius" value="<?php echo $border_radius_value; ?>" placeholder="e.g. 5px" />
                    </td>
                </tr>

                <!-- Store user inputs and responses -->
                <tr valign="top">
                    <th scope="row">Store user inputs and responses</th>
                    <td>
                        <label class="toggle-container">
                            <input type="checkbox" name="ucgaip_store_inputs" value="1" <?php checked(1, get_option('ucgaip_store_inputs'), true); ?> />
                            <span class="toggle-slider"></span>
                        </label>
                    </td>
                </tr>
                
                <!-- Daily Rate Limit (per IP) -->
                <tr valign="top">
                    <th scope="row">Daily Rate Limit (per IP)</th>
                    <td>
                        <input type="number" name="ucgaip_daily_rate_limit" value="100" disabled/>
                        <span class="pro-feature-lock">&#128274;</span>
                        <a href="https://elysehbiagini.com/aipro" target="_blank">This is a PRO feature!</a>
                    </td>
                </tr>

                <!-- Exceeded Limit Message -->
                <tr valign="top">
                    <th scope="row">Exceeded Limit Message</th>
                    <td>
                        <textarea id="ucgaip-exceeded-limit-message" name="ucgaip_exceeded_limit_message" rows="5" cols="50" style="resize: both; overflow: auto;" disabled>You have reached your daily usage limit. Please return tomorrow or subscribe for unlimited access!</textarea>
                        <span class="pro-feature-lock">&#128274;</span>
                        <a href="https://elysehbiagini.com/aipro" target="_blank">This is a PRO feature!</a>
                    </td>
                </tr>

                <!-- Add more generators -->
                <tr valign="top">
                    <th scope="row">Add more generators</th>
                    <td>
                        <button type="button" disabled>Add more generators</button>
                        <span class="pro-feature-lock">&#128274;</span>
                        <a href="https://elysehbiagini.com/aipro" target="_blank">This is a PRO feature!</a>
                    </td>
                </tr>
                <tr valign="top">
                    <td colspan="2">Do you want to add multiple generators with different prompts? <a href="https://elysehbiagini.com/aipro" target="_blank">Upgrade to pro!</a></td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}




// Add settings page for the content generator
function ucgaip_content_generator_menu() {
    // Main Menu
    add_menu_page('AI Generator Settings', 'AI Generator', 'manage_options', 'ucgaip_content_generator', 'ucgaip_settings_display',  'dashicons-admin-site-alt3', 57.5);
    
    // Submenu for 'Settings'
    add_submenu_page('ucgaip_content_generator', 'AI Generator Settings', 'Settings', 'manage_options', 'ucgaip_content_generator', 'ucgaip_settings_display');
}
add_action('admin_menu', 'ucgaip_content_generator_menu');

// Create custom post type for the content generator
function ucgaip_content_generator_create_post_type() {
    register_post_type('ucgaip_input',
        array(
            'labels' => array(
                'name' => __('User Inputs', 'ucgaip-content-generator'),
                'singular_name' => __('User Input', 'ucgaip-content-generator')
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_menu' => 'ucgaip_content_generator',
        )
    );
}
add_action('init', 'ucgaip_content_generator_create_post_type');

// Add new columns to the 'ucgaip_input' post type in the admin dashboard.
function ucgaip_add_admin_columns($columns) {
    unset($columns['date']);
    $columns['user_input'] = 'User Input';
    $columns['timestamp'] = 'Timestamp';
    $columns['date'] = 'Date';
    return $columns;
}
add_filter('manage_ucgaip_input_posts_columns', 'ucgaip_add_admin_columns');

function ucgaip_populate_admin_columns($column, $post_id) {
    switch ($column) {
        case 'user_input':
            $full_prompt = get_post_meta($post_id, 'user_input', true);
            $base_prompt = sanitize_textarea_field(get_option('ucgaip_prompt')); // Retrieve the base prompt from settings
            
            // Extracting the topic by removing the base prompt
            $topic = str_replace($base_prompt, '', $full_prompt);
            $topic = trim($topic); // Remove any leading/trailing whitespace
            
            // Display the wrapped base prompt and the topic
            echo '<span class="ucgaip-prompt-hidden">' . esc_html($base_prompt) . '</span>' . esc_html($topic);
            break;

        case 'ucgaip_response':
            echo esc_html(get_post_meta($post_id, 'ucgaip_response', true));
            break;

        case 'timestamp':
            echo esc_html(get_post_meta($post_id, 'timestamp', true));
            break;
    }
}



add_action('manage_ucgaip_input_posts_custom_column', 'ucgaip_populate_admin_columns', 10, 2);
if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
    add_action('admin_notices', 'ucgaip_content_generator_cache_notice');
}

function ucgaip_content_generator_cache_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php esc_html_e('Settings saved! If you have a caching plugin installed, please clear your cache to see the changes.', 'ucgaip-content-generator'); ?></p>
    </div>
    <?php
}


function ucgaip_save_border_radius( $value ) {
    if ( is_numeric($value) ) {
        return $value . 'px';
    }
    return $value;
}
add_filter( 'pre_update_option_Ai_border_radius', 'ucgaip_save_border_radius' );

   