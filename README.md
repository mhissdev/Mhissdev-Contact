# Mhissdev-Contact
Mhissdev Contact is a WordPress contact form plugin, designed to be simple and secure.

## Features
* ‘Name’, ‘Email’, and ‘Message’ input fields.
* Mark-up specifically written for themes using Bootstrap CSS framework.
* Google Recaptcha anti-spam integration.
* Client-side validation using Parsley.

## Installation
Copy the folder and contents ‘mhissdev-contact’ into the plugins directory of your WordPress site, and activate the plugin via the plugins menu from within the WordPress administration area.

## Configuration
To utilise the Google Recaptcha feature you will need to genterate a ‘site key’ and ‘secret key’ from the Google Recapcha website. The plugin configuration can be accessed via the WordPress settings menu:-

* **Email Address To:**  The email address the message will be sent to
* **Email Address From:** This will be added to the email header (For example webmaster@yourdomain.com)
* **Google Recaptcha Site Key:** Your Recapcha site key supplied by Google
* **Google Recaptcha Secret Key:** Your Recapcha secret key supplied by Google
* **Include CSS:** This gives you the option to include the CSS styles used for client-side validation.

## Usage
Once the plugin has been configured, use the shortcode [mhissdevcontact] to display the form within your WordPress content.
