<?php
/**
 * User Agreement Update
 *
 * @file Subs-Handler.php
 * @author Labradoodle-360
 * @copyright Matthew Kerle 2012
 *
 * @version 1.0.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function retrieveAgreement() {

	//-- Globalize what we'll need here...
	global $boarddir, $txt, $user_info;

	// First, we try for the regular agreement in OUR language.
	if (file_exists($boarddir . '/agreement.' . $user_info['language'] . '.txt') == true)
		$path = 'agreement.' . $user_info['language'] . '.txt';
	// If we can't get the updated agreement in our language, try the DEFAULT agreement in our dialect.
	else if (file_exists($boarddir . '/default-agreement.' . $user_info['language'] . '.txt') == true)
		$path = 'default-agreement.' . $user_info['language'] . '.txt';
	// Otherwise, we'll have to go back to plain old English (updated).
	else if (file_exists($boarddir . '/agreement.txt') == true)
		$path = 'agreement.txt';
	// Wow, this is our LAST resort.
	else if (file_exists($boarddir . '/default-agreement.txt') == true)
		$path = 'default-agreement.txt';
	// Oh well. We did everything we could.
	else
		fatal_error($txt['invalid_language_file'], true);

	//-- Go get the agreement we defined above, assuming fatal_error() didn't stop them first.
	$content = file_get_contents($boarddir . '/' . $path);

	// And send it away.
	return $content;

}

function parseAgreement()
{

	// Globalize what we need.
	global $modSettings;

	//-- Get our agreement so that we can parse it.
	$content = retrieveAgreement();

	// No BBC? At least fix our line-breaks.
	if (empty($modSettings['agreementBBC']))
		$content = str_replace("\n", '<br />', $content);
	// If we are parsing BBC, Smileys or not?
	else
		$content = parse_bbc($content, !empty($modSettings['agreementSmileys']) ? true : false, '');

	return $content;
}

function userAgreementAddLanguage()
{

	// Globalize stuffff.
	global $context, $boarddir;

	// Clear the cache, prior to getting the languages.
	clean_cache();

	// Get the languages.
	getLanguages();

	// A simple loop, of languages.
	foreach ($context['languages'] as $language)
	{
		// If the agreement file exists for the language, and a default doesn't, we shall create one based on the default.
		if (file_exists($boarddir . '/agreement.' . $language['filename'] . '.txt') == true && file_exists($boarddir . '/default-agreement.' . $language['filename'] . '.txt') == false)
		{

			// The original...
			$agreement_content = file_get_contents($boarddir . '/agreement.' . $language['filename'] . '.txt');

			// Then the new one.
			file_put_contents($boarddir . '/default-agreement.' . $language['filename'] . '.txt', $agreement_content);

		}
	}

}

function userAgreementDeleteLanguage($lang)
{

	// Globalize what we'll need. >:D
	global $smcFunc, $boarddir;

	// Careful there, pal.
	if (empty($lang))
		return;

	// First, we delete the default-agreement.language.php file.
	if (file_exists($boarddir . '/default-agreement.' . $lang . '.txt'))
		unlink($boarddir . '/default-agreement.' . $lang . '.txt');

	// Then, we should probably clean up just a 'lil.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}members
		SET has_agreed = {int:has_agreed_int}
		WHERE lngfile = {string:language}
		AND has_agreed = {int:not_agreed_int}',
		array(
			'not_agreed_int' => 0,
			'has_agreed_int' => 1,
			'language' => $lang,
		)
	);

}

function loadMemberGroups()
{

	// Globalize everything we'll need here...
	global $smcFunc, $membergroups, $txt;

	// Quicklyyy, grab our language!
	loadLanguage('uau_language/Handler');

	// What is our request?
	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name, online_color, min_posts
		FROM {db_prefix}membergroups
		ORDER BY id_group'
	);

	// Do we have results, please?
	$membergroups = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{

		// ID Array
		$membergroups['id_array'][] = $row['id_group'];

		// Here are our Primary Membergroups
		if ($row['min_posts'] == '-1')
		{
			$membergroups['primary'][$row['id_group']] = array(
				'id' => $row['id_group'],
				'name' => $row['group_name'],
				'color' => !empty($row['online_color']) ? $smcFunc['strtolower']($row['online_color']) : 0
			);
		}
		// Then Post-Based Membergroups.
		else
		{
			$membergroups['post_based'][$row['id_group']] = array(
				'id' => $row['id_group'],
				'name' => $row['group_name'],
				'color' => !empty($row['online_color']) ? $smcFunc['strtolower']($row['online_color']) : 0
			);
		}

	}

	// Free the results!!!
	$smcFunc['db_free_result']($request);

	// "Regular" Members ;)
	$membergroups['id_array'][] = '0';
	$membergroups['primary'] += array(
		'0' => array(
			'id' => '0',
			'name' => $txt['uau_reg_mem'],
			'color' => ''
		)
	);

	// Proudly cached for your convenience.
	cache_put_data('lab_membergroups', $membergroups, 120);

	// Return our findings.
	return $membergroups;

}