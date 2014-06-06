<?php
/**
 * User Agreement Update
 *
 * @file Handler.template.php
 * @author Labradoodle-360
 * @copyright Matthew Kerle 2012
 *
 * @version 1.0.3
 */

function template_main()
{

	// Our Global Variables.
	global $context, $txt, $scripturl, $settings, $agreement, $user_info;

	// "Welcome" Them.
	echo '
		<div class="windowbg2 spacer">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $txt['updated_agreement_notice'], '
			</div>
			<span class="botslice"><span></span></span>
		</div>
	';

	// Gender, or not?
	$image_name = 'document-text';
	if (isset($user_info['gender']) && $user_info['gender'] != 0)
	{
		switch ($user_info['gender'])
		{
			case 1:
				$image_name = 'user';
				break;
			case 2:
				$image_name = 'user-female';
				break;
		}
	}

	// It is time.
	echo '
		<div class="cat_bar" style="height: 31px;">
			<h3 class="catbg">
				<span class="floatleft lab_icon_medium">
					<img src="', $settings['images_url'], '/uau_images/', $image_name, '.png" alt="" />
				</span>
				', $txt['lab_user_agreement'], '
			</h3>
		</div>
		<div class="roundframe" style="margin-top: -4px;">
			<div class="inneframe">
				<div class="content">
					<div class="information">
						', $agreement, '
					</div>
					<form action="', $scripturl, !empty($_REQUEST['action']) ? '?action=' . $_REQUEST['action'] : '', '" method="post">
						<div id="which_input" class="centertext hidden">
							<label for="has_read">
								<strong>', $txt['lab_i_have'], '</strong>
							</label>
							<select name="has_read" id="has_read" class="custom_select">
								<optgroup label="Options:">
									<option value="0" selected="selected">', $txt['lab_not'], ' ', $txt['lab_read'], '</option>
									<option value="1">', $txt['lab_read'], '</option>
								</optgroup>
							</select>
							<label for="has_read">
								<strong>', $txt['the_user_agreement'], '</strong>
							</label>
						</div>
						<noscript>
							<div class="centertext">
								<input type="submit" value="', $txt['re_accept_agreement'], '" id="submit" name="submit" class="button_submit bloated_input" />
							</div>
						</noscript>
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</form>
				</div>
			</div>
		</div>
		<span class="lowerframe"><span></span></span>
	';

	// Oh SMF, what would you do without me?
	echo '<br class="clear" />';

}

function template_user_agreement_update()
{

	// Globalize all of our stuff.
	global $context, $settings, $scripturl, $txt, $modSettings, $membergroups;

	// Warning for if the file isn't writable.
	if (!empty($context['warning']))
		echo '<div id="profile_error" class="rounded">', $context['warning'], '</div>';

	// Let's be encouraging.
	echo '
		<div id="profile_success" class="hidden rounded">
			<span class="floatleft" style="width: 18px; margin-right: 2px; margin-top: 0px;">
				<img src="', $settings['images_url'], '/uau_images/tick-circle.png" alt="" />
			</span>
			', $txt['lab_saved_notice'], '
			<span class="floatright" style="width: 18px;">
				<img src="', $settings['images_url'], '/uau_images/cross.png" alt="', $txt['lab_close'], '" title="', $txt['lab_close'], '" id="close_success" style="cursor: pointer;" />
			</span>
		</div>
	';

	// Is there more than one language to choose from?
	if (count($context['editable_agreements']) > 1)
	{
		echo '
			<div class="spacer">
				<div class="cat_bar" style="height: 28px;">
					<h3 class="catbg" style="text-transform: capitalize;">
						<span class="floatleft lab_icon_medium">
							<img src="', $settings['images_url'], '/uau_images/globe-green.png" alt="" />
						</span>
						', $txt['admin_agreement_select_language'], '
					</h3>
				</div>
				<div class="windowbg rfix">
					<div class="content">
						<form action="', $scripturl, '?action=admin;area=regcenter" id="change_reg" method="post" accept-charset="', $context['character_set'], '">
							<div class="floatleft" style="width: 28%; margin-left: 2%;">
								<select name="agree_lang" id="agree_lang" class="custom_select" onchange="document.getElementById(\'change_reg\').submit();">
									<optgroup label="', $txt['lab_languages'], ':">';
									foreach ($context['editable_agreements'] as $file => $name)
									{
										echo '<option value="', $file, '" ', $context['current_agreement'] == $file ? 'selected="selected"' : '', '>', $name, '</option>';
									}
									echo '</optgroup>
								</select>
							</div>
							<div class="floatright" style="width: 70%; text-align: left;">
								<input type="submit" name="change" value="', $txt['admin_agreement_select_language_change'], '" class="button_submit bloated_input" />
							</div>
							<br class="clear" />
							<input type="hidden" name="sa" value="agreement" />
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						</form>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
		';
	}

	// Just a big box to edit the text file ;). Not quite...
	echo '
		<form action="', $scripturl, '?action=admin;area=regcenter;sa=agreement" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar" style="height: 28px;">
				<h3 class="catbg">
					<span class="floatleft lab_icon_medium">
						<img src="', $settings['images_url'], '/uau_images/eraser.png" alt="" />
					</span>
					', $txt['registration_agreement'], '
				</h3>
			</div>
			<div class="roundframe rfix">
				<div class="inneframe">
					<div class="content">
						<div class="floatleft" id="left_column" style="width: 100.7%;">
							<div class="information">
								<div class="floatleft" style="width: 112px; margin-top: 3px;">
									<label for="agreementBBC">
										<strong>', $txt['lab_parse_bbc'], ':</strong>
									</label>
								</div>
								<input type="checkbox" name="agreementBBC" id="agreementBBC"', $modSettings['agreementBBC'] ? ' checked="checked"' : '', ' value="1" class="input_check" />
							</div>
						</div>
						<div class="floatright hidden" id="right_column" style="text-align: left;">
							<div class="information">
								<div class="floatleft" style="width: 112px; margin-top: 3px;">
									<label for="agreementSmileys">
										<strong>', $txt['lab_show_smileys'], ':</strong>
									</label>
								</div>
								<input type="checkbox" name="agreementSmileys" id="agreementSmileys"', $modSettings['agreementSmileys'] ? ' checked="checked"' : '', ' value="1" class="input_check" />
							</div>
						</div>
						<br class="clear" />
						<textarea rows="20" name="agreement" id="agreement" style="width: 99%; margin-left: 1px; padding: 10px;">', isset($_POST['agreement']) ? $_POST['agreement'] : $context['agreement'], '</textarea>
						<div class="smalltext centertext">
							', $txt['lab_restore_to'], ':&nbsp;
							<span id="restore_latest_rev" class="fake_link">', $txt['lab_latest_revision'], '</span>
							&nbsp;|&nbsp;
							<span id="restore_original" class="fake_link">', $txt['lab_default_agreement'], '</span>
						</div>
					</div>
				</div>
			</div>
			<span class="lowerframe"><span></span></span>
			<div class="cat_bar upper_space" style="height: 28px;">
				<h3 class="catbg">
					<span class="floatleft lab_icon_medium">
						<img src="', $settings['images_url'], '/uau_images/equalizer.png" alt="" />
					</span>
					', $txt['lab_addit_settings'], '
				</h3>
			</div>
			<div class="roundframe" style="margin-top: -2px;">
				<div class="innerframe">
					<div class="content">
						<div class="floatleft" style="width: 30%;">
							<label for="requireAgreement">
								<span class="bold">', $txt['lab_setting_show_require'], ':</span>
							</label>
							<div class="tinytext">', $txt['lab_setting_desc_show_require'], '</div>
						</div>
						<div class="floatright" style="text-align: left; width: 70%;">
							<input type="checkbox" name="requireAgreement" id="requireAgreement"', $modSettings['requireAgreement'] ? ' checked="checked"' : '', ' value="1" class="input_check" />
						</div>
						<br class="clear" />
						<div id="required_mode_only">
							<hr />
							<div class="floatleft" style="width: 30%;">
								<label for="requireReagreement">
									<span class="bold">', $txt['lab_setting_require_reagreement'], ':</span>
								</label>
								<div class="tinytext">', $txt['lab_setting_desc_require_reagreement'], '</div>
							</div>
							<div class="floatright" style="text-align: left; width: 70%;">
								<div class="floatleft" style="width: 26x;">
									<input type="checkbox" name="requireReagreement" id="requireReagreement"', $modSettings['requireReagreement'] == true ? ' checked="checked"' : '', ' value="1" class="input_check" />
								</div>
								<div class="floatleft" style="margin-top: 2px; margin-left: 16px;">
									<span class="smalltext">', $txt['lab_last_reset'], ': ', $context['lab_last_reset'], '</span>
								</div>
								<br class="clear" />
							</div>
							<br class="clear" />
							<div id="member_dependent" class="hidden">
								<hr />
								<div class="floatleft" style="width: 30%;">
									<label for="userAgreementUpdateMode">
										<span class="bold">', $txt['lab_setting_member_mode'], ':</span>
									</label>
									<div class="tinytext">
										', $txt['lab_member_mode_strict'], '
										<br />
										', $txt['lab_member_mode_relaxed'], '
									</div>
								</div>
								<div class="floatright" style="text-align: left; width: 70%;">
									<select name="userAgreementUpdateMode" id="userAgreementUpdateMode" class="custom_select">
										<optgroup label="', $txt['lab_modes'], ':">
											<option value="strict"', $modSettings['userAgreementUpdateMode'] == 'strict' ? ' selected="selected"' : '', '>', $txt['lab_strict'], '</option>
											<option value="relaxed"', $modSettings['userAgreementUpdateMode'] == 'relaxed' ? ' selected="selected"' : '', '>', $txt['lab_relaxed'], '</option>
										</optgroup>
									</select>
								</div>
								<br class="clear" />
								<hr />
								<div class="floatleft" style="width: 30%;">
									<strong>', $txt['lab_setting_bypass_groups'], ':</strong>
									<div class="tinytext">', $txt['lab_setting_desc_bypass_groups'], '</div>
								</div>
								<div class="floatright" style="text-align: left; width: 70%;">
									<div id="collapse_membergroups" class="hidden fake_link">[', $txt['lab_collapse_mgroups'], ']</div>
									<div id="expand_membergroups" class="fake_link">[', $txt['lab_expand_mgroups'], ']</div>
									<div id="membergroups_group" class="hidden" style="margin-top: 15px;">
										<div class="floatleft" style="width: 50%;" id="primary_mgroups">
											<strong>', $txt['lab_primary'], '</strong>&nbsp;
											(<a href="#" class="smalltext" id="primary_mgroups_check">', $txt['lab_check'], '</a>
											&nbsp;/&nbsp;
											<a href="#" class="smalltext" id="primary_mgroups_uncheck">', $txt['lab_uncheck'], '</a>)
											<hr />';
											foreach ($membergroups['primary'] as $act => $membergroup)
											{
												echo '
													<div style="margin-bottom: -9px;">
														<div class="floatleft" style="width: 6%;">
															<input type="checkbox" value="', $act, '" class="input_check membergroup_primary" id="', $act, '" name="agreementMembergroups[]" />
														</div>
														<div class="floatright" style="width: 94%; margin-top: 1px; text-align: left;">
															<label for="', $act, '">
																<span', !empty($membergroup['color']) ? ' style="color: ' . $membergroup['color'] . ';"' : '', '">', $membergroup['name'], $txt['lab_plural_form'], '</span>
															</label>
														</div>
														<br class="clear" />
														<br />
													</div>
												';
											}
											echo '
										</div>
										<div class="floatright" style="width: 50%;" id="postbased_mgroups">
											<strong>', $txt['lab_post_based'], '</strong>&nbsp;
											(<a href="#" class="smalltext" id="postbased_mgroups_check">', $txt['lab_check'], '</a>
											&nbsp;/&nbsp;
											<a href="#" class="smalltext" id="postbased_mgroups_uncheck">', $txt['lab_uncheck'], '</a>)
											<hr />';
											foreach ($membergroups['post_based'] as $act => $membergroup)
											{
												echo '
													<div style="margin-bottom: -9px;">
														<div class="floatleft" style="width: 6%;">
															<input type="checkbox" value="', $act, '" class="input_check membergroup_postbased" id="', $act, '" name="agreementMembergroups[]" />
														</div>
														<div class="floatright" style="width: 94%; margin-top: 1px; text-align: left;">
															<label for="', $act, '">
																<span', !empty($membergroup['color']) ? ' style="color: ' . $membergroup['color'] . ';"' : '', '"><em>', $membergroup['name'], $txt['lab_plural_form'], '</em></span>
															</label>
														</div>
														<br class="clear" />
														<br />
													</div>
												';
											}
											echo '
										</div>
										<br class="clear" />
									</div>
								</div>
								<br class="clear" />
								<hr />
								<div class="floatleft" style="width: 30%;">
									<strong>', $txt['lab_setting_bypass_members'], ':</strong>
									<div class="tinytext">', $txt['lab_setting_desc_bypass_members'], '</div>
								</div>
								<div class="floatright" style="text-align: left; width: 70%;">
									<input type="text" id="bypass_members" name="bypass_members" class="input_text bloated_input" style="width: 25%;" />
									<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?fin20"></script>
									<script type="text/javascript"><!-- // --><![CDATA[
										var oBypassMembers = new smc_AutoSuggest({
											sSelf: \'oBypassMembers\',
											sSessionId: \'', $context['session_id'], '\',
											sSessionVar: \'', $context['session_var'], '\',
											sSuggestId: \'bypass_members\',
											sControlId: \'bypass_members\',
											sSearchType: \'member\',
											bItemList: true,
											sPostName: \'bypassed_members\',
											sURLMask: \'action=profile;u=%item_id%\',
											sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '" class="delete_item\',
											sItemListContainerId: \'members_container\',
											aListItems: []
										});
									// ]]></script>
									<div id="members_container"></div>
								</div>
								<br class="clear" />
							</div>
						</div>
					</div>
				</div>
			</div>
			<span class="lowerframe"><span></span></span>
			<input type="hidden" name="agree_lang" value="', $context['current_agreement'], '" />
			<input type="hidden" name="sa" value="agreement" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<div class="centertext upper_space">
				<input type="submit" value="', $txt['lab_save_settings'], '" class="button_submit bloated_input" />
			</div>
		</form>
		', $context['please_donate'], '
		<br class="clear" />
	';

}

function template_copyright_above()
{
}

function template_copyright_below()
{
	// Globals & Copyright
	global $txt;
	echo '
		<div class="centertext smalltext" style="margin-top: -10px;">
			', $txt['lab_copyright'], '
		</div>
	';
}