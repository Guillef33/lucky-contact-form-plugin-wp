<?php

add_shortcode('lucky-contact-form', 'show_contact_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_submission_posts_columns', 'custom_submission_columns');

add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function setup_search()
{
    global $typenow;
    if ($typenow  === 'subsmission') {
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
            echo get_post_meta($post_id, 'name', true);
            break;
        case 'email':
            echo get_post_meta($post_id, 'email', true);
            break;
        case 'phone':
            echo get_post_meta($post_id, 'phone', true);
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
    add_meta_box('custom_contact_form', 'Submission', 'display_submission', 'submission');
}

function display_submission()
{

    $post_metas = get_post_meta(get_the_ID());

    unset($post_metas['_edit_lock']);

    echo '<ul>';
    foreach ($post_metas as $key => $value) {
        echo '<li><strong>' . ucfirst($key) . "</strong>:</br>" . $value[0] . '</li>';
    }
}

function create_submissions_page()
{

    $args = [
        'public' => true,
        'has_archive' => true,
        'labels' => [
            'name' => 'Submissions',
            'singular_name' => "Submission"
        ],
        // Disable to create new submissions
        // 'capabilities' => ['create_posts' => 'do_not_allow']
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => ['create_posts' => 'do_not_allow'],
        'map_meta_cap' => true
    ];

    register_post_type('submission', $args);
}


function show_contact_form()
{
    include(MY_PLUGIN_PATH .
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
    $headers[] = "Reply to: {$params['name']} <{$params['email']}>";
    $subject = "New message from: {$params['name']} <br /> <br />";


    $message = " ";
    $message .= "Message has been sent from {$params['name']} <br /> <br />";

    $postarray = [
        'post_title' => $params['name'],
        'post_type' => 'submission',
        'post_status' => 'publish'
    ];

    $post_id = wp_insert_post($postarray);

    foreach ($params as $label => $value) {
        $message .= ucfirst($label) . ":" . $value;
        add_post_meta($post_id, $label, $value);
    }

    wp_mail($sender_email, $subject, $message, $headers);

    wp_insert_post($postarray);


    return new WP_REST_Response('Message sent', 200);
}
