<?php

$where = '' ;
if($_SESSION['user_level']==2)
$where='  AND ( tbl_locations.ParentID = '.$_SESSION['user_province1'].' OR Parent.ParentID = '.$_SESSION['user_province1'].'  ) ';

$qry = "SELECT
			tbl_locations.PkLocID,
			tbl_locations.LocName,
			tbl_dist_levels.lvl_name,
			tbl_locationtype.LoctypeName,
			(CASE WHEN tbl_locations.LocLvl=3 THEN Parent.LocName WHEN tbl_locations.LocLvl=4 THEN Parent2.LocName END) AS Province
		FROM
			tbl_locations
		INNER JOIN tbl_locationtype ON tbl_locations.LocType = tbl_locationtype.LoctypeID
		INNER JOIN tbl_dist_levels ON tbl_locations.LocLvl = tbl_dist_levels.lvl_id
		INNER JOIN tbl_locations AS Parent ON tbl_locations.ParentID = Parent.PkLocID
                INNER JOIN tbl_locations AS Parent2 ON Parent.ParentID = Parent2.PkLocID
		WHERE
			tbl_locations.LocLvl IN (3, 4)
                        ".$where."
		ORDER BY
			Parent.PkLocID ASC,
			tbl_locations.LocName ASC";
//echo $qry;exit;
$qryRes = mysql_query($qry);
$xmlstore = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
$xmlstore .="<rows>";
$counter = 1;
while ($row = mysql_fetch_array($qryRes)) {
    $temp = "\"$row[PkLocID]\"";
    $xmlstore .="<row>";
    $xmlstore .="<cell>" . $counter++ . "</cell>";
    $xmlstore .="<cell>" . $row['Province'] . "</cell>";
    $xmlstore .="<cell>" . $row['lvl_name'] . "</cell>";
    $xmlstore .="<cell>" . $row['LoctypeName'] . "</cell>";
    $xmlstore .="<cell>" . $row['LocName'] . "</cell>";
    $xmlstore .="<cell type=\"img\">" . PUBLIC_URL . "dhtmlxGrid/dhtmlxGrid/codebase/imgs/edit.gif^" . PUBLIC_URL . "dhtmlxGrid/dhtmlxGrid/codebase/imgs/edit.gif^javascript:editFunction($temp)^_self</cell>";
    $xmlstore .="<cell></cell>";
    $xmlstore .="</row>";
}
$xmlstore .="</rows>";
