<?php
/*
Plugin Name: Squelch WordPress Unspam
Plugin URI: http://squelchdesign.com/wordpress-plugin-squelch-unspam/
Description: Stops spam at the root by renaming the fields on the comment forms
Version: 1.3
Author: Matt Lowe
Author URI: http://squelchdesign.com/matt-lowe
License: GPL2
*/

/*  Copyright 2013-2014  Matt Lowe / Squelch Design  (http://squelch.it/)

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


// TODO : Add a nonce to the comments form
// TODO : Add a PHP script that generates an image and sets a cookie
// TODO : Add a JS that causes the person submitting the spam to have to do some work
// TODO : Add automatic IP address blocking
// TODO : Marking a comment as spam automatically triggers IP address blocking
// TODO : Build a known-spammers IP database into the distributed plugin



/* =Activation
---------------------------------------------------------------------------- */

/**
 * On activation of the plugin generate random names for the extra fields.
 */
function lstunspam_activate() {
    // Ensure that when the next comment is submitted, the field names will be changed
    update_option( 'lstunspam_last_change', lstunspam_get_day() - 1 );

    // Show welcome message
    update_option( 'lstunspam_showmessage', 2 );
}
register_activation_hook( __FILE__, 'lstunspam_activate' );



/**
 * Called when the plugin is updated.
 */
function lstunspam_updated() {
    lstunspam_delete_options();

    // Show welcome message
    update_option( 'lstunspam_showmessage', 2 );
}

function lstunspam_update_checker() {
    $reqd_version = '1.3';
    $last_version = get_option( 'lstunspam_version' );

    if ($reqd_version != $last_version) {
        lstunspam_updated();
        update_option( 'lstunspam_version', $reqd_version );
    }
}
add_action('init', 'lstunspam_update_checker' );



/**
 * Displays the welcome message.
 */
function lstunspam_welcome_message() {
    // Message hiding/showing etc
    $rmvmsg = '';
    if (!empty($_GET['unspam-rmvmsg'])) $rmvmsg = $_GET['unspam-rmvmsg'];

    if (!empty($rmvmsg)) {
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
}
add_action( 'admin_notices', 'lstunspam_welcome_message' );





/* =On Submit
---------------------------------------------------------------------------- */

// Deal with renamed fields as early as possible
$fieldsMap = get_option( 'lstunspam_fieldsMap', array() );

if (!is_admin() && isset( $fieldsMap )) {
    // Only prevent access to original fields if an attempt has been made to post a comment
    // (ie do not interfere with other forms that have the same field names)
    if (isset($_POST['comment_post_ID']) && !empty($_POST['comment_post_ID'])) {

        foreach (array_keys( $fieldsMap ) as $field) {
            // Look for data being submitted blindly to the original field names, which
            // would suggest an automated spambot assuming that all WordPress blogs
            // have identical field names
            if ( isset( $_POST[$field] ) ) lstunspam_spam('You attempted to submit to the field '.$field.' which has been renamed to '.$fieldsMap[$field] );

            // If form has been submitted using the new name then move the value over
            // to the correct field name for further processing by WP
            if ( isset( $_POST[$fieldsMap[$field]] ) ) {
                $_POST[$field] = $_POST[$fieldsMap[$field]];
            }
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
    $field3name = get_option( 'lstunspam_field3name' );
    $field4name = get_option( 'lstunspam_field4name' );
    $field4val  = get_option( 'lstunspam_field4val'  );

    if (!is_admin() && !empty($_POST['comment_post_ID'])) {
        // Logged in users aren't treated with the same degree of suspicion
        if (!is_user_logged_in()) {
            if (!isset($_POST[$field1name])) lstunspam_spam( 'You did not send field 1: '.$field1name );
            if (!isset($_POST[$field2name])) lstunspam_spam( 'You did not send field 2: '.$field2name );
            if (!isset($_POST[$field3name])) lstunspam_spam( 'You did not send field 3: '.$field3name );
            if (!isset($_POST[$field4name])) lstunspam_spam( 'You did not send field 4: '.$field4name );
        }

        // Field 1 must be left empty
        if (!empty($_POST[$field1name])) lstunspam_spam( 'Field 1 ('.$fieldnam1.') must be left empty' );

        // Field 2 must have the URL of the website in it
        if (!empty($_POST[$field2name])) {
            if ($_POST[$field2name] != get_bloginfo("wpurl")) lstunspam_spam( 'Field 2 ('.$field2name.') must contain the site URL' );
        }

        // Field 3 must be left empty
        if (!empty($_POST[$field3name])) lstunspam_spam( 'Field 3 ('.$field3name.') must be left empty' );

        // Field 4 must contain the value placed into it by the JS side of the script
        if ($_POST[$field4name] != $field4val) lstunspam_spam( 'Field 4 ('.$field4name.') must contain the value added by the javascript' );
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
    if ( lstunspam_field_names_update_needed() ) {
        lstunspam_update_field_names();
        return true;
    }
    return false;
}


/**
 * Update the randomized field names.
 */
function lstunspam_update_field_names() {

    $fields = get_option( 'lstunspam_default_fields' );

    if (!$fields) {

        // um...?

    } else {

        lstunspam_update_additional_field_names();
        lstunspam_update_builtin_field_names($fields);

        update_option( 'lstunspam_last_change', lstunspam_get_day() );

    }
}



function lstunspam_update_additional_field_names() {

    // Create the randomized names/values of the four hidden fields
    $field1name = lstunspam_randomString( 5 );
    $field2name = lstunspam_randomString( 5 );
    $field3name = lstunspam_randomString( 5 );
    $field4name = lstunspam_randomString( 5 );
    $field4val  = lstunspam_randomString( 10 );

    update_option( 'lstunspam_field1name', $field1name );
    update_option( 'lstunspam_field2name', $field2name );
    update_option( 'lstunspam_field3name', $field3name );
    update_option( 'lstunspam_field4name', $field4name );
    update_option( 'lstunspam_field4val',  $field4val );

}


function lstunspam_update_builtin_field_names($fields) {

    // For all the fields collected from the form, create new names for them
    $fieldsMap = array();

    foreach ( $fields as $field ) {
        $newField = lstunspam_randomString( 5 );
        $fieldsMap[$field] = $newField;
    }

    // Store the new names
    update_option( 'lstunspam_fieldsMap', $fieldsMap );

    // Change the field names nightly (ie When "day" changes)
    update_option( 'lstunspam_last_change', lstunspam_get_day() );
    update_option( 'lstunspam_last_change_version', '1.3' );

}



/**
 * Generates a list of all field names currently in use. This will typically be
 * author, email, url, comment, comment_post_ID and comment_parent. These
 * are stored internally and posts that refer to these directly will be treated
 * as spam. New names will be generated for these fields and used in their
 * place to allow legitimate commenters to submit comments.
 *
 * As of 1.2 this function also sets up lstunspam_rename_field to rename each
 * field in turn as it is being output to the page. The change is made in this
 * way to allow support for woocommerce.
 */
function lstunspam_get_field_names( $fields ) {
    $found = get_option( 'lstunspam_default_fields', array() );
    $found = array_merge(array_keys( $fields ), $found);
    array_push( $found, "comment" );
    $found = array_unique($found);

    update_option( 'lstunspam_default_fields', $found );

    foreach ( $found as $field ) {
        add_filter( "comment_form_field_{$field}", 'lstunspam_rename_field', 1, 20 );
    }

    return $fields;
}


/**
 * Add two new fields to the comment form. One that must remain empty, one that
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
    $field3name = get_option( 'lstunspam_field3name' );
    $field4name = get_option( 'lstunspam_field4name' );
    $field4val  = get_option( 'lstunspam_field4val'  );

    $url     = get_bloginfo('wpurl');
    $field1  = '<p class="comment-form-url"><input id="'.$field1name.'" name="'.$field1name.'" type="url" value="" size="30" /></p>';
    $field2  = '<p class="comment-form-url"><input id="'.$field2name.'" name="'.$field2name.'" type="url" value="'.$url.'" size="30" aria-required="true" /></p>';
    $field3  = '<p class="comment-form-email"><input id="'.$field3name.'" name="'.$field3name.'" type="email" value="" size="30"  /></p>';
    $field4  = '<p class="comment-form-text"><input id="'.$field4name.'" name="'.$field4name.'" type="text" value="" size="30" aria-required="true" /></p>';

    // Randomize the order of the additional fields when put to the screen to make life harder for bots
    $all_new_fields = array($field1, $field2, $field3, $field4 );
    //shuffle( $all_new_fields );

    $i = 0;
    foreach ($all_new_fields as $field) {
        $fields["lstunspam_$i"] = $field;

        $i++;
    }

    return $fields;
}


/**
 * Rename all fields in the generated form to use the newly chosen values.
 */
function lstunspam_rename_field( $val ) {

    $fieldsMap = get_option( 'lstunspam_fieldsMap' );

    // Name of field we are acting on isn't passed through, so have to calculate it from filter name
    // *sigh*
    $field = current_filter();
    $field = preg_replace( '/^comment_form_field_/', '', $field );

    $newField = $fieldsMap[$field];

    if ( is_string( $val ) ) {
        $val = preg_replace( '/id="'  .$field.'"/', 'id="'  .$newField.'"', $val );
        $val = preg_replace( '/name="'.$field.'"/', 'name="'.$newField.'"', $val );
        $val = preg_replace( '/for="' .$field.'"/', 'for="' .$newField.'"', $val );

        $fields[$field] = $val;
    }

    return $val;
}


///**
// * Rename all fields in the generated form to use the newly chosen values.
// */
//function lstunspam_rename_fields( $fields ) {
//    $fieldsMap = get_option( 'lstunspam_fieldsMap' );
//
//    foreach ( array_keys($fields) as $field ) {
//        $val = $fields[$field];
//
//        if ( is_string( $val ) ) {
//            $val = preg_replace( '/id="'  .$field.'"/', 'id="'  .$fieldsMap[$field].'"', $val );
//            $val = preg_replace( '/name="'.$field.'"/', 'name="'.$fieldsMap[$field].'"', $val );
//            $val = preg_replace( '/for="' .$field.'"/', 'for="' .$fieldsMap[$field].'"', $val );
//
//            $fields[$field] = $val;
//        }
//    }
//
//    return $fields;
//}


/**
 * Renames the fields on the form.
 */
function lstunspam_rename_comment_field( $fields ) {

    $fields['fields'] = lstunspam_get_field_names(   $fields['fields'] );
    $fields['fields'] = lstunspam_add_hidden_fields( $fields['fields'] );
    //$fields['fields'] = lstunspam_rename_fields(     $fields['fields'] );

    $fieldsMap = get_option( 'lstunspam_fieldsMap' );
    $val = $fields['comment_field'];

    return $fields;
}
add_filter( 'comment_form_defaults', 'lstunspam_rename_comment_field', 1, 10 );
add_filter( 'woocommerce_product_review_comment_form_args', 'lstunspam_rename_comment_field', 1, 10 );




/* =JavaScript
---------------------------------------------------------------------------- */

/**
 * Enqueue necessary scripts.
 */
function lstunspam_enqueue_scripts() {
    wp_enqueue_script(
        'unspam',
        plugins_url( 'unspam.js', __FILE__ ),
        array( 'jquery' ),
        '1.3',
        true
    );

    wp_enqueue_style(
        'unspam',
        plugins_url( 'unspam.css', __FILE__ ),
        array(),
        '1.3'
    );
}
add_action( 'wp_enqueue_scripts', 'lstunspam_enqueue_scripts' );



/**
 * Add JS to the top of the page to set the field values as necessary, and also
 * add a script to hide the additional fields.
 */
function lstunspam_head() {

    $field1name = get_option( 'lstunspam_field1name' );
    $field2name = get_option( 'lstunspam_field2name' );
    $field3name = get_option( 'lstunspam_field3name' );
    $field4name = get_option( 'lstunspam_field4name' );
    $field4val  = get_option( 'lstunspam_field4val'  );
    ?>
        <script type="text/javascript">
            var injectable = {
                name: '<?php echo $field4name; ?>',
                val:  '<?php echo $field4val;  ?>'
            };
        </script>

        <style type="text/css">
            body #commentform #<?php echo $field1name; ?>,
            body #commentform #<?php echo $field2name; ?>,
            body #commentform #<?php echo $field3name; ?>,
            body #commentform #<?php echo $field4name; ?> {
                display: none;
            }
            #commentform input,
            #commentform textarea {
                display: none;
            }
        </style>
    <?php
}
add_action( 'wp_footer', 'lstunspam_head' );



/* =Statistics
---------------------------------------------------------------------------- */

/**
 * Dies and logs the spammer's IP address.
 */
function lstunspam_spam( $reason = '' ) {
    if ($reason && WP_DEBUG) {
        wp_die( 'Sorry, your comment was not accepted because: '.$reason );
    }

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
    $chr1 = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 1);
    $str  = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $len);
    $rv   = substr( $chr1.$str, 0, $len );

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
    lstunspam_delete_options();
}
register_deactivation_hook( __FILE__, 'lstunspam_deactivate' );



function lstunspam_delete_options() {
    delete_option( 'lstunspam_field1name'           );
    delete_option( 'lstunspam_field2name'           );
    delete_option( 'lstunspam_field3name'           );
    delete_option( 'lstunspam_field4name'           );
    delete_option( 'lstunspam_field4val'            );
    delete_option( 'lstunspam_last_change'          );
    delete_option( 'lstunspam_showmessage'          );
    delete_option( 'lstunspam_fieldsMap'            );
    delete_option( 'lstunspam_default_fields'       );
    delete_option( 'lstunspam_ignorewoocommerce'    );
    delete_option( 'lstunspam_version'              );
}
