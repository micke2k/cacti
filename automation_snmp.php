<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2016 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
 */

include('./include/auth.php');
include_once('./lib/snmp.php');

$automation_snmp_actions = array(
	1 => 'Delete',
	2 => 'Duplicate',
);

/* set default action */
set_default_action();

/* correct for a cancel button */
if (isset_request_var('cancel')) { 
	set_request_var('action', '');
}

switch (get_request_var('action')) {
	case 'save':
		form_automation_snmp_save();

		break;
	case 'actions':
		form_automation_snmp_actions();

		break;
	case 'item_movedown':
		get_filter_request_var('id');

		automation_snmp_item_movedown();

		header('Location: automation_snmp.php?action=edit&id=' . get_request_var('id'));
		break;
	case 'item_moveup':
		get_filter_request_var('id');

		automation_snmp_item_moveup();

		header('Location: automation_snmp.php?action=edit&id=' . get_request_var('id'));
		break;
	case 'item_remove':
		get_filter_request_var('id');

		automation_snmp_item_remove();

		header('Location: automation_snmp.php?header=false&action=edit&header=false&id=' . get_request_var('id'));
		break;
	case 'item_edit':
		top_header();

		automation_snmp_item_edit();

		bottom_footer();
		break;
	case 'edit':
		top_header();

		automation_snmp_edit();

		bottom_footer();
		break;
	default:
		top_header();

		automation_snmp();

		bottom_footer();
		break;
}

function form_automation_snmp_save() {

	if (isset_request_var('save_component_automation_snmp')) {
		/* ================= input validation ================= */
		get_filter_request_var('id');
		/* ==================================================== */

		$save['id']     = get_nfilter_request_var('id');
		$save['name']   = sql_sanitize(form_input_validate(get_nfilter_request_var('name'), 'name', '', false, 3));

		if (!is_error_message()) {
			$id = sql_save($save, 'automation_snmp');
			if ($id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		header('Location: automation_snmp.php?header=false&action=edit&id=' . (empty($id) ? get_nfilter_request_var('id') : $id));
	}elseif (isset_request_var('save_component_automation_snmp_item')) {
		/* ================= input validation ================= */
		get_filter_request_var('item_id');
		get_filter_request_var('id');
		/* ==================================================== */

		$save = array();
		$save['id']						= form_input_validate(get_nfilter_request_var('item_id'), '', '^[0-9]+$', false, 3);
		$save['snmp_id'] 				= form_input_validate(get_nfilter_request_var('id'), 'snmp_id', '^[0-9]+$', false, 3);
		$save['sequence'] 				= form_input_validate(get_nfilter_request_var('sequence'), 'sequence', '^[0-9]+$', false, 3);
		$save['snmp_readstring'] 		= form_input_validate(get_nfilter_request_var('snmp_readstring'), 'snmp_readstring', '', false, 3);
		$save['snmp_version'] 			= form_input_validate(get_nfilter_request_var('snmp_version'), 'snmp_version', '', false, 3);
		$save['snmp_username']			= form_input_validate(get_nfilter_request_var('snmp_username'), 'snmp_username', '', true, 3);
		$save['snmp_password']			= form_input_validate(get_nfilter_request_var('snmp_password'), 'snmp_password', '', true, 3);
		$save['snmp_auth_protocol']		= form_input_validate(get_nfilter_request_var('snmp_auth_protocol'), 'snmp_auth_protocol', '', true, 3);
		$save['snmp_priv_passphrase']	= form_input_validate(get_nfilter_request_var('snmp_priv_passphrase'), 'snmp_priv_passphrase', '', true, 3);
		$save['snmp_priv_protocol']		= form_input_validate(get_nfilter_request_var('snmp_priv_protocol'), 'snmp_priv_protocol', '', true, 3);
		$save['snmp_context']			= form_input_validate(get_nfilter_request_var('snmp_context'), 'snmp_context', '', true, 3);
		$save['snmp_port']				= form_input_validate(get_nfilter_request_var('snmp_port'), 'snmp_port', '^[0-9]+$', false, 3);
		$save['snmp_timeout']			= form_input_validate(get_nfilter_request_var('snmp_timeout'), 'snmp_timeout', '^[0-9]+$', false, 3);
		$save['snmp_retries']			= form_input_validate(get_nfilter_request_var('snmp_retries'), 'snmp_retries', '^[0-9]+$', false, 3);
		$save['max_oids']				= form_input_validate(get_nfilter_request_var('max_oids'), 'max_oids', '^[0-9]+$', false, 3);

		if (!is_error_message()) {
			$item_id = sql_save($save, 'automation_snmp_items');

			if ($item_id) {
				raise_message(1);
			}else{
				raise_message(2);
			}
		}

		if (is_error_message()) {
			header('Location: automation_snmp.php?header=false&action=item_edit&id=' . get_nfilter_request_var('id') . '&item_id=' . (empty($item_id) ? get_filter_request_var('id') : $item_id));
		}else{
			header('Location: automation_snmp.php?header=false&action=edit&id=' . get_nfilter_request_var('id'));
		}
	} else {
		raise_message(2);
		header('Location: automation_snmp.php?header=false');
	}
}


/* ------------------------
 The 'actions' function
 ------------------------ */
function form_automation_snmp_actions() {
	global $config, $automation_snmp_actions;

	/* ================= input validation ================= */
	get_filter_request_var('drp_action');
	/* ==================================================== */

	/* if we are to save this form, instead of display it */
	if (isset_request_var('selected_items')) {
		$selected_items = sanitize_unserialize_selected_items(get_nfilter_request_var('selected_items'));

		if ($selected_items != false) {
			if (get_nfilter_request_var('drp_action') == '1') { /* delete */
				db_execute('DELETE FROM automation_snmp WHERE ' . array_to_sql_or($selected_items, 'id'));
				db_execute('DELETE FROM automation_snmp_items WHERE ' . str_replace('id', 'snmp_id', array_to_sql_or($selected_items, 'id')));
			}elseif (get_nfilter_request_var('drp_action') == '2') { /* duplicate */
				for ($i=0;($i<count($selected_items));$i++) {
					duplicate_mactrack($selected_items[$i], get_nfilter_request_var('name_format'));
				}
			}
		}

		header('Location: automation_snmp.php?header=false');
		exit;
	}

	/* setup some variables */
	$snmp_groups = ''; $i = 0;
	/* loop through each of the graphs selected on the previous page and get more info about them */
	while (list($var,$val) = each($_POST)) {
		if (preg_match('/^chk_([0-9]+)$/', $var, $matches)) {
			/* ================= input validation ================= */
			input_validate_input_number($matches[1]);
			/* ==================================================== */
			$snmp_groups .= '<li>' . db_fetch_cell('SELECT name FROM automation_snmp WHERE id=' . $matches[1]) . '</li>';
			$automation_array[$i] = $matches[1];
			$i++;
		}
	}

	general_header();

	display_output_messages();

	?>
	<script type='text/javascript'>
	function goTo(location) {
		document.location = location;
	}
	</script>
	<?php

	form_start('automation_snmp.php', 'automation_filter');

	html_start_box($automation_snmp_actions{get_nfilter_request_var('drp_action')}, '60%', '', '3', 'center', '');

	if (!isset($automation_array)) {
		print "<tr><td class='even'><span class='textError'>You must select at least one SNMP Option.</span></td></tr>\n";
		$save_html = '';
	}else{
		$save_html = "<input type='submit' value='Continue' name='save'>";

		if (get_nfilter_request_var('drp_action') == '1') { /* delete */
			print "<tr>
				<td class='textArea'>
					<p>Click 'Continue' to delete the following SNMP Option(s).</p>
					<p><ul>$snmp_groups</ul></p>
				</td>
			</tr>\n";
		}elseif (get_nfilter_request_var('drp_action') == '2') { /* duplicate */
			print "<tr>
				<td class='textArea'>
					<p>Click 'Continue' to duplicate the following SNMP Options. You can
					optionally change the title format for the new SNMP Options.</p>
					<p><ul>$snmp_groups</ul></p>
					<p>Name Format:<br>"; form_text_box('name_format', '<name> (1)', '', '255', '30', 'text'); print "</p>
				</td>
			</tr>\n";
		}
	}

	print "	<tr>
		<td class='saveRow'>
			<input type='hidden' name='action' value='actions'>
			<input type='hidden' name='selected_items' value='" . (isset($automation_array) ? serialize($automation_array) : '') . "'>
			<input type='hidden' name='drp_action' value='" . get_nfilter_request_var('drp_action') . "'>
			<input type='button' onClick='goTo(\"" . "automation_snmp.php" . "\")' value='" . ($save_html == '' ? 'Return':'Cancel') . "' name='cancel'>
			$save_html
		</td>
	</tr>\n";

	html_end_box();

	form_end();

	bottom_footer();
}

/* --------------------------
 mactrack Item Functions
 -------------------------- */
function automation_snmp_item_movedown() {
	/* ================= input validation ================= */
	get_filter_request_var('item_id');
	get_filter_request_var('id');
	/* ==================================================== */
	move_item_down('automation_snmp_items', get_request_var('item_id'), 'snmp_id=' . get_request_var('id'));
}

function automation_snmp_item_moveup() {
	/* ================= input validation ================= */
	get_filter_request_var('item_id');
	get_filter_request_var('id');
	/* ==================================================== */
	move_item_up('automation_snmp_items', get_request_var('item_id'), 'snmp_id=' . get_request_var('id'));
}

function automation_snmp_item_remove() {
	/* ================= input validation ================= */
	get_filter_request_var('item_id');
	/* ==================================================== */
	db_execute('delete from automation_snmp_items where id=' . get_request_var('item_id'));
}

function automation_snmp_item_edit() {
	global $config, $snmp_auth_protocols, $snmp_priv_protocols, $snmp_versions;

	#include_once($config['base_path'].'/plugins/mactrack/lib/automation_functions.php');

	/* ================= input validation ================= */
	get_filter_request_var('id');
	get_filter_request_var('item_id');
	/* ==================================================== */

	# fetch the current mactrack snmp record
	$snmp_option = db_fetch_row('SELECT * 
		FROM automation_snmp 
		WHERE id=' . get_request_var('id'));

	# if an existing item was requested, fetch data for it
	if (get_request_var('item_id', '') !== '') {
		$automation_snmp_item = db_fetch_row('SELECT * 
			FROM automation_snmp_items 
			WHERE id=' . get_request_var('item_id'));

		$header_label = '[edit: ' . $snmp_option['name'] . ']';
	}else{
		$header_label = '[new]';
		$automation_snmp_item = array();
		$automation_snmp_item['snmp_id'] = get_request_var('id');
		$automation_snmp_item['sequence'] = get_sequence('', 'sequence', 'automation_snmp_items', 'snmp_id=' . get_request_var('id'));
	}

	form_start('automation_snmp.php', 'automation_item_edit');

	html_start_box("SNMP Options $header_label", '100%', '', '3', 'center', '');

	/* this is snmp we are talking about here */
	unset($snmp_versions[0]);

	$fields_automation_snmp_item = array(
	'snmp_version' => array(
		'method' => 'drop_array',
		'friendly_name' => 'SNMP Version',
		'description' => 'Choose the SNMP version for this host.',
		'on_change' => 'changeSNMPVersion()',
		'value' => '|arg1:snmp_version|',
		'default' => read_config_option('snmp_ver'),
		'array' => $snmp_versions
		),
	'snmp_readstring' => array(
		'method' => 'textbox',
		'friendly_name' => 'SNMP Community String',
		'description' => 'Fill in the SNMP read community for this device.',
		'value' => '|arg1:snmp_readstring|',
		'default' => read_config_option('snmp_community'),
		'max_length' => '100',
		'size' => '20'
		),
	'snmp_port' => array(
		'method' => 'textbox',
		'friendly_name' => 'SNMP Port',
		'description' => 'The UDP/TCP Port to poll the SNMP agent on.',
		'value' => '|arg1:snmp_port|',
		'max_length' => '8',
		'default' => read_config_option('snmp_port'),
		'size' => '10'
		),
	'snmp_timeout' => array(
		'method' => 'textbox',
		'friendly_name' => 'SNMP Timeout',
		'description' => 'The maximum number of milliseconds Cacti will wait for an SNMP response (does not work with php-snmp support).',
		'value' => '|arg1:snmp_timeout|',
		'max_length' => '8',
		'default' => read_config_option('snmp_timeout'),
		'size' => '10'
		),
	'snmp_retries' => array(
		'method' => 'textbox',
		'friendly_name' => 'SNMP Retries',
		'description' => 'The maximum number of attempts to reach a device via an SNMP readstring prior to giving up.',
		'value' => '|arg1:snmp_retries|',
		'max_length' => '8',
		'default' => read_config_option('snmp_retries'),
		'size' => '10'
		),
	'max_oids' => array(
		'method' => 'textbox',
		'friendly_name' => "Maximum OID's Per Get Request",
		'description' => 'Specified the number of OIDs that can be obtained in a single SNMP Get request.',
		'value' => '|arg1:max_oids|',
		'max_length' => '8',
		'default' => read_config_option('max_get_size'),
		'size' => '15'
		),
	'snmp_username' => array(
		'method' => 'textbox',
		'friendly_name' => 'SNMP Username (v3)',
		'description' => 'SNMP v3 username for this device.',
		'value' => '|arg1:snmp_username|',
		'default' => read_config_option('snmp_username'),
		'max_length' => '50',
		'size' => '15'
		),
	'snmp_password' => array(
		'method' => 'textbox_password',
		'friendly_name' => 'SNMP Password (v3)',
		'description' => 'SNMP v3 password for this device.',
		'value' => '|arg1:snmp_password|',
		'default' => read_config_option('snmp_password'),
		'max_length' => '50',
		'size' => '15'
		),
	'snmp_auth_protocol' => array(
		'method' => 'drop_array',
		'friendly_name' => 'SNMP Auth Protocol (v3)',
		'description' => 'Choose the SNMPv3 Authorization Protocol.',
		'value' => '|arg1:snmp_auth_protocol|',
		'default' => read_config_option('snmp_auth_protocol'),
		'array' => $snmp_auth_protocols,
		),
	'snmp_priv_passphrase' => array(
		'method' => 'textbox',
		'friendly_name' => 'SNMP Privacy Passphrase (v3)',
		'description' => 'Choose the SNMPv3 Privacy Passphrase.',
		'value' => '|arg1:snmp_priv_passphrase|',
		'default' => read_config_option('snmp_priv_passphrase'),
		'max_length' => '200',
		'size' => '40'
		),
	'snmp_priv_protocol' => array(
		'method' => 'drop_array',
		'friendly_name' => 'SNMP Privacy Protocol (v3)',
		'description' => 'Choose the SNMPv3 Privacy Protocol.',
		'value' => '|arg1:snmp_priv_protocol|',
		'default' => read_config_option('snmp_priv_protocol'),
		'array' => $snmp_priv_protocols,
		),
	'snmp_context' => array(
		'method' => 'textbox',
		'friendly_name' => 'SNMP Context',
		'description' => 'Enter the SNMP Context to use for this device.',
		'value' => '|arg1:snmp_context|',
		'default' => '',
		'max_length' => '64',
		'size' => '25'
		),
	);

    /* file: mactrack_snmp.php, action: item_edit */
	$fields_automation_snmp_item_edit = $fields_automation_snmp_item + array(
	'sequence' => array(
		'method' => 'view',
		'friendly_name' => 'Sequence',
		'description' => 'Sequence of Item.',
		'value' => '|arg1:sequence|'),
	);

	draw_edit_form(array(
		'config' => array('no_form_tag' => true),
		'fields' => inject_form_variables($fields_automation_snmp_item_edit, (isset($automation_snmp_item) ? $automation_snmp_item : array()))
	));

	html_end_box();
	form_hidden_box('item_id', (isset_request_var('item_id') ? get_request_var('item_id') : '0'), '');
	form_hidden_box('id', (isset($automation_snmp_item['snmp_id']) ? $automation_snmp_item['snmp_id'] : '0'), '');
	form_hidden_box('save_component_automation_snmp_item', '1', '');

	form_save_button(htmlspecialchars('automation_snmp.php?action=edit&id=' . get_request_var('id')));

	?>
	<script type='text/javascript'>
	function changeSNMPVersion() {
		console.log('Here');
		version = parseInt($('#snmp_version').val());
		switch (version) {
		case 0:
			$('#row_snmp_username').hide();
			$('#row_snmp_password').hide();
			$('#row_snmp_readstring').hide();
			$('#row_snmp_auth_protocol').hide();
			$('#row_snmp_priv_passphrase').hide();
			$('#row_snmp_priv_protocol').hide();
			$('#row_snmp_context').hide();
			$('#row_snmp_port').hide();
			$('#row_snmp_timeout').hide();
			$('#row_snmp_retries').hide();
			$('#row_max_oids').hide();

			break;
		case 1:
		case 2:
			$('#row_snmp_username').hide();
			$('#row_snmp_password').hide();
			$('#row_snmp_readstring').show();
			$('#row_snmp_auth_protocol').hide();
			$('#row_snmp_priv_passphrase').hide();
			$('#row_snmp_priv_protocol').hide();
			$('#row_snmp_context').hide();
			$('#row_snmp_port').show();
			$('#row_snmp_timeout').show();
			$('#row_snmp_retries').show();
			$('#row_max_oids').show();

			break;
		case 3:
			$('#row_snmp_username').show();
			$('#row_snmp_password').show();
			$('#row_snmp_readstring').hide();
			$('#row_snmp_auth_protocol').show();
			$('#row_snmp_priv_passphrase').show();
			$('#row_snmp_priv_protocol').show();
			$('#row_snmp_context').show();
			$('#row_snmp_port').show();
			$('#row_snmp_timeout').show();
			$('#row_snmp_retries').show();
			$('#row_max_oids').show();

			break;
		}
	}

	$(function() {
		changeSNMPVersion();
	});
	</script>
	<?php
}

function automation_snmp_edit() {
	global $config, $fields_automation_snmp_edit;

	/* ================= input validation ================= */
	get_filter_request_var('id');
	/* ==================================================== */

	/* display the mactrack snmp option set */
	$snmp_group = array();
	if (!isempty_request_var('id')) {
		$snmp_group = db_fetch_row('SELECT * FROM automation_snmp where id=' . get_request_var('id'));
		# setup header
		$header_label = '[edit: ' . $snmp_group['name'] . ']';
	}else{
		$header_label = '[new]';
	}

	form_start('automation_snmp.php', 'automation_snmp_group');

	html_start_box("SNMP Option Set $header_label", '100%', '', '3', 'center', '');

    /* file: automation_snmp.php, action: edit */
	$fields_automation_snmp_edit = array(
		'name' => array(
			'method' => 'textbox',
			'friendly_name' => 'Name',
			'description' => 'Fill in the name of this SNMP Option Set.',
			'value' => '|arg1:name|',
			'default' => '',
			'max_length' => '100',
			'size' => '40'
		)
    );


	draw_edit_form(array(
		'config' => array('no_form_tag' => true),
		'fields' => inject_form_variables($fields_automation_snmp_edit, $snmp_group)
	));

	html_end_box();
	form_hidden_box('id', (isset_request_var('id') ? get_request_var('id'): '0'), '');
	form_hidden_box('save_component_automation_snmp', '1', '');

	if (!isempty_request_var('id')) {
		$items = db_fetch_assoc('SELECT * 
			FROM automation_snmp_items
			WHERE snmp_id=' . get_request_var('id') . '
			ORDER BY sequence');

		html_start_box('Automation SNMP Options', '100%', '', '3', 'center', 'automation_snmp.php?action=item_edit&id=' . get_request_var('id'));

		print "<tr class='tableHeader'>";

		$display_text = array(
			array('display' => 'Item', 'align' => 'left'),
			array('display' => 'Version', 'align' => 'left'),
			array('display' => 'Community', 'align' => 'left'),
			array('display' => 'Port', 'align' => 'right'),
			array('display' => 'Timeout', 'align' => 'right'),
			array('display' => 'Retries', 'align' => 'right'),
			array('display' => 'Max OIDS', 'align' => 'right'),
			array('display' => 'Auth Username', 'align' => 'left'),
			array('display' => 'Auth Password', 'align' => 'left'),
			array('display' => 'Auth Protocol', 'align' => 'left'),
			array('display' => 'Priv Passphrase', 'align' => 'left'),
			array('display' => 'Priv Protocol', 'align' => 'left'),
			array('display' => 'Context', 'align' => 'left'),
			array('display' => 'Action', 'align' => 'right')
		);

		html_header($display_text, 2);

		print '</tr>';

		$i = 1;
		if (sizeof($items)) {
			$total_items = sizeof($items);

			foreach ($items as $item) {
				form_alternate_row();
				$form_data = "<td><a class='linkEditMain' href='" . htmlspecialchars('automation_snmp.php?action=item_edit&item_id=' . $item['id'] . '&id=' . $item['snmp_id']) . "'>Item#" . $i . '</a></td>';
				#$form_data .= '<td>' . 	$item['sequence'] . '</td>';
				$form_data .= '<td>' . 	$item['snmp_version'] . '</td>';
				$form_data .= '<td class="left">' . 	($item['snmp_version'] == 3 ? 'none' : $item['snmp_readstring']) . '</td>';
				$form_data .= '<td class="right">' . 	$item['snmp_port'] . '</td>';
				$form_data .= '<td class="right">' . 	$item['snmp_timeout'] . '</td>';
				$form_data .= '<td class="right">' . 	$item['snmp_retries'] . '</td>';
				$form_data .= '<td class="right">' . 	$item['max_oids'] . '</td>';
				$form_data .= '<td>' . 	($item['snmp_version'] == 3 ? $item['snmp_username'] : 'N/A') . '</td>';
				$form_data .= '<td>' . 	(($item['snmp_version'] == 3 AND $item['snmp_password'] !== '') ? '*********' : 'N/A') . '</td>';
				$form_data .= '<td>' . 	($item['snmp_version'] == 3 ? $item['snmp_auth_protocol'] : 'N/A') . '</td>';
				$form_data .= '<td>' . 	($item['snmp_version'] == 3 ? $item['snmp_priv_passphrase'] : 'N/A') . '</td>';
				$form_data .= '<td>' . 	($item['snmp_version'] == 3 ? $item['snmp_priv_protocol'] : 'N/A') . '</td>';
				$form_data .= '<td>' . 	($item['snmp_version'] == 3 ? $item['snmp_context'] : 'N/A') . '</td>';

				$form_data .= '<td class="nowrap right">';
				if ($i < $total_items && $total_items > 1) {
					$form_data .= '<a class="pic fa fa-arrow-down moveArrow" href="' . htmlspecialchars('automation_snmp.php?action=item_movedown&item_id=' . $item['id'] . '&id=' . $item['snmp_id']) . '" title="Move Down"></a>';
				}else{
					$form_data .= '<span class="moveArrowNone"></span>';
				}

				if ($i > 1 && $i <= $total_items) {
					$form_data .= '<a class="pic fa fa-arrow-up moveArrow" href="' . htmlspecialchars('automation_snmp.php?action=item_moveup&item_id=' . $item['id'] .	'&id=' . $item['snmp_id']) . '" title="Move Up"></a>';
				}else{
					$form_data .= '<span class="moveArrowNone"></span>';
				}
				$form_data .= '</td>';

				$form_data .= '<td class="nowrap right">';
				$form_data .= '<a class="pic deleteMarker fa fa-remove" href="' . htmlspecialchars('automation_snmp.php?action=item_remove&item_id=' . $item['id'] .	'&id=' . $item['snmp_id']) . '" title="Delete"></a>';
				$form_data .= '</td></tr>';

				print $form_data;

				$i++;
			}
		} else {
			print "<tr><td><em>No SNMP Items</em></td></tr>\n";
		}

		html_end_box();
	}

	form_save_button('automation_snmp.php', 'return');

    ?>
    <script type='text/javascript'>
    $(function() {
        $('img.action').click(function() {
            strURL = $(this).attr('href');
			loadPageNoHeader(strURL);
        });
    });
    </script>
    <?php
}

function automation_snmp() {
	global $config, $item_rows, $automation_snmp_actions;

	/* ================= input validation and session storage ================= */
	$filters = array(
		'rows' => array(
			'filter' => FILTER_VALIDATE_INT, 
			'pageset' => true,
			'default' => '-1'
			),
		'page' => array(
			'filter' => FILTER_VALIDATE_INT, 
			'default' => '1'
			),
		'filter' => array(
			'filter' => FILTER_CALLBACK, 
			'pageset' => true,
			'default' => '', 
			'options' => array('options' => 'sanitize_search_string')
			),
		'sort_column' => array(
			'filter' => FILTER_CALLBACK, 
			'default' => 'name', 
			'options' => array('options' => 'sanitize_search_string')
			),
		'sort_direction' => array(
			'filter' => FILTER_CALLBACK, 
			'default' => 'ASC', 
			'options' => array('options' => 'sanitize_search_string')
			)
	);

	validate_store_request_vars($filters, 'sess_autom_snmp');

	if (get_request_var('rows') == -1) {
		$rows = read_config_option('num_rows_table');
	}else{
		$rows = get_request_var('rows');
	}

	form_start('automation_snmp.php', 'automation_snmp');

	html_start_box('Automation SNMP Options', '100%', '', '3', 'center', 'automation_snmp.php?action=edit');

	?>
	<tr class='even'>
		<td>
			<table class='filterTable'>
				<tr>
					<td>
						Search
					</td>
					<td>
						<input type='text' id='filter' size='25' value='<?php print get_request_var('filter');?>'>
					</td>
					<td>
						SNMP Rules
					</td>
                    <td>
                        <select id='rows' onChange='applyFilter()'>
							<option value='-1'<?php print (get_request_var('rows') == '-1' ? ' selected>':'>') . __('Default');?>
                            <?php
                            if (sizeof($item_rows)) {
                                foreach ($item_rows as $key => $value) {
                                    print "<option value='" . $key . "'"; if (get_request_var('rows') == $key) { print ' selected'; } print '>' . htmlspecialchars($value) . "</option>\n";
                                }
                            }
                            ?>
                        </select>
                    </td>
					<td>
						<input id='refresh' type='button' value='Go' title='Set/Refresh Filters'>
					</td>
					<td>
						<input id='clear' type='button' value='Clear' title='Clear Filters'>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<input type='hidden' name='page' value='<?php print get_request_var('page');?>'>
		</td>
	</tr>
	<script type='text/javascript'>
	</script>
	<?php

	html_end_box();

	form_end();

	/* form the 'where' clause for our main sql query */
	if (strlen(get_request_var('filter'))) {
		$sql_where = "WHERE (automation_snmp.name LIKE '%" . get_request_var('filter') . "%')";
	}else{
		$sql_where = '';
	}

	html_start_box('', '100%', '', '3', 'center', '');

	$total_rows = db_fetch_cell("SELECT
		COUNT(asnmp.id)
		FROM automation_snmp AS asnmp
		LEFT JOIN automation_networks AS anw
		ON asnmp.id=anw.snmp_id
		LEFT JOIN automation_snmp_items AS asnmpi
		ON asnmp.id=asnmpi.snmp_id
		GROUP BY asnmp.id
		$sql_where");

	$snmp_groups = db_fetch_assoc("SELECT asnmp.*, COUNT(anw.id) AS networks,
		COUNT(asnmpi.snmp_id) AS totals,
		SUM(CASE WHEN asnmpi.snmp_version=1 THEN 1 ELSE 0 END) AS v1entries,
		SUM(CASE WHEN asnmpi.snmp_version=2 THEN 1 ELSE 0 END) AS v2entries,
		SUM(CASE WHEN asnmpi.snmp_version=3 THEN 1 ELSE 0 END) AS v3entries
		FROM automation_snmp AS asnmp
		LEFT JOIN automation_networks AS anw
		ON asnmp.id=anw.snmp_id
		LEFT JOIN automation_snmp_items AS asnmpi
		ON asnmp.id=asnmpi.snmp_id
		GROUP BY asnmp.id
		$sql_where
		ORDER BY " . get_request_var('sort_column') . ' ' . get_request_var('sort_direction') . '
		LIMIT ' . ($rows*(get_request_var('page')-1)) . ',' . $rows);

	$nav = html_nav_bar('automation_snmp.php?filter=' . get_request_var('filter'), MAX_DISPLAY_PAGES, get_request_var('page'), $rows, $total_rows, 12, 'SNMP Option Sets');

	print $nav;

	$display_text = array(
		'name' => array('display' => 'SNMP Option Set', 'align' => 'left', 'sort' => 'ASC'),
		'networks' => array('display' => 'Networks Using', 'align' => 'right', 'sort' => 'DESC'),
		'totals' => array('display' => 'SNMP Entries', 'align' => 'right', 'sort' => 'DESC'),
		'v1entries' => array('display' => 'V1 Entries', 'align' => 'right', 'sort' => 'DESC'),
		'v2entries' => array('display' => 'V2 Entries', 'align' => 'right', 'sort' => 'DESC'),
		'v3entries' => array('display' => 'V3 Entries', 'align' => 'right', 'sort' => 'DESC')
	);

	html_header_sort_checkbox($display_text, get_request_var('sort_column'), get_request_var('sort_direction'));

	if (sizeof($snmp_groups) > 0) {
		foreach ($snmp_groups as $snmp_group) {
			form_alternate_row('line' . $snmp_group['id'], true);

			form_selectable_cell("<a class='linkEditMain' href='" . htmlspecialchars('automation_snmp.php?action=edit&id=' . $snmp_group['id'] . '&page=1') . "'>" . ((get_request_var('filter') != '') ? preg_replace('/(' . preg_quote(get_request_var('filter')) . ')/i', "<span class='filteredValue'>\\1</span>", htmlspecialchars($snmp_group['name'])) : htmlspecialchars($snmp_group['name'])) . '</a>', $snmp_group['id']);
			form_selectable_cell($snmp_group['networks'], $snmp_group['id'], '', 'text-align:right;');
			form_selectable_cell($snmp_group['totals'], $snmp_group['id'], '', 'text-align:right;');
			form_selectable_cell($snmp_group['v1entries'], $snmp_group['id'], '', 'text-align:right;');
			form_selectable_cell($snmp_group['v2entries'], $snmp_group['id'], '', 'text-align:right;');
			form_selectable_cell($snmp_group['v3entries'], $snmp_group['id'], '', 'text-align:right;');
			form_checkbox_cell($snmp_group['name'], $snmp_group['id']);

			form_end_row();
		}
	}else{
		print "<tr><td><em>No SNMP Option Sets Found</em></td></tr>\n";
	}
	print $nav;

	html_end_box(false);

	/* draw the dropdown containing a list of available actions for this form */
	draw_actions_dropdown($automation_snmp_actions);

	form_end();

	?>
	<script type='text/javascript'>
	function applyFilter() {
		strURL  = 'automation_snmp.php?header=false&rows=' + $('#rows').val();
		strURL += strURL + '&filter=' + $('#filter').val();
		loadPageNoHeader(strURL);
	}
	</script>
	<?php
}

?>
