/**
 * User Agreement Update
 *
 * @file handler.js
 * @author Labradoodle-360
 * @copyright Matthew Kerle 2012-2014
 *
 * @version 1.0.4
 */

function uau_reaccept_agreement($, text) {
	$("#which_input").removeClass("hidden");
	$("#has_read").on("change", function() {
		if ($(this).val() == 1) {
			$("#which_input").html('<input type="submit" value="' + text + '" id="submit" name="submit" class="button_submit bloated_input" />');
		}
	});
}

function uau_admin_scripts($, confirm_latest, latest_agreement, confirm_default, default_agreement) {
	$("#restore_latest_rev").on("click", function(event) {
			event.preventDefault();
			if (confirm(confirm_latest)) {
				$("#agreement").val(latest_agreement);
			}
		});

	$("#restore_original").on("click", function(event) {
		event.preventDefault();
		if (confirm(confirm_default)) {
			$("#agreement").val(default_agreement);
		}
	});

	// If the registration agreement isn't required, don't show anything else.
	if ($("#requireAgreement").is(":checked")) {
		$("#required_mode_only").show();
	} else {
		$("#required_mode_only").addClass("hidden");
	}
	$("#requireAgreement").on("change", function() {
		if ($("#requireAgreement").is(":checked")) {
			$("#required_mode_only").slideDown("slow");
		} else {
			$("#required_mode_only").slideUp("slow");
		}
	});

	// If we don't require members to update, no point in the next two settings.
	if ($("#uau_requireReagreement").is(":checked")) {
		$("#member_dependent").removeClass("hidden");
	}
	$("#uau_requireReagreement").on("change", function() {
		if ($(this).is(":checked")) {
			$("#member_dependent").slideDown("slow");
		}
		else {
			$("#member_dependent").slideUp("slow");
		}
	});

	// Direct Agreement Options
	if ($("#uau_agreementBBC").is(":checked")) {
		/* Do nothing... */
	} else {
		$("#uau_agreementSmileys").prop("disabled", "disabled");
		$("#uau_agreementSmileys_label").addClass("lightgrey");
	}
	$("#uau_agreementBBC").on("change", function() {
		if ($(this).is(":checked")) {
			$("#uau_agreementSmileys").prop("disabled", "");
			$("#uau_agreementSmileys_label").removeClass("lightgrey");
		} else {
			$("#uau_agreementSmileys").prop("disabled", "disabled");
			$("#uau_agreementSmileys_label").addClass("lightgrey");
		}
	});

	// Check All / Uncheck All
	$("#primary_mgroups_check").on("click", function(event) {
		event.preventDefault();
		$("input.membergroup_primary").prop("checked", "checked");
	});
	$("#primary_mgroups_uncheck").on("click", function(event) {
		event.preventDefault();
		$("input.membergroup_primary").prop("checked", "");
	});
	$("#postbased_mgroups_check").on("click", function(event) {
		event.preventDefault();
		$("input.membergroup_postbased").prop("checked", "checked");
	});
	$("#postbased_mgroups_uncheck").on("click", function(event) {
		event.preventDefault();
		$("input.membergroup_postbased").prop("checked", "");
	});

	// Membergroups jQuery
	$("#expand_membergroups").on("click", function(event) {
		event.preventDefault();
		$("#membergroups_group").slideDown("slow");
		$("#collapse_membergroups").removeClass("hidden");
		$("#expand_membergroups").addClass("hidden");
	});
	$("#collapse_membergroups").on("click", function(event) {
		event.preventDefault();
		$("#membergroups_group").slideUp("slow");
		$("#expand_membergroups").removeClass("hidden");
		$("#collapse_membergroups").addClass("hidden");
	});
}

function uau_success_notification($) {
	$("#profile_success").delay(500).slideDown("slow");
	$("#close_success").click(function() {
		$("#profile_success").delay(500).slideUp("slow");
	});
}