<?php

// Data functions (insert, update, delete, form) for table item_prices

// This script and data application were generated by AppGini 22.12
// Download AppGini for free from https://bigprof.com/appgini/download/

function item_prices_insert(&$error_message = '') {
	global $Translation;

	// mm: can member insert record?
	$arrPerm = getTablePermissions('item_prices');
	if(!$arrPerm['insert']) return false;

	$data = [
		'item' => Request::lookup('item', ''),
		'price' => Request::val('price', '0.00'),
		'date' => Request::dateComponents('date', '1'),
	];


	// hook: item_prices_before_insert
	if(function_exists('item_prices_before_insert')) {
		$args = [];
		if(!item_prices_before_insert($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$error = '';
	// set empty fields to NULL
	$data = array_map(function($v) { return ($v === '' ? NULL : $v); }, $data);
	insert('item_prices', backtick_keys_once($data), $error);
	if($error)
		die("{$error}<br><a href=\"#\" onclick=\"history.go(-1);\">{$Translation['< back']}</a>");

	$recID = db_insert_id(db_link());

	update_calc_fields('item_prices', $recID, calculated_fields()['item_prices']);

	// hook: item_prices_after_insert
	if(function_exists('item_prices_after_insert')) {
		$res = sql("SELECT * FROM `item_prices` WHERE `id`='" . makeSafe($recID, false) . "' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)) {
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = makeSafe($recID, false);
		$args=[];
		if(!item_prices_after_insert($data, getMemberInfo(), $args)) { return $recID; }
	}

	// mm: save ownership data
	set_record_owner('item_prices', $recID, getLoggedMemberID());

	// if this record is a copy of another record, copy children if applicable
	if(strlen(Request::val('SelectedID'))) item_prices_copy_children($recID, Request::val('SelectedID'));

	return $recID;
}

function item_prices_copy_children($destination_id, $source_id) {
	global $Translation;
	$requests = []; // array of curl handlers for launching insert requests
	$eo = ['silentErrors' => true];
	$safe_sid = makeSafe($source_id);

	// launch requests, asynchronously
	curl_batch($requests);
}

function item_prices_delete($selected_id, $AllowDeleteOfParents = false, $skipChecks = false) {
	// insure referential integrity ...
	global $Translation;
	$selected_id = makeSafe($selected_id);

	// mm: can member delete record?
	if(!check_record_permission('item_prices', $selected_id, 'delete')) {
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: item_prices_before_delete
	if(function_exists('item_prices_before_delete')) {
		$args = [];
		if(!item_prices_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'] . (
				!empty($args['error_message']) ?
					'<div class="text-bold">' . strip_tags($args['error_message']) . '</div>'
					: '' 
			);
	}

	sql("DELETE FROM `item_prices` WHERE `id`='{$selected_id}'", $eo);

	// hook: item_prices_after_delete
	if(function_exists('item_prices_after_delete')) {
		$args = [];
		item_prices_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("DELETE FROM `membership_userrecords` WHERE `tableName`='item_prices' AND `pkValue`='{$selected_id}'", $eo);
}

function item_prices_update(&$selected_id, &$error_message = '') {
	global $Translation;

	// mm: can member edit record?
	if(!check_record_permission('item_prices', $selected_id, 'edit')) return false;

	$data = [
		'item' => Request::lookup('item', ''),
		'price' => Request::val('price', ''),
		'date' => Request::dateComponents('date', ''),
	];

	// get existing values
	$old_data = getRecord('item_prices', $selected_id);
	if(is_array($old_data)) {
		$old_data = array_map('makeSafe', $old_data);
		$old_data['selectedID'] = makeSafe($selected_id);
	}

	$data['selectedID'] = makeSafe($selected_id);

	// hook: item_prices_before_update
	if(function_exists('item_prices_before_update')) {
		$args = ['old_data' => $old_data];
		if(!item_prices_before_update($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$set = $data; unset($set['selectedID']);
	foreach ($set as $field => $value) {
		$set[$field] = ($value !== '' && $value !== NULL) ? $value : NULL;
	}

	if(!update(
		'item_prices', 
		backtick_keys_once($set), 
		['`id`' => $selected_id], 
		$error_message
	)) {
		echo $error_message;
		echo '<a href="item_prices_view.php?SelectedID=' . urlencode($selected_id) . "\">{$Translation['< back']}</a>";
		exit;
	}


	$eo = ['silentErrors' => true];

	update_calc_fields('item_prices', $data['selectedID'], calculated_fields()['item_prices']);

	// hook: item_prices_after_update
	if(function_exists('item_prices_after_update')) {
		$res = sql("SELECT * FROM `item_prices` WHERE `id`='{$data['selectedID']}' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)) $data = array_map('makeSafe', $row);

		$data['selectedID'] = $data['id'];
		$args = ['old_data' => $old_data];
		if(!item_prices_after_update($data, getMemberInfo(), $args)) return;
	}

	// mm: update ownership data
	sql("UPDATE `membership_userrecords` SET `dateUpdated`='" . time() . "' WHERE `tableName`='item_prices' AND `pkValue`='" . makeSafe($selected_id) . "'", $eo);
}

function item_prices_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $separateDV = 0, $TemplateDV = '', $TemplateDVP = '') {
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;
	$eo = ['silentErrors' => true];
	$noUploads = null;
	$row = $urow = $jsReadOnly = $jsEditable = $lookups = null;

	// mm: get table permissions
	$arrPerm = getTablePermissions('item_prices');
	if(!$arrPerm['insert'] && $selected_id == '')
		// no insert permission and no record selected
		// so show access denied error unless TVDV
		return $separateDV ? $Translation['tableAccessDenied'] : '';
	$AllowInsert = ($arrPerm['insert'] ? true : false);
	// print preview?
	$dvprint = false;
	if(strlen($selected_id) && Request::val('dvprint_x') != '') {
		$dvprint = true;
	}

	$filterer_item = Request::val('filterer_item');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: item
	$combo_item = new DataCombo;
	// combobox: date
	$combo_date = new DateCombo;
	$combo_date->DateFormat = "dmy";
	$combo_date->MinYear = defined('item_prices.date.MinYear') ? constant('item_prices.date.MinYear') : 1900;
	$combo_date->MaxYear = defined('item_prices.date.MaxYear') ? constant('item_prices.date.MaxYear') : 2100;
	$combo_date->DefaultDate = parseMySQLDate('1', '1');
	$combo_date->MonthNames = $Translation['month names'];
	$combo_date->NamePrefix = 'date';

	if($selected_id) {
		// mm: check member permissions
		if(!$arrPerm['view']) return $Translation['tableAccessDenied'];

		// mm: who is the owner?
		$ownerGroupID = sqlValue("SELECT `groupID` FROM `membership_userrecords` WHERE `tableName`='item_prices' AND `pkValue`='" . makeSafe($selected_id) . "'");
		$ownerMemberID = sqlValue("SELECT LCASE(`memberID`) FROM `membership_userrecords` WHERE `tableName`='item_prices' AND `pkValue`='" . makeSafe($selected_id) . "'");

		if($arrPerm['view'] == 1 && getLoggedMemberID() != $ownerMemberID) return $Translation['tableAccessDenied'];
		if($arrPerm['view'] == 2 && getLoggedGroupID() != $ownerGroupID) return $Translation['tableAccessDenied'];

		// can edit?
		$AllowUpdate = 0;
		if(($arrPerm['edit'] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm['edit'] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm['edit'] == 3) {
			$AllowUpdate = 1;
		}

		$res = sql("SELECT * FROM `item_prices` WHERE `id`='" . makeSafe($selected_id) . "'", $eo);
		if(!($row = db_fetch_array($res))) {
			return error_message($Translation['No records found'], 'item_prices_view.php', false);
		}
		$combo_item->SelectedData = $row['item'];
		$combo_date->DefaultDate = $row['date'];
		$urow = $row; /* unsanitized data */
		$row = array_map('safe_html', $row);
	} else {
		$filterField = Request::val('FilterField');
		$filterOperator = Request::val('FilterOperator');
		$filterValue = Request::val('FilterValue');
		$combo_item->SelectedData = $filterer_item;
	}
	$combo_item->HTML = '<span id="item-container' . $rnd1 . '"></span><input type="hidden" name="item" id="item' . $rnd1 . '" value="' . html_attr($combo_item->SelectedData) . '">';
	$combo_item->MatchText = '<span id="item-container-readonly' . $rnd1 . '"></span><input type="hidden" name="item" id="item' . $rnd1 . '" value="' . html_attr($combo_item->SelectedData) . '">';

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_item__RAND__ = { text: "", value: "<?php echo addslashes($selected_id ? $urow['item'] : htmlspecialchars($filterer_item, ENT_QUOTES)); ?>"};

		jQuery(function() {
			setTimeout(function() {
				if(typeof(item_reload__RAND__) == 'function') item_reload__RAND__();
			}, 50); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function item_reload__RAND__() {
		<?php if(($AllowUpdate || $AllowInsert) && !$dvprint) { ?>

			$j("#item-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_item__RAND__.value, t: 'item_prices', f: 'item' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="item"]').val(resp.results[0].id);
							$j('[id=item-container-readonly__RAND__]').html('<span class="match-text" id="item-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); } else { $j('.btn[id=items_view_parent]').show(); }


							if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { return { s: term, p: page, t: 'item_prices', f: 'item' }; },
					results: function(resp, page) { return resp; }
				},
				escapeMarkup: function(str) { return str; }
			}).on('change', function(e) {
				AppGini.current_item__RAND__.value = e.added.id;
				AppGini.current_item__RAND__.text = e.added.text;
				$j('[name="item"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); } else { $j('.btn[id=items_view_parent]').show(); }


				if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
			});

			if(!$j("#item-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_item__RAND__.value, t: 'item_prices', f: 'item' },
					success: function(resp) {
						$j('[name="item"]').val(resp.results[0].id);
						$j('[id=item-container-readonly__RAND__]').html('<span class="match-text" id="item-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); } else { $j('.btn[id=items_view_parent]').show(); }

						if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
					}
				});
			}

		<?php } else { ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_item__RAND__.value, t: 'item_prices', f: 'item' },
				success: function(resp) {
					$j('[id=item-container__RAND__], [id=item-container-readonly__RAND__]').html('<span class="match-text" id="item-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=items_view_parent]').hide(); } else { $j('.btn[id=items_view_parent]').show(); }

					if(typeof(item_update_autofills__RAND__) == 'function') item_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_clean());


	// code for template based detail view forms

	// open the detail view template
	if($dvprint) {
		$template_file = is_file("./{$TemplateDVP}") ? "./{$TemplateDVP}" : './templates/item_prices_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	} else {
		$template_file = is_file("./{$TemplateDV}") ? "./{$TemplateDV}" : './templates/item_prices_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Item price details', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', (Request::val('Embedded') ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($arrPerm[1] && !$selected_id) { // allow insert and no record selected?
		if(!$selected_id) $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return item_prices_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return item_prices_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if(Request::val('Embedded')) {
		$backAction = 'AppGini.closeParentModal(); return false;';
	} else {
		$backAction = '$j(\'form\').eq(0).attr(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
	}

	if($selected_id) {
		if(!Request::val('Embedded')) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$j(\'form\').eq(0).prop(\'novalidate\', true); document.myform.reset(); return true;" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate) {
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return item_prices_validateData();" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		} else {
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		}
		if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3) { // allow delete?
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		} else {
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', ($separateDV ? '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(($selected_id && !$AllowUpdate) || (!$selected_id && !$AllowInsert)) {
		$jsReadOnly = '';
		$jsReadOnly .= "\tjQuery('#item').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#item_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#price').replaceWith('<div class=\"form-control-static\" id=\"price\">' + (jQuery('#price').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#date').prop('readonly', true);\n";
		$jsReadOnly .= "\tjQuery('#dateDay, #dateMonth, #dateYear').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	} elseif(($AllowInsert && !$selected_id) || ($AllowUpdate && $selected_id)) {
		$jsEditable = "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(item)%%>', $combo_item->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(item)%%>', $combo_item->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(item)%%>', urlencode($combo_item->MatchText), $templateCode);
	$templateCode = str_replace('<%%COMBO(date)%%>', ($selected_id && !$arrPerm[3] ? '<div class="form-control-static">' . $combo_date->GetHTML(true) . '</div>' : $combo_date->GetHTML()), $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(date)%%>', $combo_date->GetHTML(true), $templateCode);

	/* lookup fields array: 'lookup field name' => ['parent table name', 'lookup field caption'] */
	$lookup_fields = ['item' => ['items', 'Item'], ];
	foreach($lookup_fields as $luf => $ptfc) {
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if($pt_perm['view'] || $pt_perm['edit']) {
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] /* && !Request::val('Embedded')*/) {
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-default add_new_parent" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus text-success"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(id)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(item)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(price)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(date)%%>', '', $templateCode);

	// process values
	if($selected_id) {
		if( $dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', safe_html($urow['id']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', html_attr($row['id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(item)%%>', safe_html($urow['item']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(item)%%>', html_attr($row['item']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(item)%%>', urlencode($urow['item']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(price)%%>', safe_html($urow['price']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(price)%%>', html_attr($row['price']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(price)%%>', urlencode($urow['price']), $templateCode);
		$templateCode = str_replace('<%%VALUE(date)%%>', app_datetime($row['date']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(date)%%>', urlencode(app_datetime($urow['date'])), $templateCode);
	} else {
		$templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(item)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(item)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(price)%%>', '0.00', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(price)%%>', urlencode('0.00'), $templateCode);
		$templateCode = str_replace('<%%VALUE(date)%%>', '1', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(date)%%>', urlencode('1'), $templateCode);
	}

	// process translations
	$templateCode = parseTemplate($templateCode);

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if(Request::val('dvprint_x') == '') {
		$templateCode .= "\n\n<script>\$j(function() {\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption) {
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$selected_id) {
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields
	$filterField = Request::val('FilterField');
	$filterOperator = Request::val('FilterOperator');
	$filterValue = Request::val('FilterValue');

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('item_prices');
	if($selected_id) {
		$jdata = get_joined_record('item_prices', $selected_id);
		if($jdata === false) $jdata = get_defaults('item_prices');
		$rdata = $row;
	}
	$templateCode .= loadView('item_prices-ajax-cache', ['rdata' => $rdata, 'jdata' => $jdata]);

	// hook: item_prices_dv
	if(function_exists('item_prices_dv')) {
		$args=[];
		item_prices_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}