<?php
include("../includes/classes/AllClasses.php");
//echo '<pre>';print_r($_REQUEST);
//exit;

$strSql = " INSERT INTO `fq_master_data` 
            SET 
            `base_year`='".$_REQUEST['base_year']."'
            , `start_year`='".$_REQUEST['start_year']."'
            , `end_year`='".$_REQUEST['end_year']."'
            , `purpose`='".$_REQUEST['purpose']."'
            , `source`='".$_REQUEST['source']."'
            , `created_by`=".$_SESSION['user_id']."
            , `created_at`=now()
            , `stakeholders`='".implode(',',$_REQUEST['stakeholder'])."'
            , `province_id`='".$_REQUEST['province']."'
            , `forecasting_methods`='".$_REQUEST['fc_methods']."'
            , `item_group`='".$_REQUEST['task_order']."'
   ";
//echo $strSql;
//exit;
$rsSql = mysql_query($strSql) or die('Err inserting demographics P')  ;
$parent_id = mysql_insert_id();

header("location:forecasting_list.php?err=0");
exit;

?>