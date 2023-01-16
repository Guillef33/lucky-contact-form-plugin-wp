<?php

/**
 * 
 * Plugin Name: Lucky Contact Form
 * Description: Plugin for collect emails in the Lucky Store Theme
 * Version: 1.0.0
 * Text-Domain: options-plugin
 * 
 */

if (!defined('ABSPATH')) {
    die('Wrong way');
}


if (!class_exists('LuckyContactForm')) {
    class LuckyContactForm
    {

        public function __construct()
        {
            define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));

            define('MY_PLUGIN_URL', plugin_dir_path(__FILE__));

            require_once(MY_PLUGIN_PATH .
                '/vendor/autoload.php'
            );
        }

        public function initialize()
        {

            include_once(MY_PLUGIN_PATH .
                '/inc/utils.php'
            );
            include_once(MY_PLUGIN_PATH .
                '/inc/options-page.php'
            );

            include_once(MY_PLUGIN_PATH .
                '/inc/contact-form.php'
            );
        }
    }
    // Runs the constructor
    $LuckyContactForm = new LuckyContactForm;
    // Inicialize the class
    $LuckyContactForm->initialize();
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function enqueue_custom_scripts()
{

    wp_enqueue_style('lucky-contact-form', plugin_dir_url(__FILE__) . 'assets/css/contact-styles.css');
}
