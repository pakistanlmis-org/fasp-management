<?php
include("../includes/classes/AllClasses.php");
//echo '<pre>';print_r($_REQUEST);exit;
$qry = "SELECT
            fq_forecasting_master.*
        FROM
            fq_forecasting_master
        WHERE 
            fq_forecasting_master.pk_id = '".$_REQUEST['forecasted_id']."'
        ";
//query result
$rsQry = mysql_query($qry) or die();
$fc_data = mysql_fetch_assoc($rsQry) ;
//print_r($fc_data);exit;
$strSql = " INSERT INTO `fq_quantification_master` 
            SET 
            `forecasting_master_id`= '".$_REQUEST['forecasted_id']."', 
            `year`='".$fc_data['year']."', 
            `description`='', 
            `level`='".$fc_data['level']."', 
            `level_id`='".$fc_data['level_id']."', 
            `item_group`='".$fc_data['item_group']."',   
            `reference`= '', 
            `created_by`= ".$_SESSION['user_id'].", 
            `created_at`= now(),
            `date_of_entry`= now()
   ";
//echo $strSql;exit;
$rsSql = mysql_query($strSql) or die('Err inserting quantification P')  ;
$parent_id = mysql_insert_id();

$input_fields_arr = array();
foreach($_REQUEST as $field_name => $field_val)
{
    $temp = array();
    $temp = explode('_',$field_name);
    if($temp[0] == 'input')
    {
        $item_group =  $temp[1];
        $prod_id    =  $temp[2];
        $col_id     =  $temp[3];
        
        $input_fields_arr[$item_group][$prod_id][$col_id]=$field_val;
        
    }
}

//echo '<pre>';print_r($input_fields_arr);exit;
foreach($input_fields_arr as $item_group => $item_group_data)
{
    foreach($item_group_data as $prod_id => $prod_data)
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
        $strSql2 = " INSERT INTO `fq_quantification_child` 
                        SET 
                        `quantification_master_id`= $parent_id, 
                        `product_id`= '$prod_id' 

                        $cols;
               ";
            //echo $strSql2;exit;
            $rsSql2 = mysql_query($strSql2) or die('Err inserting quantification C') ;
    }
}
//exit;
$_SESSION['err']['msg']='Data Saved Successfully.';
$_SESSION['err']['type']='success';
header("location:quantification.php");
exit;

?>