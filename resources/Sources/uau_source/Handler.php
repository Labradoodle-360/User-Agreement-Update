<?php
/**
 * User Agreement Update
 *
 * @file Handler.php
 * @author Labradoodle-360
 * @copyright Matthew Kerle 2012-2014
 *
 * @version 1.0.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function HandlerFunc()
{

	// Our Globals...
	global $context, $txt, $settings, $agreement, $user_info, $sourcedir;

	// Subs-Handler.php.
	require_once($sourcedir . '/uau_source/Subs-Handler.php');

	// Loadss
	loadTemplate('uau_template/Handler');
	loadLanguage('uau_language/Handler');

	// Linktree Item
	$context['linktree'][] = array(
		'name' => $txt['updated_user_agreement']
	);

	// Page Title
	$context['page_title'] = $txt['updated_user_agreement'];

	// Our CSS File & jQuery Implementation
	$context['html_headers'] .= "\n" . '
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/uau_css/handler.css" />
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/uau_javascript/handler.js"></script>
		<script type="text/javascript">
			//<![CDATA[
			if (!window.jQuery) {
				document.write(unescape("%3Cscript src=\"//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"%3E%3C/script%3E"));
			}
			//]]>
		</script>
		<script type="text/javascript">
			//<![CDATA[
			var reaccept_agreement = ' . JavaScriptEscape($txt['re_accept_agreement']) . ';
			jQuery(document).ready(function($) {
				uau_reaccept_agreement($, reaccept_agreement);
			});
			//]]>
		</script>
	';

	//-- Specify the proper sub-template for use.
	$context['sub_template'] = 'reaccept';

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

	$agreement = parseAgreement();

}

function userAgreementUpdate()
{

	// Globalize everything...
	global $txt, $boarddir, $context, $modSettings, $smcFunc;
	global $settings, $user_info, $sourcedir, $membergroups;

	// Subs-Handler.php.
	require_once($sourcedir . '/uau_source/Subs-Handler.php');

	// Some "loads".
	loadTemplate('uau_template/Handler');
	loadLanguage('uau_language/Handler');

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
		'uau_agreementBBC' => '0',
		'uau_agreementSmileys' => '0',
		'requireAgreement' => '1',
		'uau_requireReagreement' => '0',
		'uau_userAgreementUpdateMode' => 'strict',
		'uau_lastUpdatedUA' => '',
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
			$context['editable_agreements']['.' . $lang['filename']] =  str_replace('utf8', '(UTF-8)', str_replace('-', ' ', $lang['name']));

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
	if (!empty($context['current_agreement']))
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
	else
	{
		// If we only have English, no reason to go farther.
		$original_agreement = utf8_encode(un_htmlspecialchars($context['original_agreement']));
		$revision_agreement = utf8_encode(un_htmlspecialchars($context['agreement']));
	}

	// Our CSS file, jQuery include, and some jQuery.
	$context['html_headers'] .= "\n" . '
		<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/uau_css/handler.css" />
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/uau_javascript/handler.js"></script>
		<meta http-equiv="Content-Type" content="text/html;charset=' . $context['character_set'] . '">
		<script type="text/javascript">
			//<![CDATA[
			if (!window.jQuery) {
				document.write(unescape("%3Cscript src=\"//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js\"%3E%3C/script%3E"));
			}
			else {
			}
			//]]>
		</script>
		<style type="text/css">
		</style>
		<script type="text/javascript">
			//<![CDATA[
				var confirm_latest    = ' . JavaScriptEscape(sprintf($txt['restore_jquery'], $txt['restore_opt_latest'])) . ';
				var latest_agreement  = ' . JavaScriptEscape(html_entity_decode($revision_agreement)) . ';
				var confirm_default   = ' . JavaScriptEscape(sprintf($txt['restore_jquery'], $txt['restore_opt_default'])) . ';
				var default_agreement = ' . JavaScriptEscape(html_entity_decode($original_agreement)) . ';
				jQuery(document).ready(function($) {
					uau_admin_scripts($, confirm_latest, latest_agreement, confirm_default, default_agreement);
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
			foreach ($_POST['agreementMembergroups'] as $value)
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
			foreach ($_POST['bypassed_members'] as $member)
			{
				$bypassed_members[] = (int) $member;
			}
		}

		// Our post variables to update.
		$post_vars = array(
			'uau_agreementBBC' => !empty($_POST['uau_agreementBBC']) ? 1 : 0,
			'uau_agreementSmileys' => !empty($_POST['uau_agreementSmileys']) ? 1 : 0,
			'requireAgreement' => !empty($_POST['requireAgreement']) ? 1 : 0,
			'uau_requireReagreement' => !empty($_POST['uau_requireReagreement']) ? 1 : 0,
			'uau_userAgreementUpdateMode' => isset($_POST['uau_userAgreementUpdateMode']) ? $_POST['uau_userAgreementUpdateMode'] : 'strict',
			'uau_lastUpdatedUA' => !empty($_POST['uau_requireReagreement']) ? time() : '',
		);

		// Send them through.
		updateSettings($post_vars);

		// Time to flip the reset switch.
		if (!empty($_POST['uau_requireReagreement']) && $smcFunc['htmlspecialchars']($_POST['agreement'], ENT_QUOTES) != $context['agreement'])
		{
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
					jQuery(document).ready(function($) {
						uau_success_notification($);
					});
				//]]>
			</script>
		';
	}

	// When did we last reset, again...?
	$context['lab_last_reset'] = !empty($modSettings['uau_lastUpdatedUA']) ? timeformat($modSettings['uau_lastUpdatedUA'], true) : '<em>' . $txt['lab_never'] . '</em>';

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