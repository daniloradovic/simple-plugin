<?php
/*
Plugin Name: Simple Plugin Test
Plugin URI: http://simple-plugin-test.test
description: >-
a plugin to create awesomeness and spread joy
Version: 1.2
Author: Danilo Radovic
*/


define( 'SUBMITTIONS_PAGE_SLUG', 'simple-form-submissions' );

/**
 * Set up the required form submissions table
 */
register_activation_hook( __FILE__, 'setup_simple_content_table' );
function setup_simple_content_table() {
    // instantiate the global $wpdb object to interact with the database
    global $wpdb;

    $table_name = $wpdb->prefix . 'content_submissions';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar (100) NOT NULL,
        email varchar (100) NOT NULL,
        content varchar (200) NOT NULL,
        PRIMARY KEY (id)
    )";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
}

/**
 * add shortcode for simple form submit
 */
add_shortcode( 'simpleform', 'contact_form' );
function contact_form() {
    ob_start();
    ?>
    <form method="post">
        <input type="hidden" name="sc-simple-form" value="submit">
        <div class="sc-form-wrapper">
            <div class="sc-form-content">
                <label for="name">Name (required)</label>
                <input class="input-field" type="text" id="name" name="sc-name" placeholder="Name">
            </div>
            <div class="sc-form-content">
                <label for="email">Email address (required)</label>
                <input class="input-field" type="text" id="email" name="sc-email" placeholder="Email address">
            </div>
            <div class="sc-form-content">
                <label for="description">Description</label>
                <input class="input-field" type="text" id="email" name="sc-description" placeholder="Description area">
            </div>
            <div class="submit-wrapper">
                <input class="submit" type="submit" id="submit" name="submit" value="Submit">
            </div>
        </div>
    </form>
    <?php
    $form = ob_get_clean();
    return $form;
}

/**
 * add action hook to process simple form and save data
 */
add_action('wp', 'process_sc_form');
function process_sc_form() {
    if( !isset( $_POST["sc-simple-form"]) ) {
        return;
    }
    $fields = '';
    if( empty($_POST["sc-email"])) {
        $fields .= '<br>' .  'email';
    }

    if( empty($_POST["sc-name"])) {
        $fields .= '<br>' . 'name';
    }

    $alert = '
        <div class="alert">
          Please fill the required fields.
          ' . $fields . '
        </div>
        ';

    if(empty($_POST["sc-email"]) || empty($_POST["sc-name"])) {
        echo $alert;
        return;
    }

    $name = $_POST["sc-name"];
    $email = $_POST["sc-email"];
    $description = $_POST["sc-description"];

    // instantiate the global $wpdb object to interact with the database
    global $wpdb;

    $table_name = $wpdb->prefix . 'content_submissions';

    $wpdb->insert($table_name, array(
        'name' => $name,
        'email' => $email,
        'content' => $description
    ));

    wp_redirect(admin_url('/admin.php?page=simple-form-submissions'));
}

/**
 * include custom js file for the simple test plugin
 */
add_action('admin_enqueue_scripts','simple_custom_js');
function simple_custom_js() {
    wp_register_script(
        'simple-custom-js-script',
        plugin_dir_url( __FILE__ ) . '/js/simple_custom_js.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
    wp_enqueue_script( 'simple-custom-js-script' );
}

/**
 * Include custom css
 */
wp_enqueue_style('simple-plugin-style', plugin_dir_url( __FILE__ ) . '/css/simple_plugin_style.css');

/**
 * Create an admin page to show the form submissions
 */
add_action('admin_menu', 'simple_submenu', 11);
function simple_submenu() {
    add_menu_page(
        esc_html__( 'Admin Page', 'simple' ),
        esc_html__( 'Admin Page', 'simple' ),
        'manage_options',
        'simple-form-submissions',
        'render_admin_submission_page',
        'dashicons-admin-tools'
    );
}

function render_admin_submission_page() {
    $data = get_simple_form_submissions();

    ?>
    <div class="simple-table-wrapper">
        <h1>Submissions table</h1>
        <table class="simple-plugin-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Content</th>
            </tr>
            </thead>
            <?php foreach ($data as $entry){ ?>
                <tr>
                    <td><?php echo $entry->name?></td>
                    <td><?php echo $entry->email?></td>
                    <td><?php echo $entry->content?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php
}


/**
 * Get all the form submissions
 *
 * @return array|object|null
 */
function get_simple_form_submissions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'content_submissions';

    $sql     = "SELECT * FROM $table_name";
    $results = $wpdb->get_results( $sql );

    return $results;
}