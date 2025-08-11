<?php
/**
 * Plugin Name: WP AI Excerpt
 * Plugin URI: https://github.com/factus10/WP-AI-Excerpt
 * Description: Automatically generate excerpts for posts and pages using AI
 * Version: 1.0
 * Author: David
 * Author URI: https://github.com/factus10
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-ai-excerpt
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_AI_EXCERPT_VERSION', '1.0');
define('WP_AI_EXCERPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_AI_EXCERPT_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main plugin class
 */
class WP_AI_Excerpt {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('add_meta_boxes', array($this, 'add_excerpt_meta_box'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX hooks
        add_action('wp_ajax_generate_ai_excerpt', array($this, 'ajax_generate_excerpt'));
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        add_option('wp_ai_excerpt_default_length', 150);
        add_option('wp_ai_excerpt_api_provider', 'openai');
        add_option('wp_ai_excerpt_api_key', '');
        add_option('wp_ai_excerpt_anthropic_api_key', '');
        add_option('wp_ai_excerpt_model', 'gpt-3.5-turbo');
        add_option('wp_ai_excerpt_anthropic_model', 'claude-3-haiku-20240307');
        add_option('wp_ai_excerpt_prompt', 'Create a concise and informative excerpt of approximately {length} words from the following content. The excerpt should accurately summarize the main points without being overly promotional:');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('WP AI Excerpt Settings', 'wp-ai-excerpt'),
            __('AI Excerpt', 'wp-ai-excerpt'),
            'manage_options',
            'wp-ai-excerpt',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wp_ai_excerpt_settings', 'wp_ai_excerpt_api_provider');
        register_setting('wp_ai_excerpt_settings', 'wp_ai_excerpt_api_key');
        register_setting('wp_ai_excerpt_settings', 'wp_ai_excerpt_anthropic_api_key');
        register_setting('wp_ai_excerpt_settings', 'wp_ai_excerpt_default_length');
        register_setting('wp_ai_excerpt_settings', 'wp_ai_excerpt_model');
        register_setting('wp_ai_excerpt_settings', 'wp_ai_excerpt_anthropic_model');
        register_setting('wp_ai_excerpt_settings', 'wp_ai_excerpt_prompt');
        
        add_settings_section(
            'wp_ai_excerpt_main',
            __('AI Excerpt Settings', 'wp-ai-excerpt'),
            array($this, 'settings_section_callback'),
            'wp-ai-excerpt'
        );
        
        add_settings_field(
            'wp_ai_excerpt_api_provider',
            __('API Provider', 'wp-ai-excerpt'),
            array($this, 'api_provider_field_callback'),
            'wp-ai-excerpt',
            'wp_ai_excerpt_main'
        );
        
        add_settings_field(
            'wp_ai_excerpt_api_key',
            __('OpenAI API Key', 'wp-ai-excerpt'),
            array($this, 'api_key_field_callback'),
            'wp-ai-excerpt',
            'wp_ai_excerpt_main'
        );
        
        add_settings_field(
            'wp_ai_excerpt_anthropic_api_key',
            __('Anthropic API Key', 'wp-ai-excerpt'),
            array($this, 'anthropic_api_key_field_callback'),
            'wp-ai-excerpt',
            'wp_ai_excerpt_main'
        );
        
        add_settings_field(
            'wp_ai_excerpt_default_length',
            __('Default Excerpt Length (words)', 'wp-ai-excerpt'),
            array($this, 'default_length_field_callback'),
            'wp-ai-excerpt',
            'wp_ai_excerpt_main'
        );
        
        add_settings_field(
            'wp_ai_excerpt_model',
            __('AI Model', 'wp-ai-excerpt'),
            array($this, 'model_field_callback'),
            'wp-ai-excerpt',
            'wp_ai_excerpt_main'
        );
        
        add_settings_field(
            'wp_ai_excerpt_anthropic_model',
            __('Anthropic Model', 'wp-ai-excerpt'),
            array($this, 'anthropic_model_field_callback'),
            'wp-ai-excerpt',
            'wp_ai_excerpt_main'
        );
        
        add_settings_field(
            'wp_ai_excerpt_prompt',
            __('AI Prompt Template', 'wp-ai-excerpt'),
            array($this, 'prompt_field_callback'),
            'wp-ai-excerpt',
            'wp_ai_excerpt_main'
        );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure the AI excerpt generation settings.', 'wp-ai-excerpt') . '</p>';
    }
    
    /**
     * API provider field callback
     */
    public function api_provider_field_callback() {
        $provider = get_option('wp_ai_excerpt_api_provider', 'openai');
        ?>
        <select id="wp_ai_excerpt_api_provider" name="wp_ai_excerpt_api_provider" class="wp-ai-excerpt-provider-select">
            <option value="openai" <?php selected($provider, 'openai'); ?>>OpenAI</option>
            <option value="anthropic" <?php selected($provider, 'anthropic'); ?>>Anthropic (Claude)</option>
        </select>
        <p class="description"><?php _e('Select which AI provider to use for excerpt generation.', 'wp-ai-excerpt'); ?></p>
        <?php
    }
    
    /**
     * API key field callback
     */
    public function api_key_field_callback() {
        $api_key = get_option('wp_ai_excerpt_api_key');
        $provider = get_option('wp_ai_excerpt_api_provider', 'openai');
        $style = $provider !== 'openai' ? 'display:none;' : '';
        echo '<div class="wp-ai-excerpt-openai-fields" style="' . $style . '">';
        echo '<input type="password" id="wp_ai_excerpt_api_key" name="wp_ai_excerpt_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your OpenAI API key. Get one at', 'wp-ai-excerpt') . ' <a href="https://platform.openai.com/" target="_blank">platform.openai.com</a></p>';
        echo '</div>';
    }
    
    /**
     * Anthropic API key field callback
     */
    public function anthropic_api_key_field_callback() {
        $api_key = get_option('wp_ai_excerpt_anthropic_api_key');
        $provider = get_option('wp_ai_excerpt_api_provider', 'openai');
        $style = $provider !== 'anthropic' ? 'display:none;' : '';
        echo '<div class="wp-ai-excerpt-anthropic-fields" style="' . $style . '">';
        echo '<input type="password" id="wp_ai_excerpt_anthropic_api_key" name="wp_ai_excerpt_anthropic_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your Anthropic API key. Get one at', 'wp-ai-excerpt') . ' <a href="https://console.anthropic.com/" target="_blank">console.anthropic.com</a></p>';
        echo '</div>';
    }
    
    /**
     * Default length field callback
     */
    public function default_length_field_callback() {
        $length = get_option('wp_ai_excerpt_default_length', 150);
        echo '<input type="number" id="wp_ai_excerpt_default_length" name="wp_ai_excerpt_default_length" value="' . esc_attr($length) . '" min="25" max="500" />';
        echo '<p class="description">' . __('Default number of words for generated excerpts.', 'wp-ai-excerpt') . '</p>';
    }
    
    /**
     * Model field callback
     */
    public function model_field_callback() {
        $model = get_option('wp_ai_excerpt_model', 'gpt-3.5-turbo');
        $provider = get_option('wp_ai_excerpt_api_provider', 'openai');
        $style = $provider !== 'openai' ? 'display:none;' : '';
        ?>
        <div class="wp-ai-excerpt-openai-fields" style="<?php echo $style; ?>">
            <select id="wp_ai_excerpt_model" name="wp_ai_excerpt_model">
                <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>>GPT-4</option>
                <option value="gpt-4-turbo-preview" <?php selected($model, 'gpt-4-turbo-preview'); ?>>GPT-4 Turbo</option>
            </select>
            <p class="description"><?php _e('Select the OpenAI model to use for excerpt generation.', 'wp-ai-excerpt'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Anthropic model field callback
     */
    public function anthropic_model_field_callback() {
        $model = get_option('wp_ai_excerpt_anthropic_model', 'claude-3-haiku-20240307');
        $provider = get_option('wp_ai_excerpt_api_provider', 'openai');
        $style = $provider !== 'anthropic' ? 'display:none;' : '';
        ?>
        <div class="wp-ai-excerpt-anthropic-fields" style="<?php echo $style; ?>">
            <select id="wp_ai_excerpt_anthropic_model" name="wp_ai_excerpt_anthropic_model">
                <option value="claude-3-haiku-20240307" <?php selected($model, 'claude-3-haiku-20240307'); ?>>Claude 3 Haiku</option>
                <option value="claude-3-sonnet-20240229" <?php selected($model, 'claude-3-sonnet-20240229'); ?>>Claude 3 Sonnet</option>
                <option value="claude-3-opus-20240229" <?php selected($model, 'claude-3-opus-20240229'); ?>>Claude 3 Opus</option>
                <option value="claude-3-5-sonnet-20241022" <?php selected($model, 'claude-3-5-sonnet-20241022'); ?>>Claude 3.5 Sonnet</option>
            </select>
            <p class="description"><?php _e('Select the Anthropic model to use for excerpt generation.', 'wp-ai-excerpt'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Prompt field callback
     */
    public function prompt_field_callback() {
        $default_prompt = 'Create a concise and informative excerpt of approximately {length} words from the following content. The excerpt should accurately summarize the main points without being overly promotional:';
        $prompt = get_option('wp_ai_excerpt_prompt', $default_prompt);
        ?>
        <textarea id="wp_ai_excerpt_prompt" name="wp_ai_excerpt_prompt" rows="4" class="large-text"><?php echo esc_textarea($prompt); ?></textarea>
        <p class="description">
            <?php _e('Customize the prompt sent to the AI. Use {length} as a placeholder for the word count.', 'wp-ai-excerpt'); ?><br>
            <?php _e('Examples:', 'wp-ai-excerpt'); ?><br>
            <em><?php _e('• For neutral tone: "Summarize the following content in {length} words, focusing on key facts and information:"', 'wp-ai-excerpt'); ?></em><br>
            <em><?php _e('• For academic tone: "Write a scholarly abstract of {length} words for the following content:"', 'wp-ai-excerpt'); ?></em><br>
            <em><?php _e('• For casual tone: "Write a friendly, conversational summary of about {length} words for this content:"', 'wp-ai-excerpt'); ?></em>
        </p>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('wp_ai_excerpt_settings');
                do_settings_sections('wp-ai-excerpt');
                submit_button();
                ?>
            </form>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#wp_ai_excerpt_api_provider').on('change', function() {
                var provider = $(this).val();
                if (provider === 'openai') {
                    $('.wp-ai-excerpt-openai-fields').show();
                    $('.wp-ai-excerpt-anthropic-fields').hide();
                } else {
                    $('.wp-ai-excerpt-openai-fields').hide();
                    $('.wp-ai-excerpt-anthropic-fields').show();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Add meta box to post editor
     */
    public function add_excerpt_meta_box() {
        $post_types = get_post_types(array('public' => true), 'names');
        
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'excerpt')) {
                add_meta_box(
                    'wp_ai_excerpt_meta_box',
                    __('Generate AI Excerpt', 'wp-ai-excerpt'),
                    array($this, 'render_meta_box'),
                    $post_type,
                    'normal',
                    'high'
                );
            }
        }
    }
    
    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        $default_length = get_option('wp_ai_excerpt_default_length', 150);
        ?>
        <div id="wp-ai-excerpt-generator">
            <p><?php _e('Generate an AI-powered excerpt for this post.', 'wp-ai-excerpt'); ?></p>
            
            <label for="wp_ai_excerpt_length">
                <?php _e('Excerpt length (words):', 'wp-ai-excerpt'); ?>
                <input type="number" id="wp_ai_excerpt_length" value="<?php echo esc_attr($default_length); ?>" min="25" max="500" />
            </label>
            
            <button type="button" id="wp_ai_excerpt_generate" class="button button-primary">
                <?php _e('Generate Excerpt', 'wp-ai-excerpt'); ?>
            </button>
            
            <div id="wp_ai_excerpt_status"></div>
            
            <div id="wp_ai_excerpt_result" style="display:none;">
                <h4><?php _e('Generated Excerpt:', 'wp-ai-excerpt'); ?></h4>
                <textarea id="wp_ai_excerpt_text" rows="5" class="large-text"></textarea>
                <button type="button" id="wp_ai_excerpt_use" class="button">
                    <?php _e('Use This Excerpt', 'wp-ai-excerpt'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $post;
        
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        if ($post && post_type_supports($post->post_type, 'excerpt')) {
            // Enqueue jQuery UI for dialog
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('wp-jquery-ui-dialog');
            
            wp_enqueue_script(
                'wp-ai-excerpt-admin',
                WP_AI_EXCERPT_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-dialog'),
                WP_AI_EXCERPT_VERSION,
                true
            );
            
            wp_localize_script('wp-ai-excerpt-admin', 'wpAiExcerpt', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_ai_excerpt_nonce'),
                'post_id' => $post->ID,
                'generating' => __('Generating excerpt...', 'wp-ai-excerpt'),
                'error' => __('Error generating excerpt. Please try again.', 'wp-ai-excerpt'),
                'defaultLength' => get_option('wp_ai_excerpt_default_length', 150),
            ));
            
            wp_enqueue_style(
                'wp-ai-excerpt-admin',
                WP_AI_EXCERPT_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WP_AI_EXCERPT_VERSION
            );
        }
    }
    
    /**
     * AJAX handler for excerpt generation
     */
    public function ajax_generate_excerpt() {
        check_ajax_referer('wp_ai_excerpt_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to perform this action.', 'wp-ai-excerpt'));
        }
        
        $post_id = intval($_POST['post_id']);
        $length = intval($_POST['length']);
        
        if (!$post_id || !$length) {
            wp_send_json_error(__('Invalid parameters.', 'wp-ai-excerpt'));
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(__('Post not found.', 'wp-ai-excerpt'));
        }
        
        // Get post content
        $content = wp_strip_all_tags($post->post_content);
        
        // Generate excerpt using OpenAI API
        $excerpt = $this->generate_excerpt_with_ai($content, $length);
        
        if (is_wp_error($excerpt)) {
            wp_send_json_error($excerpt->get_error_message());
        }
        
        wp_send_json_success(array('excerpt' => $excerpt));
    }
    
    /**
     * Generate excerpt using AI API
     */
    private function generate_excerpt_with_ai($content, $length) {
        $provider = get_option('wp_ai_excerpt_api_provider', 'openai');
        
        if ($provider === 'anthropic') {
            return $this->generate_excerpt_with_anthropic($content, $length);
        } else {
            return $this->generate_excerpt_with_openai($content, $length);
        }
    }
    
    /**
     * Generate excerpt using OpenAI API
     */
    private function generate_excerpt_with_openai($content, $length) {
        $api_key = get_option('wp_ai_excerpt_api_key');
        $model = get_option('wp_ai_excerpt_model', 'gpt-3.5-turbo');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('OpenAI API key is not configured.', 'wp-ai-excerpt'));
        }
        
        // Get custom prompt template or use default
        $default_prompt = 'Create a concise and informative excerpt of approximately {length} words from the following content. The excerpt should accurately summarize the main points without being overly promotional:';
        $prompt_template = get_option('wp_ai_excerpt_prompt', $default_prompt);
        
        // Replace {length} placeholder with actual length
        $prompt_instruction = str_replace('{length}', $length, $prompt_template);
        
        // Combine prompt with content
        $prompt = $prompt_instruction . "\n\n" . substr($content, 0, 3000); // Limit content to avoid token limits
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => $model,
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'You are a professional content writer who creates excerpts based on specific instructions.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => $length * 2, // Approximate tokens
                'temperature' => 0.7,
            )),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']['message']);
        }
        
        if (isset($data['choices'][0]['message']['content'])) {
            $excerpt = trim($data['choices'][0]['message']['content']);
            // Remove quotes from beginning and end if present
            $excerpt = trim($excerpt, '"\'');
            // Remove smart quotes as well
            $excerpt = trim($excerpt, '""''');
            return $excerpt;
        }
        
        return new WP_Error('unexpected_response', __('Unexpected response from API.', 'wp-ai-excerpt'));
    }
    
    /**
     * Generate excerpt using Anthropic API
     */
    private function generate_excerpt_with_anthropic($content, $length) {
        $api_key = get_option('wp_ai_excerpt_anthropic_api_key');
        $model = get_option('wp_ai_excerpt_anthropic_model', 'claude-3-haiku-20240307');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Anthropic API key is not configured.', 'wp-ai-excerpt'));
        }
        
        // Get custom prompt template or use default
        $default_prompt = 'Create a concise and informative excerpt of approximately {length} words from the following content. The excerpt should accurately summarize the main points without being overly promotional:';
        $prompt_template = get_option('wp_ai_excerpt_prompt', $default_prompt);
        
        // Replace {length} placeholder with actual length
        $prompt_instruction = str_replace('{length}', $length, $prompt_template);
        
        // Combine prompt with content
        $prompt = $prompt_instruction . "\n\n" . substr($content, 0, 3000); // Limit content to avoid token limits
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'headers' => array(
                'x-api-key' => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => $model,
                'max_tokens' => $length * 3, // Approximate tokens (more generous for Claude)
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'system' => 'You are a professional content writer who creates excerpts based on specific instructions.',
            )),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']['message']);
        }
        
        if (isset($data['content'][0]['text'])) {
            $excerpt = trim($data['content'][0]['text']);
            // Remove quotes from beginning and end if present
            $excerpt = trim($excerpt, '"\'');
            // Remove smart quotes as well
            $excerpt = trim($excerpt, '""''');
            return $excerpt;
        }
        
        return new WP_Error('unexpected_response', __('Unexpected response from Anthropic API.', 'wp-ai-excerpt'));
    }
}

// Initialize the plugin
WP_AI_Excerpt::get_instance();