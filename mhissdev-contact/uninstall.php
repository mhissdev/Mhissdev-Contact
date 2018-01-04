<?php
// Deny direct access
if(!defined('WP_UNINSTALL_PLUGIN'))
{
    die();
}

// Clean up database settings
delete_option('mhissdev_contact');