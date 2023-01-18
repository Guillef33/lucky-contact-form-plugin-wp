<?php

if (!defined('ABSPATH')) {
    die('Wrong way');
}

use Carbon_Fields\Field;
use Carbon_Fields\Container;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');


function load_carbon_fields()
{
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page()
{
    Container::make('theme_options', __('Calculadora-Cuotas'))
        ->set_icon('dashicons-email')
        ->add_fields(
            array(
                Field::make('checkbox', 'lucky_contact_form_active', __('Is Active'))
                    ->set_option_value('yes'),
                Field::make('text', 'lucky_contact_form_receipients', __('Recepient Email'))
                    ->set_attribute('placeholder', 'ej: your@email.com')
                    ->set_help_text('The email that the form is submitted to'),
                Field::make('media_gallery', 'lucky_contact_form_gallery', __('Media Gallery')),
                Field::make('rich_text', 'lucky_contact_confirmation', __('Confirmation Message'))
                    ->set_help_text('Type the message you want the submitter to receive'),
            )
        );
}
