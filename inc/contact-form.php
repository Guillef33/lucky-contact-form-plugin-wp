<?php

if (!defined('ABSPATH')) {
    die('Wrong way');
}

add_shortcode('calculadora-cuotas', 'show_contact_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_submission_posts_columns', 'custom_submission_columns');

add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts_presupuesto');

function setup_search()
{
    global $typenow;
    if ($typenow  === 'presupuesto') {
        add_filter('posts-search', 'submission_search_override', 10, 2);
    }
}

function submission_search_override($search, $query)
{


    global $wpdb;

    if ($query->is_main_query() && !empty($query->query['s'])) {
        $sql    = "
              or exists (
                  select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
                  and meta_key in ('name','email','phone')
                  and meta_value like %s
              )
          ";
        $like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
        $search = preg_replace(
            "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
            $wpdb->prepare($sql, $like),
            $search
        );
    }

    return $search;
}


function fill_submission_columns($column, $post_id)
{

    switch ($column) {
        case 'name':
            echo esc_html(get_post_meta($post_id, 'name', true));
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;
        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true));
            break;
    }
}

function custom_submission_columns($columns)
{
    // TODO Aceptar support de traducciones
    $columns = array(
        'cb' => $columns['cb'],
        'name' => 'Name', //_('Name', 'lucky-contact-form'),
        'email' => 'Email', //_('Email', 'lucky-contact-form'),
        'phone' => 'Phone', //_('Phone', 'lucky-contact-form'),
        //'message' => 'Message' //_('Message', 'lucky-contact-form'),

    );

    return $columns;
}


function create_meta_box()
{
    add_meta_box('custom_contact_form', 'Presupuesto', 'display_submission', 'presupuesto');
}

function display_submission()
{

    $post_metas = get_post_meta(get_the_ID());

    unset($post_metas['_edit_lock']);

    echo '<ul>';
    foreach ($post_metas as $key => $value) {
        echo esc_html('<li><strong>' . ucfirst($key) . "</strong>:</br>" . $value[0] . '</li>');
    }
}

function create_submissions_page()
{

    $args = [
        'public' => true,
        'has_archive' => true,
        'labels' => [
            'name' => 'Presupuestos',
            'singular_name' => "Presupuesto"
        ],
        // Disable to create new submissions
        // 'capabilities' => ['create_posts' => 'do_not_allow']
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => ['create_posts' => 'do_not_allow'],
        'map_meta_cap' => true
    ];

    register_post_type('presupuesto', $args);
}


function show_contact_form()
{
    include(CALCULADORA_PATH .
        '/templates/contact-form.php'
    );
}

function create_rest_endpoint()
{
    register_rest_route('v1/contact-form', 'submit', array(
        'methods' => 'POST',
        'callback' => 'handle_enquiry'
    ));
}

function handle_enquiry($data)
{
    $params = $data->get_params();

    // Set the fields from the form

    $field_name = sanitize_text_field($params['name']);
    $field_email = sanitize_text_field($params['email']);
    $field_phone = sanitize_text_field($params['phone']);



    if (!wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {
        return new WP_REST_Response('Message not sent', 422);
    }

    unset($params['_wpnonce']);
    unset($params['_wp_http_referer']);

    $sender_email = get_bloginfo('admin_email');

    var_dump($sender_email);

    $sender_name = get_bloginfo('name');

    // Sent the email
    $headers = [];
    $headers[] = "From: {$sender_name} <{$sender_email}> ";
    // $headers[] = "Cc: {$sender_name}";
    $headers[] = "Reply to: $field_name <$field_email>";
    $subject = "New message from: $field_name <br /> <br />";


    $message = " ";
    $message .= "Message has been sent from $field_name <br /> <br />";

    $postarray = [
        'post_title' => $field_name,
        'post_type' => 'presupuesto',
        'post_status' => 'publish'
    ];

    $post_id = wp_insert_post($postarray);

    foreach ($params as $label => $value) {
        $message .= ucfirst($label) . ":" . $value;
        add_post_meta($post_id, $label, sanitize_text_field($value));
    }

    wp_mail($sender_email, $subject, $message, $headers);
    wp_insert_post($postarray);

    return new WP_REST_Response('Message sent', 200);
}
