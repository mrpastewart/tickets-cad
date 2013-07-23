<?php
error_reporting(E_ALL);

$button_height = 50;		// height in pixels
$button_width = 160;		// width in pixels
$button_spacing = 4;		// spacing in pixels
$map_size = .75;			// map size multiplier - as a percent of full size
$butts_width = 0;

$units_side_bar_height = .6;		// max height of units sidebar as decimal fraction of screen height - default is 0.6 (60%)

/*
7/13/10 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/20/10 handle non-unit access
8/27/10 button alignment, can_edit() added
8/28/10 handle facility events
8/29/10 added disp_status to units line
8/30/10 option size added
9/3/10 added user call selection via $mode
10/8/10 added self-refresh to update 'Other current calls ', DEFINES 
*/

session_start();	
require_once('incs/functions.inc.php');	
do_login(basename(__FILE__));
define("UNIT", 0);
define("MINE", 1);
define("ALL", 2);

//$istest = TRUE;
if ($istest) {
	if (!empty($_GET)) {
		print "GET<BR/>\n";
		dump ($_GET);
		}
	if (!empty($_POST)) {
		print "POST<BR/>\n";
		dump ($_POST);
		}
	}
$internet = $_SESSION['internet'];	
require_once('incs/functions_major_nm.inc.php');				// 7/28/10

// 0=>unit, 1=>my calls, 2=> all calls - 9/3/10 
																// set/initialize $mode 
if (array_key_exists('frm_mode', $_GET)) {$mode =  $_GET['frm_mode'];}
else {						// unset
	if (is_unit())  {$mode = UNIT;}
	else {

		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";			
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
//		if (!(intval ($user_row['responder_id'])>0)) {$mode=2; }	// $mode => 'all' if no unit associated this user
		$mode = (intval ($user_row['responder_id'])>0)? MINE: ALL;		// $mode => 'all' if no unit associated this user - 10/3/10
		}
	}		// end if/else initialize $mode

//dump($_REQUEST);
//dump(__LINE__);
//dump($mode);

function get_butts($ticket_id, $unit_id) {	
	$win_height =  get_variable('map_height') + 120;
	$win_width = get_variable('map_width') + 10;
	if ($_SESSION['internet']) {
		print "<INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Map' onClick  = \"var popWindow = window.open('map_popup.php?id={$ticket_id}', 'PopWindow', 'resizable=1, scrollbars, height={$win_height}, width={$win_width}, left=250,top=50,screenX=250,screenY=50'); popWindow.focus();\" />\n"; // 7/3/10
		}
	if (can_edit()) {
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'New' onClick = \"var newWindow = window.open('add_nm.php?mode=1', 'addWindow', 'resizable=1, scrollbars, height=500, width=600, left=100,top=100,screenX=100,screenY=100'); newWindow.focus();\" />\n"; // 8/9/10
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Edit' onClick = \"var mailWindow = window.open('edit_nm.php?mode=1&id={$ticket_id}', 'editWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100'); newWindow.focus();\" />\n"; // 2/1/10

		if (!is_closed($ticket_id)) {		// 10/5/09
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Close' onClick = \"var mailWindow = window.open('close_in.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=480, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\" />\n";  // 8/20/09
			}
		} 		// end if ($can_edit())
	if (is_administrator() || is_super() || is_unit()){
		if (!is_closed($ticket_id)) {
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Action' onClick  = \"var actWindow = window.open('action_w.php?mode=1&ticket_id={$ticket_id}', 'ActWindow', 'resizable=1, scrollbars, height=480, width=900, left=250,top=50,screenX=250,screenY=50'); popWindow.focus();\" />\n"; // 7/3/10
			print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Person' onClick  = \"var popWindow = window.open('patient_w.php?mode=1&ticket_id={$ticket_id}', 'PerWindow', 'resizable=1, scrollbars, height=400, width=600, left=250,top=50,screenX=250,screenY=50'); actWindow.focus();\" />\n"; // 7/3/10
			}
		print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Notify' onClick  = \"var notWindow = window.open('config.php?mode=1&func=notify&id={$ticket_id}', 'NotWindow', 'resizable=1, scrollbars, height=400, width=600, left=250,top=50,screenX=250,screenY=50'); notWindow.focus();\" />\n"; // 7/3/10
		}
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Note' onClick = \"var noteWindow = window.open('add_note.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=240, width=600, left=100,top=100,screenX=100,screenY=100'); noteWindow.focus();\" />\n"; // 10/8/08
//	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Print' onClick='main.php?print=true&id=$ticket_id;'>\n ";
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'E-mail' onClick = \"var mailWindow = window.open('mail.php?ticket_id=$ticket_id', 'mailWindow', 'resizable=1, scrollbars, height=600, width=600, left=100,top=100,screenX=100,screenY=100'); mailWindow.focus();\" />\n"; // 2/1/10
	print "<BR /><INPUT TYPE='button' CLASS = 'btn_smaller' VALUE = 'Dispatch' onClick = \"var dispWindow = window.open('routes_nm.php?frm_mode=1&ticket_id={$ticket_id}', 'dispWindow', 'resizable=1, scrollbars, height=480, width=" . round (0.8 * ($_SESSION['scr_width'])) . ", left=100,top=100,screenX=100,screenY=100'); dispWindow.focus();\" />\n"; // 2/1/10
	}				// end function get butts()

function adj_time($time_stamp) {
	$temp = mysql2timestamp($time_stamp);					// MySQL to integer form
	return date ("H:i", $temp);
	}

// $api_key = get_variable('gmaps_api_key');		// 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>

	<HEAD><TITLE>Tickets - Mobile Terminal Module</TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" />
	<META HTTP-EQUIV="Expires" CONTENT="0" />
	<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE" />
	<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
	<LINK REL=StyleSheet HREF="default.css" TYPE="text/css" />
	<STYLE>
		input.btn_chkd 		{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#EFEFEF;  border:1px solid;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: inset;text-align: center; } 
		input.btn_not_chkd 	{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; } 
		input.btn_smaller 	{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width-40;?>px; height: <?php print $button_height-6;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; } 
		input:hover 		{ background-color: white; border-width: 4px; border-STYLE: outset;}
		div.sel 			{ margin-top: <?php print $button_spacing;?>px; width: <?php print $button_width;?>px; height: <?php print $button_height;?>px; color:#050;  font: bold 120% 'trebuchet ms',helvetica,sans-serif; background-color:#DEE3E7;  border-color: #696 #363 #363 #696; border-width: 4px; border-STYLE: outset;text-align: center; } 

		select.sit 			{ font: 11px Verdana, Geneva, Arial, Helvetica, sans-serif; background-color: white; color: #102132; border: none;}
		A 					{ FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #000099; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; TEXT-DECORATION: none}
		.disp_stat 			{ FONT-WEIGHT: bold; FONT-SIZE: 12px; COLOR: #FFFFFF; BACKGROUND-COLOR: #000000; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif;}
		option				{ FONT-SIZE: 16px;}

	</STYLE>	
	<SCRIPT TYPE="text/javascript" src="./js/misc_function.js"></SCRIPT>
	<SCRIPT>
	function $() {									// 1/21/09
		var elements = new Array();
		for (var i = 0; i < arguments.length; i++) {
			var element = arguments[i];
			if (typeof element == 'string')		element = document.getElementById(element);
			if (arguments.length == 1)			return element;
			elements.push(element);
			}
		return elements;
		}
	
		parent.frames["upper"].$('user_id').innerHTML = "<?php print $_SESSION["user_id"]; ?>";		// 

<?php
	if (array_key_exists('log_in', $_GET)) {						// 12/26/09 unit login? - array_key_exists('hello', $a
?>
		parent.frames["upper"].mu_init ();										// start polling
		if (parent.frames.length == 3) {										// 1/20/09, 4/10/09
			parent.calls.location.href = 'board.php';							// 1/11/09
			}
<?php
		}		// end unit login
														// identify 'my' unit
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `r`
				LEFT JOIN `$GLOBALS[mysql_prefix]user` `u` ON (`r`.`id` = `u`.`responder_id`)
				WHERE `u`.`id` = {$_SESSION['user_id']}
				LIMIT 1";
	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$resp_row = stripslashes_deep(mysql_fetch_assoc($result));
				
?>
	var frame_rows;			// frame
	parent.upper.show_butts();										// 1/21/09
	parent.frames["upper"].document.getElementById("gout").style.display  = "inline";

	try {
		parent.frames["upper"].$("user_id").innerHTML  = 	"<?php print $_SESSION['user_id'];?>";
		parent.frames["upper"].$("whom").innerHTML  = 		"<?php print $_SESSION['user'];?>";
		parent.frames["upper"].$("level").innerHTML = 		"<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].$("script").innerHTML  = 	"<?php print LessExtension(basename(__FILE__));?>";
<?php
	if (is_unit()) {									// we have a unit?
?>	
		parent.frames["upper"].$("term").innerHTML  = 		"<?php print $resp_row['name'];?>";
		parent.frames["upper"].$("add").style.display  = "none";		// hide 'New'
<?php
		}
?>
		}
	catch(e) {
		}

<?php																	// 4/10/10
	if ((intval(get_variable('call_board')) == 2)&& (is_unit())) {						// hide the frame
?>
	frame_rows = parent.document.getElementById('the_frames').getAttribute('rows');
	var rows = frame_rows.split(",", 4);
	rows[1] = 0;
	temp = rows.join(",");
	parent.document.getElementById('the_frames').setAttribute('rows', temp);		// set revised cb frame height
	
<?php
		}		// end if ((intval(get_variable('call_board')) == 2)&& (is_unit()))
?>
	function ck_frames() {		//  onLoad = "ck_frames()"
		if(self.location.href==parent.location.href) {
			self.location.href = 'index.php';
			}
		else {
			parent.upper.show_butts();										// 1/21/09
			}
		}		// end function ck_frames()
<?php																	// 4/10/10
	if (intval(get_variable('call_board')) == 0) {						// hide the 'board' button
		print "\t parent.frames['upper'].$('call').style.display = 'none';";
		}
?>		

function replaceButtonText(buttonId, text) {
	if (document.getElementById) {
		var button=document.getElementById(buttonId);
		if (button) {
			if (button.childNodes[0]) {
				button.childNodes[0].nodeValue=text;
				}
			else if (button.value) {
				button.value=text;
				}
			else {					//if (button.innerHTML) 
				button.innerHTML=text;
				}
			}
		}
	}		// end function replaceButtonText()

	function show_btns_closed() {						// 4/30/10
		$('btn_go').style.display = 'inline';
		$('btn_can').style.display = 'inline';
		}
	function hide_btns_closed() {
		$('btn_go').style.display = 'none';
		$('btn_can').style.display = 'none';
		document.frm_interval_sel.frm_interval.selectedIndex=0;
		}

	function sendRequest(url,callback,postData) {		// ajax function set - 1/15/09
		var req = createXMLHTTPObject();
		if (!req) return;
		var method = (postData) ? "POST" : "GET";
//		req.open(method,url,true);
		req.open(method,url,false);		// synchronous, 7/27/09
		req.setRequestHeader('User-Agent','XMLHTTP/1.0');
		if (postData)
			req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.onreadystatechange = function () {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) {
<?php
	if($istest) {print "\t\t\talert('HTTP error ' + req.status + ' " . __LINE__ . "');\n";}
?>
				return;
				}
			callback(req);
			}
		if (req.readyState == 4) return;
		req.send(postData);
		}
	
	var XMLHttpFactories = [
		function () {return new XMLHttpRequest()	},
		function () {return new ActiveXObject("Msxml2.XMLHTTP")	},
		function () {return new ActiveXObject("Msxml3.XMLHTTP")	},
		function () {return new ActiveXObject("Microsoft.XMLHTTP")	}
		];
	
	function createXMLHTTPObject() {
		var xmlhttp = false;
		for (var i=0;i<XMLHttpFactories.length;i++) {
			try {
				xmlhttp = XMLHttpFactories[i]();
				}
			catch (e) {
				continue;
				}
			break;
			}
		return xmlhttp; 
		}

	var announce = true;
	function handleResult(req) {			// the called-back function
		if (announce) {alert('<?php echo __LINE__; ?>');}
		}			// end function handle Result(

	
	var announce = true;
	function handleResult(req) {			// the called-back function
		}			// end function handle Result(

	function toss() {				// ignores button click
		return;
		}
</SCRIPT>
</HEAD>

<?php																// 0=>unit, 1=>my calls, 2=> all calls - 9/3/10 
//dump($mode);
if ((($mode==0) || ($mode==1))) {									// pull $the_unit, $the_unit_name, this user
	$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user` `u` 
		LEFT JOIN `$GLOBALS[mysql_prefix]responder` `r` ON ( `u`.`responder_id` = `r`.`id` )
		WHERE `u`.`id` = {$_SESSION['user_id']} LIMIT 1";		

	$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
	$user_row = stripslashes_deep(mysql_fetch_assoc($result));
	$the_unit = $user_row['responder_id'];
//	dump($user_row);
	$the_unit_name = (empty($user_row['name']))? "NA": $user_row['name'];	// 'NA' if no responder this user
	}
else {
	 $the_unit_name = "NA";
	}
	
$restrict = (($mode==UNIT) || ($mode==MINE))? " `responder_id` = {$the_unit} AND ": "";		// 8/20/10, 9/3/10 
																						// all open assigns
$query = "SELECT *, 
	`a`.`id` AS `assign_id`,
	`r`.`id` AS `unit_id`,
	`r`.`name` AS `unit_name`,
	`t`.`street` AS `tick_street`,
	`t`.`city` AS `tick_city`
	FROM `$GLOBALS[mysql_prefix]assigns` `a`
	LEFT JOIN `$GLOBALS[mysql_prefix]ticket`	 `t` ON (`a`.`ticket_id` = `t`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]responder`	 `r` ON (`a`.`responder_id` = `r`.`id`)
	LEFT JOIN `$GLOBALS[mysql_prefix]unit_types` `u` ON ( `r`.`type` = u.id )	
	WHERE ({$restrict}
	(`clear` IS NULL OR DATE_FORMAT(`clear`,'%y') = '00'))
	ORDER BY `t`.`severity` ASC, `unit_name` ASC, `t`.`problemstart` ASC ;";	

$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename(__FILE__), __LINE__);
if (mysql_affected_rows()==0) {
	$now = mysql_format_date(time() - (intval(get_variable('delta_mins'))*60));
//	$for_str = (is_unit())? " for {$_SESSION['user']} ": "";

	$for_str = $the_unit_name;

	$caption = ($mode==MINE)? "All calls": $the_unit_name;
	$frm_mode = ($mode==MINE)? ALL: MINE;
?>
<BODY>
<BR /><BR /><BR /><BR />
<CENTER><H2><?php print $for_str;?>: no current calls  as of <?php print substr($now, 11,5);?></H2>
<FORM NAME = 'switch_form' METHOD = 'get' ACTION = '<?php print basename(__FILE__);?>'>
<INPUT TYPE='button' CLASS='btn_not_chkd' VALUE = '<?php print $caption;?>' onClick = 'this.form.submit()'>
<INPUT TYPE='hidden' NAME = 'frm_mode' VALUE = '<?php print $frm_mode;?>'>
</FORM>
</CENTER>
	
<?php
	}		// end if (mysql_affected_rows()==0)
	
else {
	
	$i = $selected_indx = 0;
	$assigns_stack = array();
	if (array_key_exists ('assign_id', $_GET)) {									// do we have a selection?
//		print __LINE__ . " " . $_GET['assign_id'];
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {			// yes		
			if ($_GET['assign_id']== $in_row['assign_id']) 	{
				$assn_row = $in_row;												// do this one 
				$selected_indx = $i;
				}
			array_push($assigns_stack, $in_row);									// save all
			$i++;
			}
		}
	else {																			// no selection - take first
		while ($in_row = stripslashes_deep(mysql_fetch_assoc($result))) {
//			dump($in_row["id"]);
			if ($i==0) 	{
				$assn_row = $in_row;
				}										// do first one
			array_push($assigns_stack, $in_row);									// save all
			$i++;
			}
		}		// end if/else()
	 
	$assign_id = $assn_row['assign_id'];
	$ticket_id = $assn_row['ticket_id'];
	$unit_id = $assn_row['responder_id'];
?>
<SCRIPT>
// =======================
	dbfns = new Array ();					//  field names per assigns_t.php expectations
	dbfns['d'] = 'frm_dispatched';
	dbfns['r'] = 'frm_responding';
	dbfns['s'] = 'frm_on_scene';
	dbfns['c'] = 'frm_clear';
	dbfns['e'] = 'frm_u2fenr';
	dbfns['a'] = 'frm_u2farr';
	
	btn_ids = new Array ();					//  
	btn_ids['d'] = 'disp_btn';
	btn_ids['r'] = 'resp_btn';
	btn_ids['s'] = 'onsc_btn';
	btn_ids['c'] = 'clear_btn';
	btn_ids['e'] = 'f_enr_btn';
	btn_ids['a'] = 'f_arr_btn';
	
	btn_labels = new Array ();				//  
	btn_labels['d'] = '<?php print get_text("Disp"); ?> @ ';
	btn_labels['r'] = '<?php print get_text("Resp"); ?> @ ';
	btn_labels['s'] = '<?php print get_text("Onsc"); ?> @ ';
	btn_labels['c'] = '<?php print get_text("Clear"); ?> @';
	btn_labels['e'] = 'Fac enr @';
	btn_labels['a'] = 'Fac arr @';
	
	btn_labels_full = new Array ();				//  
	btn_labels_full['d'] = '<?php print get_text("Dispatched"); ?> @ ';
	btn_labels_full['r'] = '<?php print get_text("Responding"); ?> @ ';
	btn_labels_full['s'] = '<?php print get_text("On-scene"); ?> @ ';
	btn_labels_full['c'] = '<?php print get_text("Clear"); ?> @';
	btn_labels_full['e'] = "Fac'y Enr @";
	btn_labels_full['a'] = "Fac'y Arr @";
	
	function set_assign(which) {						// values; d r s c a e
		var params = "frm_id=" +<?php print $assign_id;?>;				// 1/20/09
		params += "&frm_tick=" +<?php print $ticket_id;?>;
		params += "&frm_unit=" +<?php print $unit_id;?>;
		params += "&frm_vals=" + dbfns[which];
//		alert("362 " + params);
		sendRequest ('assigns_t.php',handleResult, params);			// does the work
		var curr_time = do_time();
		replaceButtonText(btn_ids[which], btn_labels[which] + curr_time)
		CngClass(btn_ids[which], 'btn_chkd');
		parent.frames['upper'].show_msg (btn_labels_full[which] + curr_time);
<?php
if (get_variable('call_board')==2			) {	
	print "\n\t parent.top.calls.do_refresh();\n";
	}
else {
	print "\n\t document.to_refresh.submit();\n";		// 10/8/10
	}
?>
		}		// end function set_assign()
</SCRIPT>
<?php
$unload_str = ($_SESSION['internet'])? " onUnload='GUnload();'"  : "";
?>
	<BODY onLoad = "ck_frames(); <?php echo $unload_str;?> ">
		<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT>
	<DIV ID='to_bottom' style="position:fixed; top:10px; left:150px; height: 12px; width: 10px;" onclick = "location.href = '#bottom';"><IMG SRC="markers/down.png" BORDER=0 /></div>
	
	<A NAME="top" /> <!-- 11/11/09 -->
<?php
//		$unit_str = (isset($user_row))? " for {$user_row['name']}": "(" . get_units_legend() . ")";
		if (($mode == UNIT) || ($mode == MINE)){
			$my_unit = empty($user_row['name'])? "(NA)": $user_row['name'];
			$unit_str = " for {$my_unit}";
			}
		else {
			$unit_str = "";
			}		
		$margin = ($_SESSION['internet'])? 222: 20;

?>

<TABLE ID='outermost' BORDER=0>
<TR>
	<TD ROWSPAN=2 ID = 'left col'>
		<TABLE BORDER=0><TR><TD>
<?php
			get_butts($ticket_id, $unit_id);
?>
		</TD></TR></TABLE>
	</TD>
	<TD ID = 'ctr top' ALIGN='center'>
		<TABLE BORDER=0 ><TR><TD>
<?php
			echo "<SPAN STYLE = 'display:inline; margin-left:{$margin}px;'>Current calls {$unit_str}</SPAN><BR />\n";
			for ($i = 0; $i<count($assigns_stack); $i++) {
				$the_icon = $assigns_stack[$i]['icon'];
				$the_bg_color = 	$GLOBALS['UNIT_TYPES_BG'][$the_icon];		// 8/29/10
				$the_text_color = 	$GLOBALS['UNIT_TYPES_TEXT'][$the_icon];
		
				$checked = ($i == $selected_indx) ? "CHECKED": "";											// this one?
				$the_ticket = (!(is_unit()))? 
					"<SPAN STYLE='background-color:{$the_bg_color};  opacity: .7; color:{$the_text_color};'>" . shorten($assigns_stack[$i]['unit_name'], 12) . "</SPAN>: ":
					"";
		
				$the_ticket .= shorten("{$assigns_stack[$i]['scope']}: 
					{$assigns_stack[$i]['tick_street']}, 
					{$assigns_stack[$i]['tick_city']}", 48);
//				dump($the_ticket);
				$the_disp_stat = get_disp_status ($assigns_stack[$i]);		// 8/29/10
				print "<INPUT TYPE = 'radio' NAME = 'others' VALUE='{$i}' {$checked} STYLE='margin-left: {$margin}px;' \
					onClick = 'location.href=\"" . basename(__FILE__) . "?assign_id={$assigns_stack[$i]['assign_id']}&frm_mode={$mode}\";'>&nbsp;{$the_disp_stat}&nbsp;{$the_ticket}<BR />\n";	
				}
?>
		</TD></TR></TABLE>

	</TD>
	<TD ROWSPAN=2 ID = 'right col'>
<?php
	
			print "<BR CLEAR = 'left' /><P ALIGN='left'>";
		
			$time_disp =  $assn_row['dispatched'];
			$time_resp =  $assn_row['responding'];
			$time_onsc =  $assn_row['on_scene'];
			$time_clear = $assn_row['clear'];
			$time_fenr =  $assn_row['u2fenr'];
			$time_farr =  $assn_row['u2farr'];
		
	$sb_width = max(320, intval($_SESSION['scr_width']* 0.4));				// 8/27/10
	$map_width = ($_SESSION['internet'])? get_variable('map_width'): 0;
	$position =  $sb_width + $map_width + $butts_width +10;
?>
		<TABLE BORDER=0><TR><TD ID='buttons' style=" height: auto; width: 170px; overflow-y: scroll; overflow-x: scroll;">
<?php	if (is_date($time_disp)) { 
?>
		<INPUT ID='disp_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='Disp @ <?php print adj_time($time_disp) ;?>' onClick = 'toss();'/>
<?php			}	
			else  { 
?>
		<INPUT ID='disp_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='Dispatched' onClick = "set_assign('d');"/>
<?php			} 
			if (is_date($time_resp)) { 
?>
		<INPUT ID='resp_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='Resp @ <?php print adj_time($time_resp) ;?>' onClick = 'toss();'/>
<?php			}	
			else  { 
?>
		<INPUT ID='resp_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='Responding' onClick = "set_assign('r');"/>
<?php			} 
			if (is_date($time_onsc)) { 
?>
		<INPUT ID='onsc_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='On-scene @ <?php print adj_time($time_onsc);?>' onClick = 'toss();'/>
<?php			}	
			else  { 
?>
		<INPUT ID='onsc_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='On-scene' onClick = "set_assign('s');"/>
<?php			} 
			if (($assn_row['facility_id']>0) || ($assn_row['rec_facility_id']>0)) {
				if (is_date($time_fenr)) { 
?>
			<INPUT ID='f_enr_btn' TYPE= 'button' CLASS='btn_chkd' VALUE="Fac'y enr @ <?php print adj_time($time_fenr);?>" onClick = 'toss();'/>
<?php				}	
				else  { 
?>
			<INPUT ID='f_enr_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE="Fac'y enroute" onClick = "set_assign('e');"/>
<?php				} 	
				if (is_date($time_farr)) { 
?>
			<INPUT ID='time_farr' TYPE= 'button' CLASS='btn_chkd' VALUE="Fac'y arr @ <?php print adj_time($time_farr);?>" onClick = 'toss();'/>
<?php			}	
				else  { 
?>
			<INPUT ID='time_farr' TYPE= 'button' CLASS='btn_not_chkd' VALUE="Fac'y arrive" onClick = "set_assign('a');"/>
<?php				} 	
				}		//  end if (facility ... )
				
			if (is_date($time_clear)) { 
?>
		<INPUT ID='clear_btn' TYPE= 'button' CLASS='btn_chkd' VALUE='Clear @ <?php print adj_time($time_clear);?>' onClick = 'toss();'/>
<?php			}	
				else  { 
?>
		<INPUT ID='clear_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='Clear' onClick = "set_assign('c');" />	
<?php
					}		// end if (is_date($time_clear))

	if ((is_unit())|| (has_admin())) {				// do/do-not allow staTus change
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]responder` `u` 
			WHERE `u`.`id` = {$unit_id} LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
		$temp_row = mysql_fetch_assoc($result);    
?>
		<DIV CLASS='sel'><?php print get_text("Status"); ?>:<BR /><?php print get_status_sel($unit_id, $temp_row['un_status_id'], "u", 10);?></DIV>
<?php
		}
	 if ($mode == ALL) {
		$query = "SELECT * FROM `$GLOBALS[mysql_prefix]user`
			LEFT JOIN `$GLOBALS[mysql_prefix]responder` ON (`$GLOBALS[mysql_prefix]responder`.`id` = `$GLOBALS[mysql_prefix]user`.`responder_id`)
			WHERE `$GLOBALS[mysql_prefix]user`.`id` = {$_SESSION['user_id']}
			LIMIT 1";
		$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);

		$user_row = stripslashes_deep(mysql_fetch_assoc($result));
//		dump ($user_row);
		if (intval($user_row['responder_id'])>0) {
?>		
			<FORM NAME = 'switch_form' METHOD = 'get' ACTION = '<?php print basename(__FILE__);?>'>
			<INPUT TYPE='hidden' NAME = 'frm_mode' VALUE = '1' />	
			<INPUT ID='chng_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE="<?php print $user_row['name'];?>" onClick = 'document.switch_form.submit();' />
			</FORM>
<?php			
	 		}
	 	}				// end  if ($mode == ALL)
	else {		// do all button
?>
			<FORM NAME = 'switch_form' METHOD = 'get' ACTION = '<?php print basename(__FILE__);?>'>
			<INPUT TYPE='hidden' NAME = 'frm_mode' VALUE = '2' />	
			<INPUT ID='chng_btn' TYPE= 'button' CLASS='btn_not_chkd' VALUE='All calls' onClick = 'document.switch_form.submit();' />
			</FORM>
<?php 
	 	}
?>
		</TD></TR></TABLE>

	</TD>
	</TR>
<TR>
	<TD ID = 'ctr 2nd'>
		<TABLE BORDER=0><TR><TD STYLE='WIDTH:<?php print $butts_width;?>PX'></TD><TD>
<?php
			show_ticket($ticket_id, NULL, NULL, FALSE );		// hide map
?>
		</TD></TR></TABLE>

	</TD>
</TR>
</TABLE>

<?php
		include("./incs/links.inc.php");
		$get_print = 			(array_key_exists('print', ($_GET)))?			$_GET['print']: 		NULL;
		$get_id = 				(array_key_exists('id', ($_GET)))?				$_GET['id']  :			NULL;
		$get_sort_by_field = 	(array_key_exists('sort_by_field', ($_GET)))?	$_GET['sort_by_field']:	NULL;
		$get_sort_value = 		(array_key_exists('sort_value', ($_GET)))?		$_GET['sort_value']:	NULL;
	
?>

<A NAME="bottom" /> <!-- 11/11/09 -->

<?php
	}		// end if/else

//$caption = ($mode==1)? "All calls": $the_unit_name;	
//$mode = ($mode==1)? ALL: MINE;	
?>
	<FORM NAME='to_closed' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'>
	<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_CLOSED'];?>' />
	<INPUT TYPE='hidden' NAME='func' VALUE='' />
	</FORM>
	<FORM NAME='to_all' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
	<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_OPEN'];?>' />
	</FORM>
	<FORM NAME='to_scheduled' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 1/23/09 -->
	<INPUT TYPE='hidden' NAME='status' VALUE='<?php print $GLOBALS['STATUS_SCHEDULED'];?>' />
	</FORM>

	<FORM NAME='to_refresh' METHOD='get' ACTION = '<?php print basename( __FILE__); ?>'> <!-- 10/8/10 -->
	<INPUT TYPE='hidden' NAME='frm_mode' VALUE='<?php print $mode;?>' />
	</FORM>
<p>
<a href="javascript:decreaseFontSize();">-</a> 
<a href="javascript:increaseFontSize();">+</a>
</p>
</BODY>

</HTML>
