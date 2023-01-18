<?php

/**
 * 
 * Plugin Name: calculadora-cuotas
 * Description: Plugin for display a calculator in WooCommmerce
 * Version: 1.0.0
 * Text-Domain: options-plugin
 * 
 */

if (!defined('ABSPATH')) {
    die('Wrong way');
}


if (!class_exists('CalculadoraCuotas')) {
    class CalculadoraCuotas
    {

        public function __construct()
        {
            define('CALCULADORA_PATH', plugin_dir_path(__FILE__));

            define('CALCULADORA_URL', plugin_dir_path(__FILE__));

            require_once(CALCULADORA_PATH .
                '/vendor/autoload.php'
            );
        }

        public function initialize()
        {

            include_once(CALCULADORA_PATH .
                '/inc/utils.php'
            );
            include_once(CALCULADORA_PATH .
                '/inc/options-page.php'
            );

            include_once(CALCULADORA_PATH .
                '/inc/contact-form.php'
            );
        }
    }
    // Runs the constructor
    $CalculadoraCuotas = new CalculadoraCuotas;
    // Inicialize the class
    $CalculadoraCuotas->initialize();
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts_presupuesto');

function enqueue_custom_scripts_presupuesto()
{

    wp_enqueue_style('calculadora-cuotas', plugin_dir_url(__FILE__) . 'assets/css/contact-styles.css');
}
