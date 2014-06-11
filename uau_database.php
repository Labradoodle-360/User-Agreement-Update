<?php
/**
 * User Agreement Update
 *
 * @file uau_database.php
 * @author Labradoodle-360
 * @copyright Matthew Kerle 2012 - 2014
 *
 * @version 1.0.4
 */
 
// Using SSI?
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<strong>Error:</strong> Cannot install - please make sure that this file in the same directory as SMF\'s SSI.php file.');

// We need packages...
if (SMF == 'SSI')
	db_extend('packages');

// Globalize what we need...
global $smcFunc, $context, $boarddir;

// Insert our settings.
$variables = array(
	'uau_agreementBBC' => '0',
	'uau_agreementSmileys' => '0',
	'uau_requireReagreement' => '0',
	'uau_userAgreementUpdateMode' => 'strict',
	'uau_lastUpdatedUA' => '',
);
updateSettings($variables, false, false);

// Our languages
getLanguages();

// Our column...
$column_info = array(
	'name' => 'has_agreed',
	'auto' => false,
	'type' => 'tinyint',
	'size' => 1,
	'default' => 1,
	'null' => false,
);

// Add it....
$smcFunc['db_add_column']('{db_prefix}members', $column_info, array(), 'update');

// Add our default-agreement.language.txt files.
if (!empty($context['languages']))
{
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

// And, we're done!
if (SMF == 'SSI')
	echo 'Database changes are complete!';