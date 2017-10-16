<?php
/*
** Script Name: Emergency Admin
 * Developer   : Armin Nikdel
 * Website     : http://sanarena/wpemergency
 * Contact     : sanarena@gmail.com
 * Twitter     : http://twitter.com/sanarena
 * Version     : 1.0
 * Date        : Feb 23 2014

 ** How to use:
 * Please copy wp-emergency.php in root of your wordpress installation.
 * Root of your wordpress is same folder where you can find wp-config.php file.
 * Then open yoursite.com/your_wordpress_instalation_path/wp-emergency.php in your browser
 * and enter new info to create new admin for your wordpress.
 

 * This script is created to add new admin when you forgot original admin password. 
 * Or when your original admin is hacked. Or by some resoan is changed. 
 * Using this code, you won't need to get your hands dirty with database stuff.
 * All you need to use this script is to copy it in your wordpress folder via ftp account or main control panel.
 *
	IMPORTANT NOTICE: Delete this file from your server when you have created admin password. otherwise it can be considered as a security weakness.
 *
*/



require('./wp-blog-header.php');

function basestart() {
global $wpdb;

		if (isset($_POST['update']))
		{
			$user_login = ( empty( $_POST['e-name'] ) ? '' : sanitize_user( $_POST['e-name'] ) );
			$user_pass  = ( empty( $_POST[ 'e-pass' ] ) ? '' : $_POST['e-pass'] );
			$answer = ( empty( $user_login ) ? '<div id="message" class="updated fade"><p><strong>The username field is empty.</strong></p></div>' : '' );
			$answer .= ( empty( $user_pass ) ? '<div id="message" class="updated fade"><p><strong>The password field is empty.</strong></p></div>' : '' );
			if ( $user_login != $wpdb->get_var("SELECT user_login FROM $wpdb->users WHERE ID = '1' LIMIT 1") )
			{
				$answer .="<div id='message' class='updated fade'><p><strong>That is not the correct administrator username.</strong></p></div>";
			}
			if( empty( $answer ) )
			{
				$wpdb->query("UPDATE $wpdb->users SET user_pass = MD5('$user_pass'), user_activation_key = '' WHERE user_login = '$user_login'");
				$plaintext_pass = $user_pass;
				$message = __('Someone, hopefully you, has reset the Administrator password for your WordPress blog using emergency script. Details follow:'). "\r\n";
				$message  .= sprintf(__('Username: %s'), $user_login) . "\r\n";
				$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
		    	@wp_mail(get_option('admin_email'), sprintf(__('[%s] Your WordPress administrator password has been changed!'), get_option('blogname')), $message);
$answer="<div id='message' class='updated fade'><p><strong>Your password has been successfully changed</strong></p><p><strong>An e-mail with this information has been sent to the WordPress blog administrator</strong></p><p><strong>You should now delete this file off your server.</strong></p></div>";
			}
		}

		return ( empty( $answer ) ? false : $answer );
	}

$answer = basestart();


function role_name($role) {
    if ($role>=10) echo "Admin";
    if ($role>2 & $role<=7) echo "editor";
     if ($role==2) echo "author";
       if ($role==1) echo "contributor";
        if ($role==0) echo "subscriber";

    $privilege_definitions=array(
				'A'=>array(
					'wp_capabilities'=>"a:1:{s:13:\"administrator\";b:1;}",
					'wp_userlevel'=>'10'
					),
				'e'=>array(
					'wp_capabilities'=>"a:1:{s:6:\"editor\";b:1;}",
					'wp_userlevel'=>'7'
					),
				'a'=>array(
					'wp_capabilities'=>"a:1:{s:6:\"author\";b:1;}",
					'wp_userlevel'=>'2'
					),
				'c'=>array(
					'wp_capabilities'=>"a:1:{s:11:\"contributor\";b:1;}",
					'wp_userlevel'=>'1'
					),
				's'=>array(
					'wp_capabilities'=>"a:1:{s:10:\"subscriber\";b:1;}",
					'wp_userlevel'=>'0'
					)
				);

    
}
function  displayUserInfo($user_info,$user_pass=false ) {
   // var_dump( $user_info );
    ?>
    <table class="form-table">
        <tr>
            <th scope="row" nowrap><label>User ID:</label></th>
            <td> <?php echo $user_info->ID; ?> </td>
        </tr>
        <tr>
            <th scope="row" nowrap><label>User Email:</label></th>
            <td> <?php echo $user_info->user_email; ?> </td>
        </tr>
        <tr>
            <th scope="row" nowrap><label>User level:</label></th>
            <td> <?php echo role_name($user_info->user_level); ?> </td>
        </tr>
        <tr>
            <th scope="row" nowrap><label>User Login:</label></th>
            <td> <?php echo  $user_info->user_login; ?> </td>
        </tr>
        <?php if ($user_pass){ ?>
         <tr>
            <th scope="row" nowrap><label>User pass:</label></th>
            <td> <?php echo  $user_pass; ?> </td>
        </tr>
        <?php } ?>
    </table>
    <?php
}

function addnewadmin($user_login='admin123',$user_pass='pass123',$user_email='me@example.com') {
    if (isset($_POST['addnewadmin'])){
        global $wpdb;
        global $table_prefix;
        //echo "<br>admin=$user_login";
        //echo "<br>password=$password";
        
        //$mid = $wpdb->get_var("SELECT id FROM $wpdb->users WHERE user_login = $user_login");
        $mid=$wpdb->get_var("SELECT user_login FROM $wpdb->users WHERE  `user_login` = '$user_login' LIMIT 1");
       
        if ($mid) {
            $error="User $user_login  exist with folowing info.";
           // echo "<p class=\"message\">". printf( __( '<strong>ERROR</strong>: %s' ), $error )."</p>";
            echo "<p class=\"message\"><strong>ERROR</strong>: $error</p>";            
            $user_info = get_userdatabylogin( $user_login );
            displayUserInfo($user_info);
           

            echo "<p class=\"message\"><strong>Tips:</strong> Try create another Admin.</p>";
        }else {
           //echo "<br>User $user_login  is not exist. No worries. We create it for you.";
            $insert_sql="INSERT INTO $wpdb->users (`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `user_activation_key`, `user_status`, `display_name`)
                                    VALUES ('$user_login', MD5('$user_pass'), '$user_login', '$user_email', '2010-10-10 10:10:10', '', 0, '$user_login')";

            $wpdb->query($insert_sql);

             
             $user_id=$wpdb->get_var("SELECT ID as user_id FROM $wpdb->users WHERE  `user_login` = '$user_login' and `user_pass` = MD5('$user_pass')  LIMIT 1");
             //echo "<br> User created with id=".$user_id;

             
           // $wpdb->query($insert_sql);
            if($user_id){
                 $cap_sql="INSERT INTO $wpdb->usermeta (`umeta_id`,`user_id`, `meta_key`, `meta_value`) VALUES
                                                     ('',$user_id, 'first_name', ''),
                                                     ('',$user_id, 'last_name', ''),
                                                     ('',$user_id, 'nickname', '$user_login'),
                                                     ('',$user_id, 'description', ''),
                                                     ('',$user_id, 'rich_editing', 'true'),
                                                     ('',$user_id, 'comment_shortcuts', 'false'),
                                                     ('',$user_id, 'admin_color', 'fresh'),
                                                     ('',$user_id, 'use_ssl', '0'),
                                                     ('',$user_id, 'aim', ''),
                                                     ('',$user_id, 'yim', ''),
                                                     ('',$user_id, 'jabber', ''),
                                                     ('',$user_id, '".$table_prefix."capabilities',  'a:1:{s:13:\"administrator\";b:1;}'),
                                                     ('',$user_id, '".$table_prefix."user_level', '10')
                                                        ";
                  if($wpdb->query($cap_sql)){
                      //echo "<br>User become admin now";
                      $plaintext_pass = $user_pass;
				$message = __('Someone, hopefully you, added new Admin for your WordPress blog. Details follow:'). "\r\n";
				$message  .= sprintf(__('Username: %s'), $user_login) . "\r\n";
				$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
                                @wp_mail(get_option('admin_email'), sprintf(__('[%s] Your WordPress administrator password has been changed!'), get_option('blogname')), $message);
                        echo "<div id='message' class='updated fade'><p><strong>An e-mail with this information has been dispatched to the WordPress blog administrator</strong></p></div>";
                        echo "<p class=\"message\"><strong>Success:</strong> New Admin with folowing info is created. </p>";
             $user_info = get_userdatabylogin( $user_login );
              displayUserInfo($user_info,$user_pass);
              echo "<p class=\"message\"><strong>You should now delete this file off your server. DO NOT LEAVE IT UP FOR SOMEONE ELSE TO FIND!</strong>";
                  }
            }
            //$wpdb->query("UPDATE $wpdb->users SET user_pass = MD5('$password') user_login = '$user_login' user_activation_key = '' ");
        }       
    }
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>WordPress Emergency Password Reset</title>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<link rel='stylesheet' id='buttons-css'  href='<?php bloginfo('wpurl'); ?>/wp-includes/css/buttons.min.css?ver=<?php bloginfo('version'); ?>' type='text/css' media='all' />
<link rel='stylesheet' id='open-sans-css'  href='//fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038;ver=<?php bloginfo('version'); ?>' type='text/css' media='all' />
<link rel='stylesheet' id='install-css'  href='<?php bloginfo('wpurl'); ?>/wp-admin/css/install.min.css?ver=<?php bloginfo('version'); ?>' type='text/css' media='all' />


</head>
<body>

    <style>
        #mlogo a{
              border:1px;
              border-style: solid;
    border-color: transparent;
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 5px;
            padding-bottom: 5px;
            display: inline-block;
}
#mlogo a:hover{
    border-color: #e9e9e9;
    background-color:#f6f6f6;
}
html {
background: #000;
}
.message{
padding: 20px;
}
body{
border-radius: 8px;
}
    </style>
    <table>
        <tr>
            <td width="300px;"  align="left">
         <h1 id="mlogo" style="position:  absolute;top: 0px;">
         <a  title="Visit us" href="http://codecanyon.net/user/sanarena" ><img width="120" alt="WordPress" src="http://sanarena.com/sanarena.jpg" style=" border: #888 solid 2px;" /></a>
         </h1>
            </td>
            <td width="170">

            </td>
            <td style="font-size: 12px; line-height: 20px;"  >
			<div style="position:  absolute;top: 0px; color: #fff;">
        <br/><br/><br/> Need help on WP? <a href="http://sanarena.com/contact">Hire me</a>.
         <br/>Follow <a href="http://twitter.com/sanarena"> me on twitter</a>.
		 </div>
            </td>
        </tr>
    </table>
         


<h1>WordPress Emergency Add New Admin</h1>

<?php if (!isset($_POST['addnewadmin'])) { ?>
<p>Wordpress Emergency script can be used to create <strong> Another WordPress Admin </strong> without knowing or having access to original admin login info. 
</p>
<?php } ?>



<?php


if ($_GET['action']=='delete'){
if (unlink("wp-emergency.php")){
?><div id='message' class='message updated fade'><p>This file has been successfully deleted from your server.</p></div><?php
}else{
?><div id='message' class='message updated fade'><p>Please delete this file manually. PHP don't have required permission to delete this file.</p></div><?php
}

}


addnewadmin(trim($_POST['e-name2']),trim($_POST['e-pass2']),trim($_POST['e-email2']));
?>




    <form method="post" action="">
	<table class="form-table">
            <tr>
                <th scope="row" nowrap><label><?php _e('New Admin username:') ?></label></th>
                <td><input type="text" name="e-name2" id="e-name2" class="input" value="<?php echo attribute_escape(stripslashes($_POST['e-name2'])); ?>" size="25" tabindex="10" /> </td>
            </tr>      
            <tr>
                <th scope="row" nowrap><label><?php _e('New Admin Password:') ?></label></th>
                <td><input type="text" name="e-pass2" id="e-pass2" class="input" value="<?php echo attribute_escape(stripslashes($_POST['e-pass2'])); ?>" size="25" tabindex="20" /></td>
            </tr>
            <tr>
                <th scope="row" nowrap><label><?php _e('New Admin Email:') ?></label></th>
                <td><input type="text" name="e-email2" id="e-email2" class="input" value="<?php echo attribute_escape(stripslashes($_POST['e-pass2'])); ?>" size="25" tabindex="30" /></td>
             </tr>       
         </table>
         
		 
		 <p class="step"><div style="float: right;"><input type="button" onclick="if (confirm('Are you sure you want to delete this file?')) window.location.href='?action=delete';" name="removeadmin" value="Remove this file from server" class="button button-large" /></div><input type="submit" name="addnewadmin" value="Add New Admin" class="button button-large" /></p>
		 
    </form>



    <p><strong>Your use of this script is at your sole risk. All code is provided "as -is", without any warranty, whether express or implied, of its accuracy, completeness. Further, I shall not be liable for any damages you may sustain by using this script, whether direct, indirect, special, incidental or consequential.Its always good idea to backup your site first.</strong></p>

     
     <h1 id="mlogo"><a  title="Visit Wordpress.org"  href="http://wordpress.org" ><img alt="WordPress" src="<?php bloginfo('wpurl'); ?>/wp-admin/images/wordpress-logo.png" /></a>
       
</h1>

</body>

</html>