<?php
/**
 * User Agreement Update
 *
 * @file uau_remove_database.php
 * @author Labradoodle-360
 * @copyright 2011 Matthew Kerle - All Rights Reserved
 *
 * @version 1.0.3
 */
 
// Using SSI?
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<strong>Error:</strong> Cannot uninstall - please make sure that this file in the same directory as SMF\'s SSI.php file.');

// We need packages...
if (SMF == 'SSI')
	db_extend('packages');

// Globalize what we need...
global $smcFunc, $context, $boarddir;

// Remove our settings.
$variables = array(
	'agreementBBC',
	'agreementSmileys',
	'requireReagreement',
	'userAgreementUpdateMode',
	'lastUpdatedUA',
);
foreach ($variables as $value)
{
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable = {string:current_variable}',
		array(
			'current_variable' => $value,
		)
	);
}

// Our languages
getLanguages();

// Remove it...
$smcFunc['db_remove_column']('{db_prefix}members', 'has_agreed');

// Add our default-agreement.language.txt files.
if (!empty($context['languages']))
{
	foreach ($context['languages'] as $key => $language)
	{
		if (file_exists($boarddir . '/default-agreement.' . $language['filename'] . '.txt') == true)
		{
			// Delete it!
			unlink($boarddir . '/default-agreement.' . $language['filename'] . '.txt');
		}
	}
}

// And, we're done!
if (SMF == 'SSI')
	echo 'Database changes are complete!';