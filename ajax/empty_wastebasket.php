<?php
/**
 * @package empty_wastebasket.php 
 * Empties messages wastebasket table - uses truncate to reset id to 0.
 * @since 10/23/12
 * @version 10/23/12
 */
require_once('../incs/functions.inc.php');

$query = "TRUNCATE TABLE `$GLOBALS[mysql_prefix]messages_bin`";
$result = mysql_query($query) or do_error($query, 'mysql_query() failed', mysql_error(), __FILE__, __LINE__);
if($result) {
	$ret_arr[0] = 100;
	} else {
	$ret_arr[0] = 200;
	}
print json_encode($ret_arr);