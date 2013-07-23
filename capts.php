<?php
ob_start();							// 8/28/10

$cols = 6;	// no. of columns in the list presentation

/*
8/21/10 initial release
*/
error_reporting(E_ALL);				

@session_start();
require_once($_SESSION['fip']);		//7/28/10
//do_login(basename(__FILE__));

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

@session_start();	
$func = (empty($_POST))? "l":$_POST['func'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Captions processor</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="text/javascript" />
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css" />
<SCRIPT>
	function do_edit(in_id) {
		document.to_edit_form.frm_id.value=in_id;
		document.to_edit_form.submit();	
		}
</SCRIPT>
</HEAD>
<BODY>
<SCRIPT TYPE="text/javascript" src="./js/wz_tooltip.js"></SCRIPT><!-- 1/3/10 -->

	<FORM NAME = 'to_edit_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
		<INPUT TYPE ='hidden' NAME = 'frm_id' VALUE='' />
		<INPUT TYPE ='hidden' NAME = 'func' VALUE='e' />
		</FORM>
	<FORM NAME = 'can_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
		<INPUT TYPE ='hidden' NAME = 'func' VALUE='l' />
		</FORM>
	<FORM NAME = 'do_restore_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
		<INPUT TYPE ='hidden' NAME = 'func' VALUE='r' />
		</FORM>
<?php 
	switch ($func) {
		case "u" :			// update
			$the_repl = quote_smart(trim($_POST['frm_repl'])) ;
			$query = "UPDATE `$GLOBALS[mysql_prefix]captions` SET `repl` = {$the_repl} WHERE `id` = {$_POST['frm_id']} LIMIT 1;";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

			$outstr = urlencode("Update applied!");
			header("Location:capts.php?caption={$outstr}");
			break;

		case "l" :	
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` ORDER BY `capt` ASC ";		
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$rows =  mysql_affected_rows(); 			// Could be a mysql_num_rows() as well
			$j = 1;
			$perCol = (integer)(ceil($rows/$cols)); 			// How many items per col
			$colors = array ('odd', 'even');
	
			$i=0;
																		// outer table
			echo "<TABLE ID='outer' ALIGN='center' CELLPADDING = 4 >";
			$notice = (array_key_exists('caption', $_GET))? $_GET['caption']: "";
			echo "<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'><B><I>{$notice}</I></B></TD></TR>\n";
			echo "<TR CLASS='even'><TD COLSPAN=99 ALIGN='center'><H3>Click <u>caption</u> to edit</H3></TD></TR>\n";
			echo "<TR VALIGN='top'><TD>";
			$out_str = "<TABLE ALIGN='center' border=0>\n";
		
			$out_str .=  "<TR CLASS='odd'><TD><B>&nbsp;&nbsp;Caption</B></TD><TD><B>&nbsp;&nbsp;Replacement</B></TD></TR>\n";
			while ($row = stripslashes_deep(mysql_fetch_assoc($result))) {
				$i++;
				$capt_val = shorten($row['capt'], 16);
				$repl_val = shorten($row['repl'], 16);
				$out_str .=  "<TR CLASS = '{$colors[$i%2]}' onClick = 'do_edit({$row['id']});'>
					<TD onMouseover=\"Tip(escape('{$row['capt']}'));\" onmouseout=\"UnTip();\" >{$capt_val}</TD>
					<TD onMouseover=\"Tip('{$row['repl']}');\" onmouseout=\"UnTip();\" >{$repl_val}</TD>
					</TR>\n";
				if ($i == $perCol){
					$i=0;
					$out_str .=  "</TABLE>\n";
					echo $out_str;
					echo "</TD><TD>";		// outer table
					$out_str = "\n<TABLE BORDER=0 ALIGN='center'>";
					$out_str .=  "<TR CLASS='odd'><TD><B>&nbsp;&nbsp;Caption</B></TD><TD><B>&nbsp;&nbsp;Replacement</B></TD></TR>\n";
					}
				$j++;
				}		// end while()
			$out_str .=  "</TABLE>";
			echo $out_str;
			echo "</TD></TR>";
?>
			<TR CLASS='odd'><TD COLSPAN=99 ALIGN='center'><BR />
				<INPUT TYPE = 'button' VALUE = 'Restore default captions' onClick = "if(confirm('Click OK to restore all original captions')){document.do_restore_form.submit();}"
				</TD></TR>			
			</TABLE><!-- outer table -->
<?php			
			break;

		case "e" :			// edit
			$query = "SELECT * FROM `$GLOBALS[mysql_prefix]captions` WHERE `id` = {$_POST['frm_id']} LIMIT 1";		
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
			$row =  stripslashes_deep(mysql_fetch_array($result));
?>
			<FORM NAME = 'to_edit_form' METHOD = 'post' ACTION = '<?php print basename(__FILE__);?>'>
				<INPUT TYPE ='hidden' NAME = 'frm_id' VALUE='<?php print $_POST['frm_id'];?>' />
				<INPUT TYPE ='hidden' NAME = 'func' VALUE='u' />
			<TABLE ALIGN='center' STYLE = 'margin-top:60px'>
			<TR CLASS='even' VALIGN = 'bottom'><TH COLSPAN=2>Enter caption change</TH></TR>
			<TR CLASS='odd' VALIGN = 'bottom'><TD COLSPAN=2>&nbsp;</TD></TR>
			
			<TR CLASS='odd' VALIGN='baseline'>
				<TD><?php print $row['capt'];?>:&nbsp;</TD>
				<TD><INPUT TYPE = "text" NAME = "frm_repl" VALUE="<?php print $row['repl'];?>" size = 36></TD>	<!-- 8/30/10 -->
				</TR>
			<TR CLASS='odd' VALIGN='baseline'>
				<TD>&nbsp;</TD>
				</TR>
			<TR CLASS='even'><TD COLSPAN=2 ALIGN='center'>
				<INPUT TYPE ='button' VALUE = 'Cancel' onClick="document.can_form.submit();" />
				<INPUT TYPE ='reset' VALUE = 'Reset' onClick = "this.form.reset()" STYLE = 'margin-left:40px;' />
				<INPUT TYPE ='submit' VALUE = 'Next'  STYLE = 'margin-left:40px;' />
				</FORM>
				</TABLE>
<?php
			break;

		case "r" :			// restore defaults
			$the_table = "$GLOBALS[mysql_prefix]captions";

			$query = "TRUNCATE TABLE `{$the_table}`;";
			$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(),basename( __FILE__), __LINE__);

			require_once ("./incs/capts.inc.php");		// array string - 8/30/10

			for ($i=0; $i< count($capts); $i++) {		// 8/30/10
				$temp = quote_smart($capts[$i]);
	
				$query = "INSERT INTO `{$the_table}` (`capt`, `repl`) VALUES ($temp, $temp);";
				$result = mysql_query($query) or do_error($query, 'mysql query failed', mysql_error(), basename( __FILE__), __LINE__);
				}				// end for ($i...)
		
			unset ($result);
			$outstr = urlencode("Restored to original values!");
			header("Location:capts.php?caption={$outstr}");
			break;

		default :
			echo "ERROR - ERROR - ERROR" . __LINE__; 
		}	// end switch 
	
?>
</BODY>
</HTML>
