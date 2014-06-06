<?php
/**
 * User Agreement Update
 *
 * @file Handler.php
 * @author Labradoodle-360
 * @copyright Matthew Kerle 2012
 *
 * @version 1.0.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function HandlerFunc()
{

	// Our Globals...
	global $context, $txt, $scripturl, $settings, $modSettings;
	global $boarddir, $boardurl, $agreement, $smcFunc, $user_info, $sourcedir;

	// Subs-Handler.php.
	require_once($sourcedir . '/uau_source/Subs-Handler.php');

	// Loadss
	loadTemplate('/uau_template/Handler');
	loadLanguage('/uau_language/Handler');

	// Linktree Item
	$context['linktree'][] = array(
		'name' => $txt['updated_user_agreement'],
	);

	// Page Title
	$context['page_title'] = $txt['updated_user_agreement'];

	// Our CSS File & jQuery Implementation
	$context['html_headers'] .= "\n" . '
		<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/uau_css/handler.css" />
		<script type="text/javascript">
			//<![CDATA[
			if (!window.jQuery) {
				document.write(unescape("%3Cscript type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js\"%3E%3C/script%3E"));
			}
			else {
			}
			//]]>
		</script>
		<script type="text/javascript">
			//<![CDATA[
			$(document).ready(function() {
				$("#has_read").change(function() {
					if ($(this).val() == 1) {
						$("#which_input").html(\'<input type="submit" value="' . $txt['re_accept_agreement'] . '" id="submit" name="submit" class="button_submit bloated_input" />\');
					}
					else {
					}
				});
			});
			//]]>
		</script>
	';

	// Processing...
	if (isset($_POST['submit']))
	{
		// Check the session.
		checkSession('post', '', true);

		// Don't make them do it again...
		updateMemberData($user_info['id'], array('has_agreed' => 1));

		// Send 'em home.
		redirectexit();
	}

	// First, we try for the regular agreement in OUR language.
	if (file_exists($boarddir . '/agreement.' . $user_info['language'] . '.txt') == true)
		$agreement = parseAgreement(file_get_contents($boarddir . '/agreement.' . $user_info['language'] . '.txt'));
	// If we can't get the updated agreement in our language, try the DEFAULT agreement in our dialect.
	elseif (file_exists($boarddir . '/default-agreement.' . $user_info['language'] . '.txt') == true)
		$agreement = parseAgreement(file_get_contents($boarddir . '/default-agreement.' . $user_info['language'] . '.txt'));
	// Otherwise, we'll have to go back to plain old English (updated).
	elseif (file_exists($boarddir . '/agreement.txt') == true)
		$agreement = parseAgreement(file_get_contents($boarddir . '/agreement.txt'));
	// Wow, this is our LAST resort.
	elseif (file_exists($boarddir . '/default-agreement.txt') == true)
		$agreement = parseAgreement(file_get_contents($boarddir . '/default-agreement.txt'));
	// Oh well, we tried.
	else
		fatal_error($txt['invalid_language_file'], true);

}

function userAgreementUpdate()
{

	// Globalize everything...
	global $txt, $boarddir, $context, $modSettings, $smcFunc;
	global $settings, $user_info, $sourcedir, $membergroups, $exploded_membergroups;

	// Subs-Handler.php.
	require_once($sourcedir . '/uau_source/Subs-Handler.php');

	// Some "loads".
	loadTemplate('/uau_template/Handler');
	loadLanguage('/uau_language/Handler');

	// Membergroups, cached for your convenience.
	if (cache_get_data('lab_membergroups', 120) != null)
		$membergroups = cache_get_data('lab_membergroups', 120);
	else
		loadMemberGroups();

	// By default we look at agreement.txt.
	$context['current_agreement'] = '';

	// Is there more than one to edit?
	$context['editable_agreements'] = array(
		'' => $txt['admin_agreement_default'],
	);

	// Get our languages.
	getLanguages();

	// Safety first.
	$variables = array(
		'agreementBBC' => '0',
		'agreementSmileys' => '0',
		'requireAgreement' => '1',
		'requireReagreement' => '0',
		'userAgreementUpdateMode' => 'strict',
		'lastUpdatedUA' => '',
	);
	foreach ($variables as $key => $default)
	{
		if (!isset($modSettings[$key]))
			$modSettings[$key] = $default;
	}

	// Try to figure out if we have more agreements.
	foreach ($context['languages'] as $lang)
	{
		// If we have more agreements, let's summon them.
		if (file_exists($boarddir . '/agreement.' . $lang['filename'] . '.txt'))
		{
			// Then feed them for template stuff.
			$context['editable_agreements']['.' . $lang['filename']] = $lang['name'];

			// If you're trying to modify a different language, this is where it's at!
			if (isset($_POST['agree_lang']) && $_POST['agree_lang'] == '.' . $lang['filename'])
				$context['current_agreement'] = '.' . $lang['filename'];
			elseif (!isset($_POST['agree_lang']) && $user_info['language'] == $lang['filename'])
				$context['current_agreement'] = '.' . $lang['filename'];
		}
	}

	// Our main agreement, original agreement, and the possibility of a warning.
	$context['agreement'] = file_exists($boarddir . '/agreement' . $context['current_agreement'] . '.txt') ? $smcFunc['htmlspecialchars']((file_get_contents($boarddir . '/agreement' . $context['current_agreement'] . '.txt')), ENT_QUOTES) : '';
	$context['original_agreement'] = file_exists($boarddir . '/default-agreement' . $context['current_agreement'] . '.txt') ? $smcFunc['htmlspecialchars']((file_get_contents($boarddir . '/default-agreement' . $context['current_agreement'] . '.txt')), ENT_QUOTES) : '';
	$context['warning'] = is_writable($boarddir . '/agreement' . $context['current_agreement'] . '.txt') ? '' : $txt['agreement_not_writable'];

	// Yes! We are multilingual!
	if (empty($context['current_agreement']))
	{
		// If we only have English, no reason to go farther.
		$original_agreement = utf8_encode(un_htmlspecialchars($context['original_agreement']));
		$revision_agreement = utf8_encode(un_htmlspecialchars($context['agreement']));
	}
	else
	{
		// Otherwise, we do have another language, pretty cool. Let's go!
		if (file_exists($boarddir . '/Themes/default/languages/index' . $context['current_agreement'] . '.php') == true)
		{

			// Assuming the language's index file ./languages/index.language exists, it's exploded into an array.
			$lang_file = explode("\n", $smcFunc['htmlspecialchars'](file_get_contents($boarddir . '/Themes/default/languages/index' . $context['current_agreement'] . '.php'), ENT_QUOTES));

			// Then, we go hunting for our character encoding.
			for ($counter = 0; $counter <= count(array_keys($lang_file)); $counter += 1)
			{
				// If we find it, we've got some work to do.
				if (strpos($lang_file[$counter], 'lang_character_set'))
				{
					// In the end, we only need the ISO type.
					$searches = array(
						$smcFunc['htmlspecialchars']('$txt[\'lang_character_set\'] = \'', ENT_QUOTES),
						$smcFunc['htmlspecialchars']('\';', ENT_QUOTES),
					);

					// Return the stripped ISO, and end the loop.
					$context['character_set'] = str_replace($searches, '', $lang_file[$counter]);
					break;
				}
			}
		}

		// Some accented characters could cause a problem, this is for jQuery restore to functions.
		$original_agreement = utf8_encode(htmlentities(un_htmlspecialchars($context['original_agreement'])));
		$revision_agreement = utf8_encode(htmlentities(un_htmlspecialchars($context['agreement'])));
	}

	// Our CSS file, jQuery include, and some jQuery.
	$context['html_headers'] .= "\n" . '
		<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/uau_css/handler.css" />
		<script type="text/javascript">
			//<![CDATA[
			if (!window.jQuery) {
				document.write(unescape("%3Cscript type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js\"%3E%3C/script%3E"));
			}
			else {
			}
			//]]>
		</script>
		<style type="text/css">
			.auto_suggest_div {
				border: 1px solid #ccc;
				position: absolute;
				visibility: hidden;
			}
			.auto_suggest_item {
				padding: 6px;
				background: #fff;
				border-bottom: 1px solid #ccc;
			}
			.auto_suggest_item_hover {
				padding: 6px;
				background: #e8e8e8;
				cursor: pointer;
				color: #4c4c4c;
				border-bottom: 1px solid #ccc;
			}
		</style>
		<script type="text/javascript">
			//<![CDATA[
				$(document).ready(function() {
					$("#restore_original").click(function() {
						if (confirm("' . sprintf($txt['restore_jquery'], $txt['restore_opt_default']) . '")) {
							$("#agreement").val(' . JavaScriptEscape(html_entity_decode($original_agreement)) . ');
						}
						else {
						}
					});
					$("#restore_latest_rev").click(function() {
						if (confirm("' . sprintf($txt['restore_jquery'], $txt['restore_opt_latest']) . '")) {
							$("#agreement").val(' . JavaScriptEscape(html_entity_decode($revision_agreement)) . ');
						}
						else {
						}
					});
					// If the registration agreement isn\'t required, don\'t show anything else.
					var require_agreement = $("#requireAgreement").attr("checked");
					if (require_agreement == "checked") {
					}
					else {
						$("#required_mode_only").slideUp("slow");
					}
					$("#requireAgreement").change(function() {
						var current_value = $(this).attr("checked");
						if (current_value == "checked") {
							$("#required_mode_only").slideDown("fast");
						}
						else {
							$("#required_mode_only").slideUp("fast");
						}
					});
					// If we don\'t require members to update, no point in the next two settings.
					var require_reagreement = $("#requireReagreement").attr("checked");
					if (require_reagreement == "checked") {
						$("#member_dependent").slideDown("slow");
					}
					else {
					}
					$("#requireReagreement").change(function() {
						var current_value = $(this).attr("checked");
						if (current_value == "checked") {
							$("#member_dependent").slideDown("slow");
						}
						else {
							$("#member_dependent").slideUp("slow");
						}
					});
					// Direct Agreement Options
					var agreement_bbc = $("#agreementBBC").attr("checked");
					if (agreement_bbc == "checked") {
							$("#left_column").animate({width: \'49%\'}, 3000);
							$("#right_column").delay(1100).fadeIn("slow").animate({width: \'50%\'}, 2000);
					}
					else {
					}
					$("#agreementBBC").change(function() {
						var current_value = $(this).attr("checked");
						if (current_value == "checked") {
							$("#left_column").animate({width: \'49%\'}, 3000);
							$("#right_column").delay(1100).fadeIn("slow").animate({width: \'50%\'}, 2000);
						}
						else {
							$("#right_column").animate({width: \'20%\'}, 2000).delay(100).fadeOut("slow");
							$("#left_column").animate({width: \'79%\'}, 2000).delay(400).animate({width: \'100%\'}, 2000);
						}
					});
					// Check All / Uncheck All
					$("#primary_mgroups_check").click(function()
					{
						$("#primary_mgroups :input").attr("checked", "checked");
					});
					$("#primary_mgroups_uncheck").click(function()
					{
						$("#primary_mgroups :input").removeAttr("checked", "checked");
					});
					$("#postbased_mgroups_check").click(function()
					{
						$("#postbased_mgroups :input").attr("checked", "checked");
					});
					$("#postbased_mgroups_uncheck").click(function()
					{
						$("#postbased_mgroups :input").removeAttr("checked", "checked");
					});
					// Membergroups jQuery
					$("#expand_membergroups").click(function()
					{
						$("#membergroups_group").slideDown("slow");
						$("#collapse_membergroups").removeClass("hidden");
						$("#expand_membergroups").addClass("hidden");
					});
					$("#collapse_membergroups").click(function()
					{
						$("#membergroups_group").slideUp("slow");
						$("#expand_membergroups").removeClass("hidden");
						$("#collapse_membergroups").addClass("hidden");
					});
				});
			//]]>
		</script>
	';

	// Submitting, we are.
	if (isset($_POST['agreement']))
	{

		// Validate our session.
		checkSession('post', '', true);

		// Off it goes to the agreement file.
		if (!empty($_POST['agreement']))
			file_put_contents($boarddir . '/agreement' . $context['current_agreement'] . '.txt', trim($_POST['agreement']));

		// No sneaking...
		$primary_membergroups = array();
		$post_based_membergroups = array();
		if (isset($_POST['agreementMembergroups']))
		{
			foreach ($_POST['agreementMembergroups'] as $key => $value)
			{
				if (array_key_exists($value, $membergroups['primary']))
					$primary_membergroups[] = (int) $value;
				else
					$post_based_membergroups[] = (int) $value;
			}
		}
		$bypassed_members = array();
		if (isset($_POST['bypassed_members']))
		{
			foreach ($_POST['bypassed_members'] as $key => $value)
			{
				$bypassed_members[] = (int) $value;
			}
		}

		// Our post variables to update.
		$post_vars = array(
			'agreementBBC' => !empty($_POST['agreementBBC']) ? 1 : 0,
			'agreementSmileys' => !empty($_POST['agreementSmileys']) ? 1 : 0,
			'requireAgreement' => !empty($_POST['requireAgreement']) ? 1 : 0,
			'requireReagreement' => !empty($_POST['requireReagreement']) ? 1 : 0,
			'userAgreementUpdateMode' => isset($_POST['userAgreementUpdateMode']) ? $_POST['userAgreementUpdateMode'] : 'strict',
			'lastUpdatedUA' => !empty($_POST['requireReagreement']) ? time() : '',
		);

		// Send them through.
		updateSettings($post_vars);

		// Time to flip the reset switch.
		if (!empty($_POST['requireReagreement']) && $smcFunc['htmlspecialchars']($_POST['agreement'], ENT_QUOTES) != $context['agreement'])
		{
			$where = '';
			if (!empty($primary_membergroups) && count(array_keys($primary_membergroups) == 1))
				$formatted_primary_groups = array($primary_membergroups[0]);
			else
				$formatted_primary_groups = !empty($primary_membergroups) ? implode(',', $primary_membergroups) : '';
			if (!empty($post_based_membergroups) && count(array_keys($post_based_membergroups) == 1))
				$formatted_post_based_groups = array($post_based_membergroups[0]);
			else
				$formatted_post_based_groups = !empty($post_based_membergroups) ? implode(',', $post_based_membergroups) : '';
			if (empty($context['current_agreement']))
				$where = 'WHERE (lngfile = \'\' OR lngfile = {string:english})';
			else
				$where = 'WHERE lngfile = {string:language}';
			if (!empty($formatted_primary_groups))
				$where .= ' AND id_group NOT IN({array_int:primary_groups})
				AND NOT FIND_IN_SET({array_int:primary_groups}, additional_groups)';
			if (!empty($formatted_post_based_groups))
				$where .= ' AND NOT FIND_IN_SET({array_int:post_based_groups}, id_post_group)';
			if (!empty($bypassed_members))
				$where .= ' AND NOT FIND_IN_SET({array_int:bypassed_members}, id_member)';
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET has_agreed = {int:set_false}
				' . $where,
				array(
					'set_false' => 0,
					'english' => 'english',
					'primary_groups' => $formatted_primary_groups,
					'post_based_groups' => $formatted_post_based_groups,
					'language' => !empty($context['current_agreement']) ? str_replace('.', '', $context['current_agreement']) : '',
					'bypassed_members' => $bypassed_members
				)
			);
		}

		// Then let them know we've saved...?
		$context['html_headers'] .= "\n" . '
			<script type="text/javascript">
				//<![CDATA[
					$(document).ready(function() {
						$("#profile_success").delay(500).slideDown("slow");
						$("#close_success").click(function() {
							$("#profile_success").delay(500).slideUp("slow");
						});
					});
				//]]>
			</script>
		';
	}

	// When did we last reset, again...?
	$context['lab_last_reset'] = !empty($modSettings['lastUpdatedUA']) ? timeformat($modSettings['lastUpdatedUA'], true) : '<em>' . $txt['lab_never'] . '</em>';

	// Every once in a while, let's show a donate block.
	if (mt_rand(0,2) == 1)
	{
		$context['please_donate'] = '
			<div class="information" style="margin-top: 12px;">
				<span class="floatleft lab_icon_medium">
					<img src="' . $settings['images_url'] . '/uau_images/thumb-up.png" alt="" />
				</span>
				<span class="floatright" style="text-align: left; margin-top: 9px; width: 97%;">
					' . $txt['lab_please_donate'] . '
				</span>
			</div>
		';
	}
	else
		$context['please_donate'] = '';

	// Template Layers.
	$context['template_layers'][] = 'copyright';

	// Our sub-template.
	$context['sub_template'] = 'user_agreement_update';

	// The page title.
	$context['page_title'] = $txt['registration_agreement'];

}