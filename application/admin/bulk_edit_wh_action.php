<?php
include("../includes/classes/AllClasses.php");

//echo '<pre>';
//print_r($_REQUEST);
//exit;

foreach($_REQUEST['updated'] as $wh_id => $flag){
    
    if($flag == 1)
    {
            $de_months = 2 ;
            if(!empty($_REQUEST['editable_de_mon'][$wh_id])) $de_months = $_REQUEST['editable_de_mon'][$wh_id];

            $rank = '';
            if(!empty($_REQUEST['rank'][$wh_id])) $rank = $_REQUEST['rank'][$wh_id];

            $qry = " UPDATE tbl_warehouse SET wh_rank = '".$rank."' , editable_data_entry_months = '".$de_months."' WHERE wh_id = '".$wh_id."'; ";
            //echo '<br/>'.$qry;
            mysql_query($qry);
    }
}

header("location:bulk_edit_wh_screen.php?dist_id=".$_REQUEST['dist_id']."&stk_id=".$_REQUEST['stk_id']);
exit;
?>