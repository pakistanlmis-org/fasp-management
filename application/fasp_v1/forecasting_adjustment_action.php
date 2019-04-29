<?php
include("../includes/classes/AllClasses.php");
//echo '<pre>';print_r($_REQUEST);exit;

$strSql = " INSERT INTO `fq_forecasting_master` 
            SET 
            `year`= '".$_REQUEST['year']."', 
            `reference`= '".$_REQUEST['reference']."', 
            `level`= '".$_REQUEST['office_level']."',
            `item_group`= '".$_REQUEST['task_order']."',
            `level_id`= '".(($_REQUEST['office_level']=='3')?$_REQUEST['district']:$_REQUEST['province'])."', 
            `created_by`= ".$_SESSION['user_id'].", 
            `created_at`= now()
   ";

$rsSql = mysql_query($strSql) or die('Err inserting forecasting P')  ;
$parent_id = mysql_insert_id();

$input_fields_arr = array();
foreach($_REQUEST as $field_name => $field_val)
{
    $temp = array();
    $temp = explode('_',$field_name);
    if($temp[0] == 'input')
    {
        $item_group =  $temp[1];
        $location   =  $temp[2];
        $prod_id    =  $temp[3];
        $col_id1     =  $temp[4];
        $col_id2    =  isset($temp[5])?$temp[5]:'';
        
        if(!empty($col_id2)) $col_id = $col_id1.'_'.$col_id2;
        else $col_id = $col_id1;
        
        $input_fields_arr[$item_group][$location][$prod_id][$col_id]=$field_val;
    }
}

//echo '<pre>';print_r($input_fields_arr);exit;
foreach($input_fields_arr as $item_group => $item_group_data)
{
    foreach($item_group_data as $location => $loc_data)
    {
        foreach($loc_data as $prod_id => $prod_data)
        {
            $cols = '';
            foreach($prod_data as $col_id => $field_val)
            {
               if(!empty($field_val))
               {
                $field_val= str_replace(',','',$field_val);
                $field_val= mysql_real_escape_string ($field_val);
                $cols .= ", `$col_id`='$field_val' "; 
               }
            }
            $strSql2 = " INSERT INTO `fq_forecasting_child` 
                            SET 
                            `master_id`= $parent_id, 
                            `location_id`= $location, 
                            `product_id`= '$prod_id' 
                            $cols;
                   ";
                 //echo $strSql2;exit;
                $rsSql2 = mysql_query($strSql2) or die('Err inserting forecasting C') ;
        }
    }
}
//exit;
$_SESSION['err']['msg']='Data Saved Successfully.';
$_SESSION['err']['type']='success';
header("location:forecasting_adjustment.php");
exit;

?>