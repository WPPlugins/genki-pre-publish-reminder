<?php
/* 
Plugin Name: Genki Pre-Publish Reminder
Version: 1.4
Plugin URI: http://ericulous.com/2007/03/19/wp-plugin-genki-pre-publish-reminder/
Description: Display a pre-publish reminder tasklist on the Posts Admin Panel
Author: Genkisan
Author URI: http://ericulous.com
*/

add_action ('admin_menu', 'genki_pre_publish_reminder_menu');

function genki_pre_publish_reminder_menu() {
	add_option('genki_pre_publish_reminder_location', 1);
	add_option('genki_pre_publish_reminder_list', 'Enter your reminder list here.');
	add_option('genki_pre_publish_reminder_bordercolor', '#e6db55');
	add_option('genki_pre_publish_reminder_bgcolor', '#ffffe0');
	
	if (get_option('genki_pre_publish_reminder_location') == '1') {
		global $wp_db_version;
		
		if ( $wp_db_version > 7097 ) { // wp version 2.5
			$location = 'submitpost_box';
		} else {
			$location = 'dbx_post_sidebar';
		}
	}
	else {
		$location = 'edit_form_advanced';
	}
	
	add_action($location, 'genki_pre_publish_reminder_sidebar');
	if (function_exists('add_management_page')) {
		add_options_page('Pre-Publish Reminder', 'Pre-Publish Reminder', 8, __FILE__, 'genki_pre_publish_reminder_manage');
	}
}

function genki_pre_publish_reminder_sidebar() {
$location = get_option('genki_pre_publish_reminder_location');
$list = get_option('genki_pre_publish_reminder_list');
$bordercolor = get_option('genki_pre_publish_reminder_bordercolor');
$bgcolor = get_option('genki_pre_publish_reminder_bgcolor');
if ($location == '1') {
?>
<style type="text/css">
code { font-family:Verdana, Arial, Helvetica, sans-serif;font-size:1em;padding:0;background-color:<?php echo $bgcolor ?> }
</style>
<fieldset class="dbx-box">
<div class="dbx-content" style="font-size:0.9em;padding:10px;margin: 0 0 10px 0;border: 1px solid <?php echo $bordercolor; ?>; background: <?php echo $bgcolor ?>;">
<?php
eval('?>'.genki_cmu_encode_xml(nl2br(stripslashes($list))));
echo '</div></fieldset>';
}
else {
?>
<style type="text/css">
code { font-family:Verdana, Arial, Helvetica, sans-serif;font-size:1em;padding:0;background-color:<?php echo $bgcolor ?> }
</style>
<div class="dbx-b-ox-wrapper">
<fieldset class="dbx-box">
<div class="dbx-h-andle-wrapper">
</div>

<div class="dbx-c-ontent-wrapper" style="font-size:0.9em;padding:10px;border: 2px solid <?php echo $bordercolor; ?>; background: <?php echo $bgcolor ?>; margin-bottom: 20px;">
<div class="dbx-content" style="padding:10px;">
<?php
eval('?>'.genki_cmu_encode_xml(nl2br(stripslashes($list))));
echo '</div></div></fieldset></div>';
}
}

function genki_pre_publish_reminder_manage() {
if (isset($_POST['update_message'])) {

	?><div id="message" class="updated fade"><p><strong><?php

	update_option('genki_pre_publish_reminder_location', $_POST['location']);
	update_option('genki_pre_publish_reminder_list', $_POST['list']);
	update_option('genki_pre_publish_reminder_bordercolor', $_POST['bordercolor']);
	update_option('genki_pre_publish_reminder_bgcolor', $_POST['bgcolor']);

	echo "Options Updated!";

	?></strong></p></div><?php
}

$location = get_option('genki_pre_publish_reminder_location');
$list = get_option('genki_pre_publish_reminder_list');
$bordercolor = get_option('genki_pre_publish_reminder_bordercolor');
$bgcolor = get_option('genki_pre_publish_reminder_bgcolor');
?>
	<div class=wrap>
	<h2>Pre-Publish Reminder</h2>
		<form name="formamt" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<fieldset class="options">
				<legend><strong>To show the Reminder List in</strong>
					<p><input name="location" id="location" type="radio" value="1" <?php if ($location == '1') echo 'checked' ; ?>> Post sidebar</input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input name="location" id="location" type="radio" value="0" <?php if ($location == '0') echo 'checked' ; ?>> Below post textarea</input>
					</p>
				</legend>
			</fieldset>
            <br />
			<fieldset class="options">
				<legend><strong>Style Reminder List</strong>
					<p>Border Color: <input name="bordercolor" id="bordercolor" type="text" value="<?php echo $bordercolor; ?>"></input><br />
					Background Color: <input name="bgcolor" id="bgcolor" type="text" value="<?php echo $bgcolor; ?>"></input>
					</p>
				</legend>
			</fieldset>
            <br />
			<fieldset class="options">
				<legend><strong>Reminder List</strong><p>HTML tags will be rendered as erm... html while php codes will be executed as erm... php codes.<br />To display unformatted html or php, enclose them in the &lt;code>&lt;/code> tag <br />e.g &lt;code>&lt;a href="http://code.com">show as code&lt;/a>&lt;/code></p>
					<p><textarea name="list" id="list" cols="40" rows="8" style="width: 80%;"><?php echo stripslashes($list); ?></textarea></p>
				</legend>
			</fieldset>
            <br />
			<fieldset class="options">
				<legend><strong>Preview</strong>
					<style type="text/css">
                    code { font-family:Verdana, Arial, Helvetica, sans-serif;font-size:1em;padding:0;background-color:<?php echo $bgcolor ?> }
                    </style>
					<div class="dbx-b-ox-wrapper">
					<fieldset class="dbx-box">
					
					<div class="dbx-c-ontent-wrapper" style="border: 1px solid <?php echo $bordercolor; ?>; background: <?php echo $bgcolor ?>; margin-bottom: 20px;">
					<div class="dbx-content" style="padding:10px;">
					<?php
					eval('?>'.genki_cmu_encode_xml(nl2br(stripslashes($list))));
					?>
					</div>
					</div>
					</fieldset>
					</div>
				</legend>
			</fieldset>
            <br />
			<p class="submit">
				<input type="submit" name="update_message" value="Save Changes" class="button-primary" />
			</p>
		</form>
	</div>
<?php
}

//functions from Code Markup Plugin
//http://www.thunderguy.com/semicolon/wordpress/code-markup-wordpress-plugin/

function genki_cmu_encode_xml($content) {
/*
	Look for <code> sections in the content and escape certain characters,
	depending on the allow and lang attributes.
	Also remove newlines after <code...> and before </code> so code displays
	nicely in <pre> blocks.
*/
	return preg_replace_callback('!<code([^>]*)>(?:\r\n|\n|\r|)(.*?)(?:\r\n|\n|\r|)</code>!ims', 'genki_cmu_encode_xml_callback', $content);
}

// ===== Callback functions ==================================================

function genki_cmu_encode_xml_callback($matches) {
/*
	Encode XML in a <code> tag.
*/
	$attributes = $matches[1];
	$escapedContent = $matches[2];

	// Escape html special chars
	$escapedContent = htmlspecialchars($escapedContent, ENT_NOQUOTES);
	return "<code$attributes>$escapedContent</code>";
}

function genki_cmu_unescape_qq_callback($matches) {
/*
	Unescape double quotes in a <pre> tag.
*/
	return "<pre{$matches[1]}>".str_replace('\"', '"', $matches[2])."</pre>";
}

function genki_cmu_untexturize_code_callback($matches) {
/*
	Undo the effect of wptexturize() within a <code> element.
	wptexturize() is meant to handle this but is buggy...
	BUGS: Turns --- into -- and `` into "
*/
	$fancy = array('&#215;', '&#8216;', '&#8217;', '&#8242;', '&#8220;', '&#8221;', '&#8243;', '&#8212;', '&#8211;', '&#8230;', '&#8220;');
	$plain = array('x'     ,'\''     , '\''     , '\''     , '"'      , '"'      , '"'      , '--'     , '--'     , '...'    , '``'     );
	return "<code{$matches[1]}>".str_replace($fancy, $plain, $matches[2])."</code>";
}
?>