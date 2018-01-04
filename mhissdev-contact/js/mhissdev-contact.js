// ReCAPTCHA Callback on successful completion
function onRecaptchaSuccess()
{
    // Give recaptcha hidden text a value to allow parsley to validate
    jQuery('#recaptcha_hidden').val('success');
}

// ReCAPTCHA Callback on expired
function onRecaptchaExpired()
{
    // Remove value from hidden text field
    jQuery('#recaptcha_hidden').val('');
}

// Initiate Parsley
jQuery(document).ready(function($){
    // Set inline validation for input and textarea fields
    $('#mhissdev_contact input').attr('data-parsley-trigger', 'focusout');
    $('#mhissdev_contact textarea').attr('data-parsley-trigger', 'focusout');

    // Set custome error messages
    $('#mhissdev_contact_name').attr('data-parsley-required-message', 'Please enter your name');
    $('#mhissdev_contact_email').attr('data-parsley-required-message', 'Please enter a valid email address');
    $('#mhissdev_contact_message').attr('data-parsley-required-message', 'Please enter your message');

    // Check Recaptcha element exists
    if($('#recaptcha_hidden').length)
    {
        $('#recaptcha_hidden').attr('data-parsley-required-message', 'Please ensure you are human');
    }

    // Initiate Parsley
    $('#mhissdev_contact').parsley();
});