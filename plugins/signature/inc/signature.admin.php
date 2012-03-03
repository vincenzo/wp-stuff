<?php
/**
 * @file Auxiliary file where all admin-related code is confined.
 */

/**
 * Content for the meta box added to the post's edit page. 
 */
function sig_options_post_box_content() {
  global $post, $postmeta_signature_option;

  // Start building the combobox for setting the signature option
  // combo values/labels
  $sig_options_values = array(
    "off" => __("Signature disabled", 'signature'),
    "on" => __("Signature enabled", 'signature')
  );

  $sig_ovveride = get_post_meta($post->ID, $postmeta_signature_option, TRUE);
  if (!$sig_ovveride) $sig_ovveride = 'on';

  $sig_override_options = "<select name='signature-options'>";
  foreach ($sig_options_values as $value => $label) {
    $selected = ($value == $sig_ovveride) ? 'selected="selected"' : '';
    $sig_override_options .= "<option value='$value' $selected>$label</option>";
  }
  $sig_override_options .= "</select>";

  print $sig_override_options;
}

/**
 * Add a meta box to the post's edit page.
 */
function sig_options_post_box() {
  add_meta_box(
    'sig_options_post_box_content',
    'Signature Options',
    'sig_options_post_box_content',
    'post'
  );
}

/**
 *  Handle the HTTP POST data
 *
 * @param object $userdata
 *  The main data of the current user profile
 */
function sig_handle_http_post($userdata) {
  global $usermeta_signature, $usermeta_signature_type;

  // Something was posted, get the data and insert them
  if (sizeof($_POST) > 0) {
    if (isset($_POST['signature-settings'])) {
      update_user_meta($userdata->ID, $usermeta_signature_type, $_POST['signature-settings']);

      if ($_POST['signature-settings'] == 'open' && isset($_POST['custom-signature'])) {
        update_user_meta($userdata->ID, $usermeta_signature, $_POST['custom-signature']);
      }
    }
  }
  // End HTTP POST handling
}

/**
 *  Create the content of the signature administration page.
 */
function sig_write_signature_page() {
  global $usermeta_signature, $usermeta_signature_type;

  global $userdata;
  get_currentuserinfo();

  // Handle the HTTP POST
  sig_handle_http_post($userdata);

  // Get current settings
  $cur_sig_type = get_user_meta($userdata->ID, $usermeta_signature_type, TRUE);
  $cur_signature = get_user_meta($userdata->ID, $usermeta_signature, TRUE);

  // Start building the combobox for setting the signature option
  // combo values/labels
  $usermeta_signature_type_values = array(
    "off" => __("Signature disabled", 'signature'),
    "first_name_l" => __("First name + Last name's initial", 'signature') . " (" . $userdata->first_name . " " . $userdata->last_name[0]  . ".)",
    "f_last_name" => __("First name's initial + Last name", 'signature') . " (" . $userdata->first_name[0] . ". " . $userdata->last_name  . ")",
    "first_name" => __("First name", 'signature') . " (" . $userdata->first_name . ")",
    "last_name" => __("Last name", 'signature') . " (" . $userdata->last_name . ")",
    "nickname" => __("Nickname", 'signature') . " (" . $userdata->nickname . ")",
    "fullname" => __("First name + Last name", 'signature') . " (" . $userdata->first_name . " " . $userdata->last_name . ")",
    "anonymous" => __("Anonymous", 'signature'),
    "open" => __("Custom signature", 'signature')
  );

  $sig_combo = "<select name='signature-settings'>";

  foreach ($usermeta_signature_type_values as $value => $label) {
    $selected = ($value == $cur_sig_type) ? "selected='$value'" : "";
    $sig_combo .= "<option value='$value' $selected>$label</option>";
  }

  $sig_combo .= "</select>";
  // Stop building the combobox

  // Start building the form
  $panel = array();

  $panel['wrap_open'] = "<div class='wrap' id='signature-page'>";
  $panel['title'] = "<h2>" . __('Your Signature', 'signature') . "</h2>";
  $panel['form_open'] = "<form name='signature' id='signature' method='post' action=''>";
  $panel['form_table_open'] = "<table class='form-table'><tbody>";
  $panel['choose_sig_row'] = "<tr><th scope='row'><label for='signature-settings'>" . __('Signature setting', 'signature') . "</label></th><td>$sig_combo</td></tr>";

  global $xhtml_tags_allowed;
  $special_code = "<ul>";
  $special_code .= "<li><strong>[first]</strong> " . __('is a placeholder for your firstname', 'signature') . " (" . $userdata->first_name . ")";
  $special_code .= "<li><strong>[last]</strong> " . __('is a placeholder for your lastname', 'signature') . " (" . $userdata->last_name . ")";
  $special_code .= "<li><strong>[nick]</strong> " . __('is a placeholder for your nickname', 'signature') . " (" . $userdata->nickname . ")";
  $special_code .= "<li><strong>[email]</strong> " . __('is a placeholder for your e-mail address', 'signature') . " (" . $userdata->user_email . ")";
  $special_code .= "<li><strong>[www]</strong> " . __('is a placeholder for your website', 'signature') . " (" . $userdata->user_url . ")";
  $special_code .= "</ul>";

  $panel['custom_sig_row'] = "<tr><th scope='row'><label for='custom-signature'>" . __('Custom signature', 'signature') . "</label></th><td>" . __('IMPORTANT: to use this field as signature you have to choose "Custom signature" from the setting above.', 'signature') . "<br /><textarea name='custom-signature' id='custom-signature' rows='10' cols='100'>$cur_signature</textarea><br /><strong>" . __('XHTML tags allowed', 'signature') . "</strong>: " . htmlspecialchars($xhtml_tags_allowed) . ".<br /><strong>" . __('Special code allowed', 'signature') . "</strong>: $special_code </td></tr>";
  $panel['form_table_close'] = "</tbody></table>";
  $panel['update_button'] = '<p class="submit"><input type="submit" value="' . __('Update', 'signature') . '" name="submit" /></p>';
  $panel['form_close'] = "</form>";
  $panel['wrap_close'] = "</div>";
  // Stop building the form

  // Print the form
  foreach ($panel as $element) {
    echo $element;
  }
}

/**
 *  Attach the signature settings page under the Users administration menu.
 */
function sig_attach_admin_menus() {
  add_submenu_page('users.php', __('Choose Your Signature', 'signature'), __('Your Signature', 'signature'), 'read', __FILE__, 'sig_write_signature_page');
}
