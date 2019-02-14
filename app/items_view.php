<?php
// This script and data application were generated by AppGini 5.74
// Download AppGini for free from https://bigprof.com/appgini/download/

	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");
	@include("$currDir/hooks/items.php");
	include("$currDir/items_dml.php");

	// mm: can the current member access this page?
	$perm=getTablePermissions('items');
	if(!$perm[0]){
		echo error_message($Translation['tableAccessDenied'], false);
		echo '<script>setTimeout("window.location=\'index.php?signOut=1\'", 2000);</script>';
		exit;
	}

	$x = new DataList;
	$x->TableName = "items";

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = array(   
		"`items`.`id`" => "id",
		"`items`.`item_description`" => "item_description",
		"`items`.`unit_price`" => "unit_price"
	);
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = array(   
		1 => '`items`.`id`',
		2 => 2,
		3 => '`items`.`unit_price`'
	);

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = array(   
		"`items`.`id`" => "id",
		"`items`.`item_description`" => "item_description",
		"`items`.`unit_price`" => "unit_price"
	);
	// Fields that can be filtered
	$x->QueryFieldsFilters = array(   
		"`items`.`id`" => "ID",
		"`items`.`item_description`" => "Item Description",
		"`items`.`unit_price`" => "Unit price"
	);

	// Fields that can be quick searched
	$x->QueryFieldsQS = array(   
		"`items`.`id`" => "id",
		"`items`.`item_description`" => "item_description",
		"`items`.`unit_price`" => "unit_price"
	);

	// Lookup fields that can be used as filterers
	$x->filterers = array();

	$x->QueryFrom = "`items` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm[2]==0 ? 1 : 0);
	$x->AllowDelete = $perm[4];
	$x->AllowMassDelete = true;
	$x->AllowInsert = $perm[1];
	$x->AllowUpdate = $perm[3];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 0;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 10;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation["quick search"];
	$x->ScriptFileName = "items_view.php";
	$x->RedirectAfterInsert = "items_view.php?SelectedID=#ID#";
	$x->TableTitle = "Items";
	$x->TableIcon = "resources/table_icons/installer_box.png";
	$x->PrimaryKey = "`items`.`id`";
	$x->DefaultSortField = '2';
	$x->DefaultSortDirection = 'asc';

	$x->ColWidth   = array(  150, 80);
	$x->ColCaption = array("Item Description", "Unit price");
	$x->ColFieldName = array('item_description', 'unit_price');
	$x->ColNumber  = array(2, 3);

	// template paths below are based on the app main directory
	$x->Template = 'templates/items_templateTV.html';
	$x->SelectedTemplate = 'templates/items_templateTVS.html';
	$x->TemplateDV = 'templates/items_templateDV.html';
	$x->TemplateDVP = 'templates/items_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HighlightColor = '#FFF0C2';

	// mm: build the query based on current member's permissions
	$DisplayRecords = $_REQUEST['DisplayRecords'];
	if(!in_array($DisplayRecords, array('user', 'group'))){ $DisplayRecords = 'all'; }
	if($perm[2]==1 || ($perm[2]>1 && $DisplayRecords=='user' && !$_REQUEST['NoFilter_x'])){ // view owner only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `items`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='items' and lcase(membership_userrecords.memberID)='".getLoggedMemberID()."'";
	}elseif($perm[2]==2 || ($perm[2]>2 && $DisplayRecords=='group' && !$_REQUEST['NoFilter_x'])){ // view group only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `items`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='items' and membership_userrecords.groupID='".getLoggedGroupID()."'";
	}elseif($perm[2]==3){ // view all
		// no further action
	}elseif($perm[2]==0){ // view none
		$x->QueryFields = array("Not enough permissions" => "NEP");
		$x->QueryFrom = '`items`';
		$x->QueryWhere = '';
		$x->DefaultSortField = '';
	}
	// hook: items_init
	$render=TRUE;
	if(function_exists('items_init')){
		$args=array();
		$render=items_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: items_header
	$headerCode='';
	if(function_exists('items_header')){
		$args=array();
		$headerCode=items_header($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$headerCode){
		include_once("$currDir/header.php"); 
	}else{
		ob_start(); include_once("$currDir/header.php"); $dHeader=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%HEADER%%>', $dHeader, $headerCode);
	}

	echo $x->HTML;
	// hook: items_footer
	$footerCode='';
	if(function_exists('items_footer')){
		$args=array();
		$footerCode=items_footer($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$footerCode){
		include_once("$currDir/footer.php"); 
	}else{
		ob_start(); include_once("$currDir/footer.php"); $dFooter=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%FOOTER%%>', $dFooter, $footerCode);
	}
?>