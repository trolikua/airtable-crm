<?php

/*
Plugin Name: Airtable CRM
Description: Simple CRM uses Airtable
Version: 1.0
*/

// include scripts and styles
add_action( 'wp_enqueue_scripts', 'crm_scripts' );
function crm_scripts() {
    wp_register_style( 'custom-style', plugins_url( 'assets/style.css', __FILE__ ), array(), 'all' );
    wp_enqueue_style( 'custom-style' );
}

// include templates
function crm_add_page_template ($templates) {
    $templates['upload-form.php'] = 'Upload form';
    $templates['upload.php'] = 'Upload';
    $templates['process.php'] = 'Process';
    return $templates;
}
add_filter ('theme_page_templates', 'crm_add_page_template');

// if files doesn't exists in theme folder, search it into plugin directory
function crm_redirect_page_template ($template) {
    $post = get_post();
    $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
    if ('upload-form.php' == basename ($page_template )) {
        $template = WP_PLUGIN_DIR . '/airtable-crm/upload-form.php';
    }
    elseif ('upload.php' == basename ($page_template )) {
        $template = WP_PLUGIN_DIR . '/airtable-crm/upload.php';
    }
    elseif ('process.php' == basename ($page_template )) {
        $template = WP_PLUGIN_DIR . '/airtable-crm/process.php';
    }
    return $template;
}
add_filter ('page_template', 'crm_redirect_page_template');