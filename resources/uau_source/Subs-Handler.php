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

function parseAgreement($content)
{

	// Globalize what we need.
	global $modSettings;

	// No BBC? At least fix our line-breaks.
	if (empty($modSettings['agreementBBC']))
		return $content = str_replace("\n", '<br />', $content);
	// If we are parsing BBC, Smileys or not?
	else
		return $content = parse_bbc($content, !empty($modSettings['agreementSmileys']) ? true : false, '');

}

function userAgreementAddLanguage()
{

	// Globalize stuffff.
	global $context, $boarddir, $sourcedir;

	// Clear the cache, prior to getting the languages.
	clean_cache();

	// Get the languages.
	getLanguages();

	// A simple loop, of languages.
	foreach ($context['languages'] as $key => $language)
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