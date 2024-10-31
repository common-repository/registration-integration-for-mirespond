<?php
/*
Plugin Name: Registration Integration for miRespond
Plugin URI: http://www.miware.co.za/mirespond
Description: Integrates the miRespond contact registration script into your WordPress registration process. Users are seamlessly added to your miRespond account during registration on your site, either by request or silently. If you do not yet have a free miRespond account, you will need to <a href='http://www.miware.co.za/mirespond'>go here</a> and sign up for one.
Version: 1.2.0
Author: miWare
Author URI: http://www.miware.co.za/

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !function_exists( 'add_action' )) {
	echo 'Hi there!  I am just a plugin, not much I can do when called directly.';
	exit;
}

/************************************/
/* Plugin Activation                */
/************************************/

function arespond_registration_activate() {
  global $wpdb, $installed;
  $result = $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}arespond_registrations(`email` VARCHAR(255) NOT NULL, PRIMARY KEY (`email`))");
  if(!$result === false) {
    // Schema Created
	add_option("arp_installed", "true", "", true);
  } else {
	// Schema Failed
	add_option("arp_installed", "false", "", true);
  }
}
register_activation_hook(__FILE__, 'arespond_registration_activate');

/************************************/
/* Plugin Deactivation              */
/************************************/

function arespond_registration_deactivate() {
  global $wpdb;
  $result = $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}arespond_registrations");
  if($result) {
    // Schema Deleted
	delete_option("arp_installed");
	delete_option("arp_form_id");
	delete_option("arp_campaign_hash");
	delete_option("arp_list_name");
	delete_option("arp_disabled");
	delete_option("arp_opt_in");
  }
}
register_deactivation_hook(__FILE__, 'arespond_registration_deactivate');

/************************************/
/* Plugin Init Load                 */
/************************************/

function arespond_registration_load() {
  global $optin;
  // Confirm Installation Before Load
  if(get_arp_option("arp_installed") !== false && get_arp_option("arp_installed") != "error" && get_arp_option("arp_installed") != "false") {
    // Install Options if not present
    if(get_arp_option("arp_form_id") === false) {
	  add_option("arp_form_id", "", "", true);
	}
	if(get_arp_option("arp_list_name") === false) {
	  add_option("arp_list_name", "", "", true);
	}
	if(get_arp_option("arp_campaign_hash") === false) {
	  add_option("arp_campaign_hash", "", "", true);
	}	
	if(get_arp_option("arp_disabled") === false) {
	  add_option("arp_disabled", "false", "", true);
	}
	if(get_arp_option("arp_opt_in") === false) {
	  add_option("arp_opt_in", "true", "", true);
	}
  }
  // Define Optin
  $optin = false;
}
add_action("admin_init", "arespond_registration_load");

/************************************/
/* Administration Menu              */
/************************************/

function arespond_registration_admin_init() {
  add_submenu_page("options-general.php", "miRespond Registration Integration", "miRespond Integration", 8, __FILE__, "arespond_registration_admin_page");
}
add_action("admin_menu", "arespond_registration_admin_init");

function arespond_registration_admin_page() {
  global $wpdb;
  $message ='';
	if ( !current_user_can( 'administrator' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
  // Credentials Form Submit
  if(isset($_POST["ARespond_Credentials"])) {
	check_admin_referer( 'mirespond_registration');
    // Update Settings
	//first run checks
	if(isset($_POST["arp_form_id"]) && $_POST["arp_form_id"] != "") {
	  $arp_form_id = (int)$_POST["arp_form_id"];
	  if($arp_form_id===0)
	  {
		  $message .= "The Campaign id must be a number greater than 0!<br>";
	  }
	}
	else
	{
		$message .= "The Campaign ID is required!<br>";
	}
	
	if(isset($_POST["arp_campaign_hash"]) && $_POST["arp_campaign_hash"] != "") {
	  $arp_campaign_hash = sanitize_text_field($_POST["arp_campaign_hash"]);
	  if(strpos($arp_campaign_hash," ")!==false) //There should be no spaces here (sanitize_text_field removed extra white space so trim() not necessary, now just checking for any spaces within the text itself)
	  {
		  $message .= "The Campaign hash is not valid!<br>";
	  }
	}
	else
	{
		$message .= "The Campaign hash is required!<br>";
	}

	if(isset($_POST["arp_list_name"]) && $_POST["arp_list_name"] != "") {
	  //list should be comma separated values of integers
	  $arp_list_name = sanitize_text_field($_POST["arp_list_name"]);
	  $arp_list_name_array = explode(",",$arp_list_name);
	  //check each value is an integer and trim spaces
	  foreach($arp_list_name_array as $key => $campid)
	  {
		  $campid = (int)trim($campid);
		  if($campid>0)
		  {
			$arp_list_name_array[$key] = trim($campid);
		  }
		  else
		  {
			  $message .= "The Extra Campaigns must be a list of comma seperated numbers greater than 0!";
			  break;
		  }
	  }  
	}
	if($message==='')//there were no errors, we can process!
	{
		update_option("arp_form_id", $arp_form_id);
		update_option("arp_campaign_hash", $arp_campaign_hash); 		  
		update_option("arp_list_name", implode(",",$arp_list_name_array));
		$message = "Your information has been saved!";
	}//If there were errors, do not update any details and display message(s).
  }

  // Settings Form Submit
  if(isset($_POST["ARespond_Settings"])) {
    // Update Settings
	check_admin_referer( 'mirespond_settings','mirespond_nonce_settings');
	if(isset($_POST["arp_disabled"]) && $_POST["arp_disabled"] != "") {
      update_option("arp_disabled", "true");
	} else {
	  update_option("arp_disabled", "false");
	}

	if(isset($_POST["arp_opt_in"]) && $_POST["arp_opt_in"] != "") {
      update_option("arp_opt_in", "true");
	} else {
	  update_option("arp_opt_in", "false");
	}

	$message = "Your settings have been saved!";
  }
  ?>

  <?php if ($message) : ?>
    <div id='message' class='updated fade'><p><?php echo $message; ?></p></div>
  <?php endif; ?>

  <div id="dropmessage" class="updated" style="display:none;"></div>
  <div class="wrap">
    <h2><?php _e("miRespond Registration Integration", "arespond_registration"); ?></h2>
    <form method="post" name="mirespond_registration_integration" target="_self">
	<?php echo wp_nonce_field( 'mirespond_registration' ); ?>
      <p>In order to integrate the <a href="https://www.miware.co.za/mirespond">miRespond</a> registration form with the WordPress registration process, you need to enter in the information below. &nbsp;This information can be found in your <a href="https://www.miware.co.za/mirespond">miRespond</a> controlpanel. &nbsp;After logging into your account, select Tools, then Form from the submenu.  &nbsp;Scroll down below the big block of code towards the bottom and retrieve the required values shown alongside 'For Integration purposes:', only copy the bold characters between the '', ie '20,250' insert 20,250 into the corresponding fields.</p>
      <table class="form-table" style="width: 400px; margin-left: 25px;">
        <tr>
          <td style="width: 100px;" valign="top"><?php _e("Campaign ID:", "arespond_registration"); ?></td>
          <td style="width: 300px;"><input type="text" name="arp_form_id" style="width: 250px;" value="<?php if (get_arp_option("arp_form_id")) echo get_arp_option("arp_form_id"); ?>" /><br /></td>
        </tr>
        <tr>
          <td valign="top"><?php _e("Campaign Hash:", "arespond_registration"); ?></td>
          <td><input type="text" name="arp_campaign_hash" style="width: 250px;" value="<?php if (get_arp_option("arp_campaign_hash")) echo get_arp_option("arp_campaign_hash"); ?>" /><br /></td></tr><td COLSPAN="2"><i>(The Hash code can be found in your miRespond control panel, if left blank, the integration will not work).</i></td>
        </tr>				
        <tr>
          <td valign="top"><?php _e("Extra Campaigns:", "arespond_registration"); ?></td>
          <td><input type="text" name="arp_list_name" style="width: 250px;" value="<?php if (get_arp_option("arp_list_name")) echo get_arp_option("arp_list_name"); ?>" /><br /></td></tr><td COLSPAN="2"><i>(Only fill in the Extra Campaigns if you want to post the users to multiple campaigns - if so, select the extra campaigns you would like to allocate to from the lists provided by the form generator in <a href="https://www.miware.co.za/mirespond">miRespond</a> and then click 'Generate Form').  Once done, the Extra Campaigns value will be shown below the form code block alongside 'For Integration purposes:', only copy the bold characters between the '', ie '20,250' insert 20,250 into the corresponding fields.</i></td>
        </tr>
        <tr>
          <td colspan="2"><input type="submit" name="ARespond_Credentials" value="<?php _e("Save Information", "arespond_registration")?> &raquo;" /></td>
        </tr>
      </table>
    </form>
    <h2><?php _e("Settings", "arespond_registration"); ?></h2>
    <form method="post" name="arespond_registration_settings" target="_self">
      <p>Use the settings below to control additional aspects of how the <a href="https://www.miware.co.za/mirespond">miRespond</a> registration process is integrated with your WordPress installation.</p>
	 <?php echo wp_nonce_field( 'mirespond_settings', 'mirespond_nonce_settings' ); ?>
      <table class="form-table" style="width: 600px; margin-left: 25px;">
        <tr>
          <td style="width: 150px;" valign="top"><?php _e("Disable Integration?", "arespond_registration"); ?></td>
          <td style="width: 25px;" valign="top"><input type="checkbox" name="arp_disabled" <?php if (get_arp_option("arp_disabled") == "true") echo "checked=\"checked\" "; ?>/></td>
          <td style="width: 425px;"><i>Removes the integration from the WordPress registration process without having to disable the plugin. &nbsp;Useful in the event of cross-plugin complications.</i></td>
        </tr>
        <tr>
          <td valign="top"><?php _e("Display Opt-In?", "arespond_registration"); ?></td>
          <td valign="top"><input type="checkbox" name="arp_opt_in" <?php if (get_arp_option("arp_opt_in") == "true") echo "checked=\"checked\" "; ?>/></td>
          <td><i>Places a checkbox on the registration form allowing the user to decide whether or not to sign up.</i></td>
        </tr>
        <tr>
          <td colspan="2"><input type="submit" name="ARespond_Settings" value="<?php _e("Save Settings", "arespond_registration")?> &raquo;" /></td>
        </tr>
      </table>
    </form>
    <h2><?php _e("Registered Users", "arespond_registration"); ?></h2>
    <p>This is a list of the registered users on your site who also requested to be signed up with miRespond. &nbsp;Please keep in mind that the list here may not reflect the list in your miRespond control panel, since users must also confirm their intent to register in a separate email sent by miRespond.</p>
    <table class="widefat fixed" cellspacing="0">
      <thead>
        <tr class="thead">
	      <th scope="col" id="username" class="manage-column column-username" style="">Username</th>
	      <th scope="col" id="name" class="manage-column column-name" style="">Display Name</th>
	      <th scope="col" id="email" class="manage-column column-email" style="">E-mail</th>
          <th scope="col" id="registered" class="manage-column column-name" style="">Registered On</th>
        </tr>
      </thead>
      <tbody id="users" class="list:user user-list">
        <?php
		  $users = $wpdb->get_results("SELECT $wpdb->users.user_login, $wpdb->users.display_name, $wpdb->users.user_email, $wpdb->users.user_registered FROM $wpdb->users, {$wpdb->prefix}arespond_registrations WHERE $wpdb->users.user_email = {$wpdb->prefix}arespond_registrations.email");
		  if($users) :
		    foreach($users as $user) { ?>
			  <tr>
                <td class="username column-username"><strong><?php echo $user->user_login; ?></strong></td>
                <td class="name column-name"><?php echo $user->display_name; ?></td>
                <td class="email column-email"><a href='mailto:<?php echo $user->user_email; ?>' title='e-mail: <?php echo $user->user_email; ?>'><?php echo $user->user_email; ?></a></td>
                <td class="name column-name"><?php echo date("F jS, Y", strtotime($user->user_registered)); ?></td>
              </tr>
			<?php } ?>
		  <?php else: ?>
		    <tr class="alternate">
              <td class="username column-username" colspan="4"><i>There are currently no registered users.</i></td>
            </tr>
		  <?php endif; ?>
      </tbody>
      <tfoot>
        <tr class="thead">
	      <th scope="col" id="username" class="manage-column column-username" style="">Username</th>
	      <th scope="col" id="name" class="manage-column column-name" style="">Display Name</th>
	      <th scope="col" id="email" class="manage-column column-email" style="">E-mail</th>
          <th scope="col" id="registered" class="manage-column column-name" style="">Registered On</th>
        </tr>
      </tfoot>
    </table>
  </div>

<?php }

/************************************/
/* Safely Retrieve Options          */
/************************************/

function get_arp_option($option = "") {
  if($option) return str_replace("\"", "'", stripcslashes(get_option($option)));
}

/************************************/
/* Check Email Existence            */
/************************************/

function arp_email_exists($email = "") {
  if($email) {
    global $wpdb;
	$arp_email = $wpdb->escape(strtolower($email));
	$exist = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}arespond_registrations WHERE LOWER(email)='$arp_email'");
	if($exist) {
	  return true;
	} else {
	  return false;
	}
  }
}

/************************************/
/* Registration Process Hooks       */
/************************************/

function arp_register($id) {
  global $wpdb, $optin;

  // Exit if ARespond is Disabled
  if(get_arp_option("arp_disabled") == "true") return;

  // Exit if ARespond Info is Absent
  if(get_arp_option("arp_form_id") == "" || get_arp_option("arp_form_id") === false) return;
  //if(get_arp_option("arp_list_name") == "" || get_arp_option("arp_list_name") === false) return;

  // Check Opt-In
  if(get_arp_option("arp_opt_in") == "true" && $optin == true) {
    // Opt-In Accepted - No Action Needed
  } elseif(get_arp_option("arp_opt_in") == "true" && $optin == false) {
    // Opt-In Declined
    return;
  }

  // Begin ARespond Registration
  $id = intval($id);
  $user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID=$id");
  if(!arp_email_exists($user->user_email)) {
  	  $arpusername = $user->user_nicename;
  	  $user_id = $id;
	  $key = 'first_name';
	  $single = true;
  	  $user_first = get_user_meta( $user_id, $key, $single );
  	  if(strlen($user_first)>1)
  	  {
			$arpusername = $user_first;
	  }
	$params = array(
	  "user_id" => get_arp_option("arp_form_id"),
	  "extra_id" => get_arp_option("arp_list_name"),
	  "user_hash" => get_arp_option("arp_campaign_hash"),
  	  "name" => $arpusername,
	  "email" => $user->user_email,
	  "submit" => "Submit"
	);
	$r = mirespond_getUrl('https://www.miware.co.za/mirespond/subscribe.php', $params);
	$result = $wpdb->query("INSERT INTO {$wpdb->prefix}arespond_registrations(email) VALUES('".$user->user_email."')");
  }
}

add_action('user_register', 'arp_register');

function arp_opt_in() {
  if(get_arp_option("arp_opt_in") == "true" && (get_arp_option("arp_disabled") == "" || get_arp_option("arp_disabled") == "false")) { ?>
    <p><input type="checkbox" name="arp_opt_in" id="arp_opt_in" class="input" <?php if(isset($_POST["arp_opt_in"]) && $_POST["arp_opt_in"] != "") { echo("checked=\"checked\""); } ?> tabindex="99"/>
	&nbsp;&nbsp;Sign me up to receive the newsletter!</p><br class="clear" />
	<?php
  }
}

add_action('register_form', 'arp_opt_in', 10, 0);

function arp_check_opt_in($login, $email, $errors) {
  global $optin;
  if(get_arp_option("arp_opt_in") == "true") {
    if(isset($_POST["arp_opt_in"]) && $_POST["arp_opt_in"] != "") {
      // Opted In
	  $optin = true;
	} else {
	  // Opted Out
	  $optin = false;
	}
  }
}

add_action('register_post', 'arp_check_opt_in', 10, 3);

/************************************/
/* Registration Process Hooks       */
/************************************/
	function mirespond_getUrl($url, $opts=null) 
	{
		if(empty($opts))
		{
			$content = file_get_contents($url);
		}
		else
		{
			$data = http_build_query($opts);
			$context_options = array (
        'http' => array (
            'method' => 'POST',
            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
            )
        );

			$context  = stream_context_create($context_options);
			$content = file_get_contents($url, true, $context);
		}
		// you can add some code to extract/parse response number from first header. 
		// For example from "HTTP/1.1 200 OK" string.
		return array(
				'headers' => $http_response_header,
				'content' => $content
		);
	}
?>