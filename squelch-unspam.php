<?php
/*
Plugin Name: Squelch WordPress Unspam
Plugin URI: http://squelchdesign.com/wordpress-plugin-squelch-unspam/
Description: Stops spam at the root by renaming the fields on the comment forms
Version: 1.1
Author: Matt Lowe
Author URI: http://squelchdesign.com/matt-lowe
License: GPL2
*/

/*  Copyright 2013  Matt Lowe / Squelch Design  (http://squelch.it/  ... email: hi@squelchdesign.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/



/* =Activation
---------------------------------------------------------------------------- */

/**
 * On activation of the plugin generate random names for the extra fields.
 */
function lstunspam_activate() {
    // Ensure that when the next comment is submitted, the field names will be changed
    update_option( 'lstunspam_last_change', lstunspam_get_day() - 1 );

    // Show welcme message
    update_option( 'lstunspam_showmessage', 2 );
}
register_activation_hook( __FILE__, 'lstunspam_activate' );


/**
 * Displays the welcome message.
 */
function lstunspam_welcome_message() {
    // Message hiding/showing etc
    $rmvmsg = $_GET['unspam-rmvmsg'];
    if (!empty($rmvmsg)) {
        if ($rmvmsg == 'ignorewoocommerce')         update_option('lstunspam_ignorewoocommerce', 1);
        if ($rmvmsg == 'showfieldupdatemessage')    update_option('lstunspam_showfieldupdatemessage', 1);
        if ($rmvmsg == 'hidefieldupdatemessage')    update_option('lstunspam_showfieldupdatemessage', 0);
    }

    if ( get_option( 'lstunspam_showmessage' ) > 0 ) {
        update_option( 'lstunspam_showmessage', get_option( 'lstunspam_showmessage' ) - 1 );

        // Output a simple message on install explaining that there's nothing to configure
        $msg = '<div class="updated">'
            .'<p style="font-size: 1.3em;">'
            ."Thank you for installing "
            .'<strong style="color: #2279a0;">'
            ."Squelch WordPress Unspam</strong>. "
            ."There's nothing to configure, the plugin just does its thing in the "
            ."background, so you can get on with running your website. Enjoy!"
            .'</p><p>This message will disappear automatically.</p></div>';
        echo $msg;
    }

    if (get_option('lstunspam_showfieldupdatemessage') > 0 && lstunspam_field_names_update_needed()) {
        $msg = '<div class="updated">'
            .'<p><strong>Squelch WordPress Unspam:</strong> Field names will '
            .'automatically update next time a post/page with comments enabled '
            .'is viewed. '
            .'<a href="'.lstunspam_get_dashboard_url().'?unspam-rmvmsg=hidefieldupdatemessage">Remove this message.</a></p></div>';
        echo $msg;
    }

    if ( class_exists( 'Woocommerce' ) && !(get_option('lstunspam_ignorewoocommerce') > 0)) {
        $msg = '<div class="error">'
            .'<p><strong>Squelch WordPress Unspam:</strong> Unspam has detected the '
            .'presence of WooCommerce on your WordPress installation. Unspam is not currently '
            ."compatible with WooCommerce's reviews mechanism, if you have reviews enabled on your "
            .'products then please do NOT use Unspam. '
            .'<a href="'.lstunspam_get_dashboard_url().'?unspam-rmvmsg=ignorewoocommerce">Remove this message</a>'
            .'</p></div>';
        echo $msg;
    }
}
add_action( 'admin_notices', 'lstunspam_welcome_message' );



/* =On Submit
---------------------------------------------------------------------------- */

// Deal with renamed fields as early as possible
$fieldsMap = get_option( 'lstunspam_fieldsMap', array() );

if (isset( $fieldsMap )) {
    foreach (array_keys( $fieldsMap ) as $field) {
        // Look for data being submitted blindly to the original field names, which
        // would suggest an automated spambot assuming that all WordPress blogs
        // have identical field names
        if ( isset( $_POST[$field] ) ) lstunspam_spam();

        // If form has been submitted using the new name then move the value over
        // to the correct field name for further processing by WP
        if ( isset( $_POST[$fieldsMap[$field]] ) ) {
            $_POST[$field] = $_POST[$fieldsMap[$field]];
        }
    }
}


/**
 * On initialization check if a comment has been posted. We do it in init so
 * that we can die (thus preventing a message from being saved) if it turns out
 * to be spam.
 */
function lstunspam_init() {
    // We use random field names to evade detection and so that no two websites
    // can be submitted to in the same way, making life harder for spammers
    $field1name = get_option( 'lstunspam_field1name' );
    $field2name = get_option( 'lstunspam_field2name' );

    // Field 1 must be left empty
    if ($_POST[$field1name] != "") lstunspam_spam();
    // Field 2 must have the URL of the website in it
    if (!empty($_POST[$field2name])) {
        if ($_POST[$field2name] != get_bloginfo("wpurl")) lstunspam_spam();
    }

}
add_action( 'init', 'lstunspam_init' );



/* =Modify comment forms
---------------------------------------------------------------------------- */

/**
 * Returns true if an update is needed.
 */
function lstunspam_field_names_update_needed() {
    $last_submit = get_option( 'lstunspam_last_change' );
    $day         = lstunspam_get_day();
    return ($last_submit != $day);
}


/**
 * Detects whether it is time to create new field names.
 */
function lstunspam_update_field_names_if_needed() {
    if ( lstunspam_field_names_update_needed() ) lstunspam_update_field_names();
}


/**
 * Update the randomized field names.
 */
function lstunspam_update_field_names() {
    // Create the randomized names of the two hidden fields
    $field1name = lstunspam_randomString( 5 );
    $field2name = lstunspam_randomString( 5 );

    update_option( 'lstunspam_field1name', $field1name );
    update_option( 'lstunspam_field2name', $field2name );

    // For all the fields collected from the form, create new names for them
    $fieldsMap = array();

    $fields = get_option( 'lstunspam_default_fields' );
    foreach ( $fields as $field ) {
        $newField = lstunspam_randomString( 5 );
        $fieldsMap[$field] = $newField;
    }

    // Store the new names
    update_option( 'lstunspam_fieldsMap', $fieldsMap );

    // Change the field names nightly (ie When "day" changes)
    update_option( 'lstunspam_last_change', lstunspam_get_day() );
}


/**
 * Generates a list of all field names currently in use. This will typically be
 * author, email, url, comment, comment_post_ID and comment_parent. These
 * are stored internally and posts that refer to these directly will be treated
 * as spam. New names will be generated for these fields and used in their
 * place to allow legitimate commenters to submit comments.
 */
function lstunspam_get_field_names( $fields ) {
    $found = array_keys( $fields );
    array_push( $found, "comment" );
    update_option( 'lstunspam_default_fields', $found );

    return $fields;
}
add_filter( 'comment_form_default_fields', 'lstunspam_get_field_names', 1, 11);


/**
 * Add to new fields to the comment form. One that must remain empty, one that
 * must contain the address of the website. These two extra fields will be
 * submitted along with the comment and tested. Spam bots will not know whether
 * to fill out the 
 */
function lstunspam_add_hidden_fields( $fields ) {
    lstunspam_update_field_names_if_needed();

    // We use random field names to evade detection and so that no two websites
    // can be submitted to in the same way, making life harder for spammers
    $field1name = get_option( 'lstunspam_field1name' );
    $field2name = get_option( 'lstunspam_field2name' );

    $pBefore = '<p style="visibility: hidden;height:0;line-height:0;margin:0;padding:0;">';
    $pAfter  = '</p>';
    $url     = get_bloginfo('wpurl');
    $field1  = '<input id="'.$field1name.'" name="'.$field1name.'" type="text" value="" size="30" />';
    $field2  = '<input id="'.$field2name.'" name="'.$field2name.'" type="text" value="'.$url.'" size="30" />';
    $fields['lstunspam_1'] = $pBefore.$field1.$pAfter;
    $fields['lstunspam_2'] = $pBefore.$field2.$pAfter;

    return $fields;
}
add_filter('comment_form_default_fields', 'lstunspam_add_hidden_fields', 1, 12);


/**
 * Rename all fields in the generated form to use the newly chosen values.
 */
function lstunspam_rename_fields( $fields ) {
    $fieldsMap = get_option( 'lstunspam_fieldsMap' );

    foreach ( array_keys($fields) as $field ) {
        $val = $fields[$field];

        if ( is_string( $val ) ) {
            $val = preg_replace( '/id="'  .$field.'"/', 'id="'  .$fieldsMap[$field].'"', $val );
            $val = preg_replace( '/name="'.$field.'"/', 'name="'.$fieldsMap[$field].'"', $val );
            $val = preg_replace( '/for="' .$field.'"/', 'for="' .$fieldsMap[$field].'"', $val );

            $fields[$field] = $val;
        }
    }

    return $fields;
}
add_filter( 'comment_form_default_fields', 'lstunspam_rename_fields', 1, 13 );


/**
 * Renames "comment" field.
 */
function lstunspam_rename_comment_field( $fields ) {
    $fieldsMap = get_option( 'lstunspam_fieldsMap' );
    $val = $fields['comment_field'];

    $val = preg_replace( '/id="'  .'comment'.'"/', 'id="'  .$fieldsMap['comment'].'"', $val );
    $val = preg_replace( '/name="'.'comment'.'"/', 'name="'.$fieldsMap['comment'].'"', $val );
    $val = preg_replace( '/for="' .'comment'.'"/', 'for="' .$fieldsMap['comment'].'"', $val );

    $fields['comment_field'] = $val;

    return $fields;
}
add_filter( 'comment_form_defaults', 'lstunspam_rename_comment_field', 1, 10 );



/* =Statistics
---------------------------------------------------------------------------- */

/**
 * Dies and logs the spammer's IP address.
 */
function lstunspam_spam() {
    wp_die( 'Sorry, your comment was not accepted.' );

    // Gather IP address, add to blacklist/counter
    // TODO
}



/* =Helper Functions
---------------------------------------------------------------------------- */

/**
 * Helper function, returns a random string of letters and numbers.
 *
 * @param $len (Optional) Number of random characters to generate
 */
function lstunspam_randomString( $len=5 ) {
    $rv = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $len);

    return $rv;
}


/**
 * Returns the current day as a number. Does the same thing as date("d"), but
 * by moving the logic into a helper function we can easily test the plugin's
 * functionality.
 */
function lstunspam_get_day() {
    //return 22; //for testing
    return date( "d" );
}


/**
 * Returns the URL of the dashboard, for creating links in messages.
 */
function lstunspam_get_dashboard_url() {
    $scheme = $atts['scheme'];
    return get_site_url( null, '', $scheme ).'/wp-admin/index.php';
}



/* =Deactivation
---------------------------------------------------------------------------- */

/**
 * On deactivation of the plugin, delete all options.
 */
function lstunspam_deactivate() {
    // On deactivation of the plugin we clean up our stored options.
    delete_option( 'lstunspam_field1name'           );
    delete_option( 'lstunspam_field2name'           );
    delete_option( 'lstunspam_last_change'          );
    delete_option( 'lstunspam_showmessage'          );
    delete_option( 'lstunspam_fieldsMap'            );
    delete_option( 'lstunspam_default_fields'       );
    delete_option( 'lstunspam_ignorewoocommerce'    );
}
register_deactivation_hook( __FILE__, 'lstunspam_deactivate' );

