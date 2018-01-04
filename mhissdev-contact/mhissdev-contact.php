<?php
/*
Plugin Name: Mhissdev Contact
Description: A simple contact form
Version: 0.1
License: GPL2
*/

// Deny direct access
if(!defined('ABSPATH')) die;

// Check for naming colliisions
if(!class_exists('Mhissdev_contact'))
{
    // Class definition
    class Mhissdev_contact{

        // Form data
        private $form_data = [];

        // Validation errors
        private $validation_errors = [];

        // HTML output for plugin
        private $output = '';

        // Email from address
        private $email_from = '';

        // Email to address
        private $email_to = '';

        // Recaptcha site key
        private $site_key = '';

        // Recaptca secret key
        private $secret_key = '';


        /**
        * Class constructor
        */
        public function __construct()
        {
            // Register settings page
            add_action('admin_menu', array($this, 'register_menu'));

            // Register settings
            add_action('admin_init', array($this, 'register_settings'));

            // Add shortcode
            add_shortcode('mhissdevcontact', array($this, 'shortcode'));

            // Enqueue styles
            $options = get_option('mhissdev_contact');

            if(isset($options['include_css']) && $options['include_css'] == 'on')
            {
                add_action('wp_enqueue_scripts', array($this,'add_styles'));
            }
            

            // Register scripts
            add_action('wp_enqueue_scripts', array($this,'register_scripts'));
            

            // Set default form data values
            $this->form_data['mhissdev_contact_name'] = '';
            $this->form_data['mhissdev_contact_email'] = '';
            $this->form_data['mhissdev_contact_message'] = '';
        }


        /**
        * Register settings menu 
        */
        public function register_menu()
        {
            add_options_page('Mhissdev Contact Settings', 'Mhissdev contact', 'manage_options', 'mhissdev-contact', array($this, 'settings_page'));
        }


        /**
        * Register Settings
        */
        public function register_settings()
        {   
            register_setting('mhissdev-contact-settings', 'mhissdev_contact');
        }


        /**
        * Add CSS styles
        */
        public function add_styles()
        {
            // Parsley
            wp_enqueue_style('mhissdev-contact-style', plugin_dir_url(__FILE__) . 'css/mhissdev-contact.css');
        }


        /**
        * Register scripts
        */
        public function register_scripts()
        {
            // Parsley
            wp_register_script('parsley-js', plugin_dir_url(__FILE__) . 'js/parsley.min.js', array('jquery'), '', true);

            // Google Recaptcha
            wp_register_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', '', '', true);

            // Mhissdev Contact JS
            wp_register_script('mhissdev-contact', plugin_dir_url(__FILE__) . 'js/mhissdev-contact.js', array('jquery'), '', true);
        }


        /**
        * Settings page
        */
        public function settings_page()
        {
            // Get options
            $options = get_option('mhissdev_contact');

            ?>
            <div class="wrap">
                <h1>Mhissdev Contact Settings</h1>
                <form action="options.php" method="post">
                    <?php settings_fields('mhissdev-contact-settings'); ?>
                    <?php do_settings_sections('mhissdev-contact-settings'); ?>
                    <table>
                        <tr>
                            <td>Email Address To:</td>
                        </tr>
                        <tr>
                            <td><input type="text" name="mhissdev_contact[email_to]" value="<?php echo esc_attr($options['email_to']); ?>" size="100" placeholder="youremail@example.com"></td>
                        </tr>
                        <tr>
                            <td>Email Address From:</td>
                        </tr>
                        <tr>
                            <td><input type="text" name="mhissdev_contact[email_from]" value="<?php echo esc_attr($options['email_from']); ?>" size="100" placeholder="webmaster@example.com"></td>
                        </tr>
                        <tr>
                            <td>Google Recaptcha Site Key:</td>
                        </tr>
                        <tr>
                            <td><input type="text" name="mhissdev_contact[site_key]" value="<?php echo esc_attr($options['site_key']); ?>" size="100" placeholder="Recaptcha Site Key"></td>
                        </tr>
                        <tr>
                            <td>Google Recaptcha Secret Key:</td>
                        </tr>
                        <tr>
                            <td><input type="text" name="mhissdev_contact[secret_key]" value="<?php echo esc_attr($options['secret_key']); ?>" size="100" placeholder="Recaptcha Secret Key"></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" name="mhissdev_contact[include_css]" <?php if(isset($options['include_css'])){ echo 'checked';} ?> >Include CSS</td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
        }


        /**
        * Shortcode
        */
        public function shortcode($att = [])
        {
            // Enqueue scripts and styles
            wp_enqueue_style('mhissdev-contact-style');
            wp_enqueue_script('google-recaptcha');
            wp_enqueue_script('parsley-js');
            wp_enqueue_script('mhissdev-contact');

            // Process Options
            $this->process_options();
            
            // Display form status
            $display_form = true;

            // Check we have POST data
            if(isset($_POST['mhissdev_contact_submit']) && !empty($_POST['mhissdev_contact_submit']))
            {
                // Verify nonce
                if(!isset($_POST['mhissdev_contact_wpnonce']) || !wp_verify_nonce($_POST['mhissdev_contact_wpnonce'], 'mhissdev_contact'))
                {
                    die('Invalid Nonce!');
                }

                // Process form POST data
                $this->process_form_data();

                // Validate form
                $this->validate_form_data();

                // Check for validation errors
                if(count($this->validation_errors) == 0)
                {
                    // Form validated OK
                    $display_form = false;

                    // Send Message
                    $this->send_message();

                    // Display success message
                    $this->output .= '<div class="alert alert-success">Thank you. Your message has been successfully sent!</div>';
                }
                else
                {
                    // Form has validation errors
                    $this->output .= $this->get_validation_errors_html();
                }

            }

            // Display form if necessary
            if($display_form === true)
            {
                $this->output .= $this->get_form_html();
            }

            // Retun HTML output
            return $this->output;
        }


        /**
        * Get form HTML
        */
        private function get_form_html()
        {
            // Get current URL
            global $wp;
            $current_url = home_url($wp->request);

            // Build nonce HTML
            $nonce_html = wp_nonce_field('mhissdev_contact', 'mhissdev_contact_wpnonce', true, false);

            // Build form HTML
            $html = '
                <form action="' . $current_url . '" method="post" accept-charset="utf-8" id="mhissdev_contact">
                    '. $nonce_html .'
                    <div class="form-group">
                        <label for="mhissdev_contact_name">Your name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mhissdev_contact_name" name="mhissdev_contact_name" placeholder="Enter name" 
                        maxlength="64" value="' . $this->form_data['mhissdev_contact_name'] . '" required>
                    </div>
                    <div class="form-group">
                        <label for="mhissdev_contact_email">Your email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="mhissdev_contact_email" name="mhissdev_contact_email" placeholder="Enter email" 
                        maxlength="64" value="' . $this->form_data['mhissdev_contact_email'] . '" required>
                    </div>
                    <div class="form-group">
                        <label for="mhissdev_contact_messsage">Your message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="mhissdev_contact_message" rows="6" name="mhissdev_contact_message" 
                        placeholder="Enter your message..." maxlength="1000" required>' . $this->form_data['mhissdev_contact_message'] . '</textarea>
                    </div>';

            // Google recaptcha
            if($this->site_key != '' && $this->secret_key != '')
            {
                $html .= '
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey="' . $this->site_key . '" data-callback="onRecaptchaSuccess" data-expired-callback="onRecaptchaExpired"></div>
                        <input type="text" id="recaptcha_hidden" required>
                    </div>
                ';
            }

            // Submit button
            $html .= '
                    <div class="form-group">
                        <input class="btn-lg btn-primary" type="submit" name="mhissdev_contact_submit" value="Send Message">
                    </div>
                </form>
            ';

            // Return HTML
            return $html;
        }


        /**
        * Process Options
        */
        private function process_options()
        {
            // Set values from options
            $options = get_option('mhissdev_contact');

            $this->email_to = $options['email_to'];
            $this->email_from = $options['email_from'];
            $this->site_key = $options['site_key'];
            $this->secret_key = $options['secret_key'];

            // Validate email adresses
            if(filter_var($this->email_to, FILTER_VALIDATE_EMAIL) == false)
            {
                $this->output .= "<p><strong>Warning:</strong> A valid 'to' email address must be set via the plugin settings</p>";
            }

            if(filter_var($this->email_from, FILTER_VALIDATE_EMAIL) == false)
            {
                $this->output .= "<p><strong>Warning:</strong> A valid 'from' email address must be set via the plugin settings</p>";
            }
            
        }


        /**
        * Process form data
        */
        private function process_form_data()
        {
            $this->form_data['mhissdev_contact_name'] = isset($_POST['mhissdev_contact_name']) ? sanitize_text_field($_POST['mhissdev_contact_name']) : '';
            $this->form_data['mhissdev_contact_email'] = isset($_POST['mhissdev_contact_email']) ? sanitize_email($_POST['mhissdev_contact_email']) : '';
            $this->form_data['mhissdev_contact_message'] = isset($_POST['mhissdev_contact_message']) ? sanitize_textarea_field($_POST['mhissdev_contact_message']) : '';
        }


        /**
        * Validate form data
        */
        private function validate_form_data()
        {
            // Name field
            $length = mb_strlen($this->form_data['mhissdev_contact_name']);

            if($length == 0)
            {
                $this->validation_errors[] = 'Name is a required field';
            }
            else if($length > 64)
            {
                $this->validation_errors[] = 'Name must be less than 65 characters long';
            }

            // Email field
            $length = mb_strlen($this->form_data['mhissdev_contact_email']);

            if($length == 0)
            {
                $this->validation_errors[] = 'Email is a required field';
            }
            else if($length > 64)
            {
                $this->validation_errors[] = 'Email must be less than 65 characters long';
            }
            else if(filter_var($this->form_data['mhissdev_contact_email'], FILTER_VALIDATE_EMAIL) == false)
            {
                $this->validation_errors[] = 'A valid email address is required';
            }

            // Message field
            $length = mb_strlen($this->form_data['mhissdev_contact_message']);

            if($length == 0)
            {
                $this->validation_errors[] = 'Message is a required field';
            }
            else if($length > 999)
            {
                $this->validation_errors[] = 'Message must be less than 1000 characters long';
            }

            // Verify Google recaptcha
            if($this->verify_recaptcha() !== true)
            {
                $this->validation_errors[] = 'Please ensure that you are human';
            }
        }


        /**
        * Verify Google Recaptcha
        */
        private function verify_recaptcha()
        {
            // Check we have POST data from recaptcha
            if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response']))
            {
                // Get user IP
                $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

                // Build URL string
                $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $this->secret_key;
                $url .= '&response=' . $_POST['g-recaptcha-response'] . '&remoteip=' . $ip;

                // Get response from Google API
                $response = json_decode(file_get_contents($url), true);

                if($response["success"] === true)
                {
                    // Successful verifiction
                    return true;
                }
            }

            // Unable to verify recaptcha
            return false;
        }


        /**
        * Get validation errors HTML
        */
        private function get_validation_errors_html()
        {
            // HTML string
            $html = '';

            if(count($this->validation_errors) > 0)
            {
                $html .= '<div class="alert alert-danger">';
                $html .= '<p>Validation errors occurred! Please fix the following problems:-</p><ul>';

                // Output list of errors
                foreach($this->validation_errors as $error)
                {
                    $html .= '<li>' . $error . '</li>';
                }

                $html .= '</ul></div>';
            }

            // Return HTML string
            return $html;
        }


        /**
        * Send email meassage
        */
        private function send_message()
        {
            // Build email headers
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: <" . $this->email_from . ">\r\n";

            // Build email body
            $body = '<p>The following enquiry has been made:-</p>';
            $body .= '<p><strong>Name: ' . $this->form_data['mhissdev_contact_name'];
            $body .= ', Email: ' . $this->form_data['mhissdev_contact_email'] . '</strong></p>';
            $body .= '<p>' . nl2br($this->form_data['mhissdev_contact_message']) . '</p>';

            // Send email
            mail($this->email_to, 'Customer Enquiry', $body, $headers);
        }

    }

    // Initiate class
    $mhissdev_contact = new Mhissdev_contact();
}