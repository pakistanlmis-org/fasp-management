<?php
/**
 * forecasting adjustment
 * @package fasp
 * 
 * @author     Ajmal Hussain
 * @email <ahussain@ghsc-psm.org>
 * 
 * @version    2.2
 * 
 */
//include AllClasses
include("../includes/classes/AllClasses.php");
//include header
include(PUBLIC_PATH . "html/header.php");
//echo '<pre>';print_r($_REQUEST);exit;

$office_level = (!empty($_REQUEST['office_level']))?$_REQUEST['office_level']:'';
$task_order     = (!empty($_REQUEST['task_order']))?$_REQUEST['task_order']:'';
$default_dg_col=1;
$itm_cat='';
if($task_order=='to3') 
{
    $task_order_full_name = 'Family Planning';
    $itm_cat = '1';
    $column_name = "Consumption";
    $default_dg_col = '1';
}    
elseif($task_order=='to4'){
    $task_order_full_name = 'MNCH';
    $itm_cat = '5';
    $column_name = "Consumption / Morbidity";
    $default_dg_col = '5';
}



$source = (!empty($_REQUEST['source']))?$_REQUEST['source']:'';
$selYear = (!empty($_REQUEST['year']))?$_REQUEST['year']:'';
if(empty($selYear))  $selYear = date('Y');
$already_saved =0;

$all_ofc_levels=array();
$all_ofc_levels['2'] = 'Provincial';
$all_ofc_levels['3'] = 'District';


$all_cols = array();
$all_cols['all']['Demographic']['dg_1']['short_name']='A1';
$all_cols['all']['Demographic']['dg_1']['long_name']='Source A';
$all_cols['all']['Demographic']['dg_2']['short_name']='A2';
$all_cols['all']['Demographic']['dg_2']['long_name']='Source B';
$all_cols['all']['Consumption']['cons_1']['short_name']='B1';
$all_cols['all']['Consumption']['cons_1']['long_name']='LMIS';
$all_cols['all']['Consumption']['cons_2']['short_name']='B2';
$all_cols['all']['Consumption']['cons_2']['long_name']='Source X';
$all_cols['all']['Forecasting Calculations']['fc_1']['short_name']='C1';
$all_cols['all']['Forecasting Calculations']['fc_1']['long_name']='Forecasting A';
$all_cols['all']['Forecasting Calculations']['fc_2']['short_name']='C2';
$all_cols['all']['Forecasting Calculations']['fc_2']['long_name']='Forecasting B';
$all_cols['all']['Forecasting Calculations']['fc_3']['short_name']='C3';
$all_cols['all']['Forecasting Calculations']['fc_3']['long_name']='Forecasting C';
$all_cols['all']['Forecasting Calculations']['fc_4']['short_name']='C4';
$all_cols['all']['Forecasting Calculations']['fc_4']['long_name']='Forecasting D';
$all_cols['all']['Forecasting Calculations']['final_fc']['short_name']='C5';
$all_cols['all']['Forecasting Calculations']['final_fc']['long_name']='Final Forecasting';
$all_cols['all']['Forecasting Calculations']['adjustment']['short_name']='C6';
$all_cols['all']['Forecasting Calculations']['adjustment']['long_name']='Adjustment (%)';
$all_cols['all']['Forecasting Calculations']['proposed_fc']['short_name']='C7';
$all_cols['all']['Forecasting Calculations']['proposed_fc']['long_name']='Proposed Forecasting';
$all_cols['all']['Remarks']['remarks']['short_name']='-';
$all_cols['all']['Remarks']['remarks']['long_name']='Details / Assumptions';

$disabled = '';
if(isset($_REQUEST['submit_btn']) && $already_saved<3)
{
    $disabled = ' readonly ';
}

$pro_options= "";


    $qry = "SELECT DISTINCT
                        tbl_locations.PkLocID,
                        tbl_locations.LocName
                FROM
                        tbl_locations
                INNER JOIN tbl_warehouse ON tbl_locations.PkLocID = tbl_warehouse.prov_id
                INNER JOIN wh_user ON tbl_warehouse.wh_id = wh_user.wh_id
                WHERE
                        tbl_locations.ParentID IS NOT NULL
                AND tbl_locations.LocLvl = 2
                AND tbl_warehouse.is_active = 1
                ORDER BY
                        tbl_locations.PkLocID";
    $rsQry = mysql_query($qry) or die();
    while ($row = mysql_fetch_array($rsQry)) {
        $sel ='';
        if(isset($_REQUEST['province']) && $_REQUEST['province'] == $row['PkLocID']){
            $sel="selected";
            $prov_name = $row['LocName'];
        }
        $pro_options .='<option value="'.$row['PkLocID'].'" '.$sel.'>'.$row['LocName'].'</option>';
    }

    
    
$qry = " SELECT
            tbl_locations.PkLocID,
            tbl_locations.LocName
            from
            tbl_locations
            WHERE

        ";
if(isset($_REQUEST['office_level']) && $_REQUEST['office_level'] == 3)
{
    $qry .= "   tbl_locations.LocLvl = 3 AND
                tbl_locations.LocType = 4 AND
                tbl_locations.PkLocID = ".$_REQUEST['district']." ";
}
else
{
    $qry .= "   tbl_locations.LocLvl = 2 AND
                LocType =2
                 ";
    if(isset($_REQUEST['province']))
    {
        
        $qry .= "   AND tbl_locations.PkLocID = ".$_REQUEST['province']." ";
    }
}

//echo $qry;exit;
$rsQry = mysql_query($qry);
$prov_arr = array();
$dist_name='';
while ($row = mysql_fetch_array($rsQry)) 
{
    $dist_name = $row['LocName'];
    $prov_arr[$row['PkLocID']] = $row['LocName'];
}                                                            


if(isset($_REQUEST['submit_btn']))
{
    $reference_txt = 'FC-'.strtoupper($task_order).'-'.$all_ofc_levels[$office_level].'-'.$prov_name.''.(($office_level == 3)?'-'.$dist_name:'').'-'.$selYear;

    
    $qry = "SELECT
                    itminfo_tab.itmrec_id,
                    itminfo_tab.itm_id,
                    itminfo_tab.itm_name,
                    itminfo_tab.method_type
                FROM
                itminfo_tab
                WHERE
                    itminfo_tab.method_type IS NOT NULL AND
                    itminfo_tab.itm_category = $itm_cat
                ORDER BY
                itminfo_tab.method_rank ASC";
    $rsQry = mysql_query($qry);
    $all_prods = array();
    while ($row = mysql_fetch_array($rsQry)) 
    {
        $all_prods['to3'][$row['itm_id']]  = $row['itm_name'];
        $all_prods['to4'][$row['itm_id']]  = $row['itm_name'];
    }

    $cons_source ='';
    if($task_order=='to3') 
    {
        $qry = "SELECT
                    fq_non_lmis_consumption_master.`year`,
                    fq_non_lmis_consumption_master.source,
                    fq_non_lmis_consumption_master.`level`,
                    fq_non_lmis_consumption_child.location_id,
                    fq_non_lmis_consumption_child.product_id,
                    fq_non_lmis_consumption_child.`value` as fq_value,
                    fq_non_lmis_consumption_master.pk_id
                FROM
                    fq_non_lmis_consumption_master
                INNER JOIN fq_non_lmis_consumption_child ON fq_non_lmis_consumption_child.master_id = fq_non_lmis_consumption_master.pk_id
                WHERE
                    fq_non_lmis_consumption_master.`level` = ".$office_level." AND
                    fq_non_lmis_consumption_master.`year` = '".$selYear."' AND
                    fq_non_lmis_consumption_child.location_id = ".$_REQUEST['province']."

                ORDER BY
                    fq_non_lmis_consumption_master.pk_id DESC,
                    fq_non_lmis_consumption_child.product_id";
        $rsQry = mysql_query($qry);
        $fetched_data = array();
        //$master_id= '';
        while ($row = mysql_fetch_array($rsQry)) 
        {
            //PICK only last available data of that year.
            if(empty($master_id) || $master_id==$row['pk_id'])
            {
                $fetched_data[$task_order]['Consumption']['cons_2'][$row['location_id']][$row['product_id']]  = (!empty($row['fq_value'])?$row['fq_value']:0);
                $master_id=$row['pk_id'];
                $cons_source=$row['source'];
            }

        }
    }
    elseif($task_order=='to4') 
    {
     $qry = "SELECT
                    fq_morbidity_master.`year`,
                    fq_morbidity_master.source,
                    fq_morbidity_master.`level`,
                    fq_morbidity_child.location_id,
                    fq_morbidity_child.product_id,
                    fq_morbidity_child.`value` as fq_value,
                    fq_morbidity_master.pk_id
                FROM
                    fq_morbidity_master
                INNER JOIN fq_morbidity_child ON fq_morbidity_child.master_id = fq_morbidity_master.pk_id
                WHERE
                    fq_morbidity_master.`level` = ".$office_level." AND
                    fq_morbidity_master.`year` = '".$selYear."' AND
                    fq_morbidity_child.location_id = ".$_REQUEST['province']."

                ORDER BY
                    fq_morbidity_master.pk_id DESC,
                    fq_morbidity_child.product_id";
     ///echo $qry;exit;
        $rsQry = mysql_query($qry);
        $fetched_data = array();
        //$master_id= '';
        while ($row = mysql_fetch_array($rsQry)) 
        {
            //PICK only last available data of that year.
            if(empty($master_id) || $master_id==$row['pk_id'])
            {
                $fetched_data[$task_order]['Consumption']['cons_2'][$row['location_id']][$row['product_id']]  = (!empty($row['fq_value'])?$row['fq_value']:0);
                $master_id=$row['pk_id'];
                $cons_source=$row['source'];
            }

        }
    }
//echo '<pre>';print_r($fetched_data[$task_order]['Consumption']);exit;

$qry = "SELECT
            itminfo_tab.itm_id,
                summary_province.province_id,
                SUM(
                        summary_province.consumption
                ) AS consumption
        FROM
                summary_province
        INNER JOIN itminfo_tab ON summary_province.item_id = itminfo_tab.itmrec_id
        INNER JOIN tbl_locations ON summary_province.province_id = tbl_locations.PkLocID
        INNER JOIN stakeholder ON summary_province.stakeholder_id = stakeholder.stkid
        WHERE
            YEAR(summary_province.reporting_date) = '$selYear'
            AND tbl_locations.ParentID IS NOT NULL
            AND summary_province.province_id = ".$_REQUEST['province']."
            AND itminfo_tab.itm_category = 1
        GROUP BY
                itminfo_tab.itm_id";
$rsQry = mysql_query($qry);
while ($row = mysql_fetch_array($rsQry)) 
{
    $fetched_data[$task_order]['Consumption']['cons_1'][$row['province_id']][$row['itm_id']]  = $row['consumption'];
}

$qry = "SELECT
            fq_demographics_master.pk_id,
            fq_demographics_child.col_id,
            fq_demographics_child.`value`,
            fq_demographics_child.location_id,
            fq_demographics_master.source
        FROM
            fq_demographics_master
        INNER JOIN fq_demographics_child ON fq_demographics_child.master_id = fq_demographics_master.pk_id
        WHERE  
            fq_demographics_child.location_id = ".$_REQUEST['province']." AND
            fq_demographics_child.col_id = ".(empty($_REQUEST['demographic_data'])?$default_dg_col:$_REQUEST['demographic_data'])."
             /*   AND fq_demographics_master.item_group = '".$task_order."' */ AND
            fq_demographics_master.`year` = '$selYear'
        ORDER by
            fq_demographics_master.pk_id desc";
//echo $qry;exit;
$rsQry = mysql_query($qry);
$dg_count =1;
$sources_arr = array();
while ($row = mysql_fetch_array($rsQry)) 
{
    if($dg_count>2)
    {
        break;
    }
    $fetched_data[$task_order]['Demographic']['dg_'.$dg_count][$row['location_id']]['val']      = $row['value'];
    $fetched_data[$task_order]['Demographic']['dg_'.$dg_count][$row['location_id']]['source']   = $row['source'];
    $sources_arr[$dg_count] = $row['source'];
    
    $dg_count++;
}
}


$qry = "SELECT
            fq_cols.pk_id,
            fq_cols.item_group,
            fq_cols.short_name,
            fq_cols.long_name
        FROM
            fq_cols
        WHERE
            /*fq_cols.item_group = '".(empty($task_order)?'to3':$task_order)."' AND*/
            fq_cols.col_type = 'main' AND
            fq_cols.is_active = 1
        ORDER BY
            fq_cols.order_by ASC
";
$rsQry = mysql_query($qry);
$dg_cols_arr = array();
while ($row = mysql_fetch_array($rsQry)) 
{
    $dg_cols_arr[$row['pk_id']] = $row;
}
//echo '<pre>';print_r($fetched_data);
//$fetched_data[$task_order]['Consumption']['cons_1']['1']['1'] = '9870';
//$fetched_data[$task_order]['Demographic']['dg_1']['1']['1'] = '10000';
//$fetched_data[$task_order]['Demographic']['dg_2']['1']['1'] = '2000';
//echo '<pre>';print_r($fetched_data);exit;

$fix_dd_value= '';
if(isset($_REQUEST['submit_btn']))
        $fix_dd_value = ' onfocus="this.defaultIndex=this.selectedIndex;" onchange="this.selectedIndex=this.defaultIndex;" ';
?>

<style>
    .nav-pills > li > a:hover,.nav-pills > li.active > a{
        color: #FFFFFF !important;
        background-color: #009C00 !important;
    } 
    .nav-pills > li > a{
        color: #000 !important;
        background-color: #FFFFFF !important;
    }
    .grid_input_number{
        padding: 0px !important;
        font-size: 11px !important;
    }
</style>
    
</head>
<!-- END HEAD -->
<body class="page-header-fixed page-quick-sidebar-over-content">
    <div class="modal"></div>
    <div class="page-container">
        <?php
        //include top
        include PUBLIC_PATH . "html/top.php";
        //include top_im	
        include PUBLIC_PATH . "html/top_im.php";
        ?>
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="page-title row-br-b-wp">
                            Forecasting Adjustment
<?php include("back_include.php"); ?>
                        </h3>
                        <div class="widget" data-toggle="collapse-widget">
                            <div class="widget-head">
                                <h3 class="heading">Forecasting Parameters</h3>
                            </div>
                            <div class="widget-body">
                                <form name="frm" id="frm" action="" method="post" role="form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            
                                            <div class="col-md-3 <?=(isset($_REQUEST['submit_btn']))?'':'hide'?>">
                                                <div class="control-group">
                                                    <label>Entry Reference</label>
                                                    <div class="controls">
                                                        <input <?=$disabled?> class="form-control" readonly="" value="<?=$reference_txt?>" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>Date</label>
                                                    <div class="controls">
                                                        <input <?=$disabled?>  class="form-control" readonly="" value="<?=date('d-M-Y');?>" />
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>Year</label>
                                                    <div class="controls">
                                                        <select <?=$disabled?>  required name="year" id="year" class="form-control input-sm" style="padding:0px !important;"  <?=$fix_dd_value;?>>
                                                            <?php
                                                            for ($j = date('Y')+10; $j >= 2015; $j--) {
                                                                if ($selYear == $j) {
                                                                    $sel = "selected='selected'";
                                                                } else {
                                                                    $sel = "";
                                                                }
                                                                
                                                                if(isset($_REQUEST['submit_btn']))  
                                                                {
                                                                    if ($selYear == $j) 
                                                                    echo '<option value="'.$j.'" '.$sel.'>'.$j.'</option>';
                                                                }
                                                                else 
                                                                    echo '<option value="'.$j.'" '.$sel.'>'.$j.'</option>';
                                                                
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>Product Category</label>
                                                    <div class="controls">
                                                        <select <?=$disabled?>  required name="task_order" id="task_order" class="form-control input-sm" <?=$fix_dd_value;?> >
                                                            <option value="">Select</option>
                                                            <option <?=($task_order=='to3')?' selected ':''?> value="to3">Family Planning</option>
                                                            <option <?=($task_order=='to4')?' selected ':''?>  value="to4">MNCH</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3" id="demographic_data_div" style="">
                                                <div class="control-group" id=" ">
                                                    <label>Demographic Data</label>
                                                    <div class="controls">
                                                        <select required name="demographic_data" id="demographic_data" class="form-control input-sm">
                                                            <?php
                                                                foreach($dg_cols_arr as $k=> $v)
                                                                {
                                                                    $sel='';
                                                                    if(isset($_REQUEST['demographic_data']) && $_REQUEST['demographic_data']==$k) $sel=' selected ';
                                                                    echo '<option value="'.$k.'" '.$sel.' >'.$v['long_name'].'</option>';
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>Level</label>
                                                    <div class="controls">
                                                        <select <?=$disabled?>  required name="office_level" id="office_level" class="form-control input-sm" <?=$fix_dd_value;?>>
<!--                                                            <option value="">Select</option>-->
                                                            <option <?=($office_level==2)?' selected ':''?> value="2">Provincial</option>
<!--                                                            <option <?=($office_level==3)?' selected ':''?>  value="3">District</option>-->
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2" id="province_div" style="">
                                                <div class="control-group">
                                                    <label>Province/Region</label>
                                                    <div class="controls">
                                                        <select required="" <?=$disabled?>  name="province" id="province" class="form-control input-sm" <?=$fix_dd_value;?>>
                                                            <option value="">Select</option>
                                                            <?php
                                                            echo $pro_options;
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-2" id="district_div" style="display:none;">
                                                <div class="control-group" id="district_data">
                                                    <label>District</label>
                                                    <div class="controls">
                                                        <select name="district" id="district" class="form-control input-sm">
                                                            <option value="">Loading...</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-1 <?=((!isset($_REQUEST['submit_btn']) || $already_saved>=3)?'':'hide')?>">
                                                <div class="control-group">
                                                    <label>&nbsp;</label>
                                                    <div class="controls">
                                                        <input type="submit" name ="submit_btn" id="submit_btn" value="Go" class="btn btn-primary input-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    </form>
                                    <?php
                                    if(isset($_REQUEST['submit_btn']) && (int)$already_saved<3)
                                    {
                                        
                                    ?>
                                <form name="frm2" id="frm2" action="forecasting_adjustment_action.php" method="post" role="form">
                                    <input type="hidden" name="year"      value="<?=(isset($_REQUEST['year']))?$_REQUEST['year']:''?>" >
                                    <input type="hidden" name="source"    value="<?=(isset($_REQUEST['source']))?$_REQUEST['source']:''?>" >
                                    <input type="hidden" name="task_order"     value="<?=(isset($_REQUEST['task_order']))?$_REQUEST['task_order']:''?>" >
                                    <input type="hidden" name="reference"     value="<?=(isset($reference_txt))?$reference_txt:''?>" >
                                    <input type="hidden" name="office_level"     value="<?=(isset($_REQUEST['office_level']))?$_REQUEST['office_level']:''?>" >
                                    <input type="hidden" name="province"  value="<?=(isset($_REQUEST['province']))?$_REQUEST['province']:''?>" >
                                    <input type="hidden" name="district"  value="<?=(isset($_REQUEST['district']))?$_REQUEST['district']:''?>" >
                                    <div class="row">
                                        <div class="col-md-12">
                                            
                                            <div class="portlet box">
						
						<div class="portlet-body">
                                                        <div class="">
								<div class="col-md-12 btn green justify"><?=$task_order_full_name?></div>
                                                                    <div class="" id="tab11">
                                                                        <div class="portlet  ">
                                                                            <div class="portlet-body">
                                                                                <table class="table table-condensed table-hover table-bordered">
                                                                                    <thead>
                                                                                        
                                                                                        <tr>
                                                                                            <?php
                                                                                            
                                                                                            echo '<td> </td>';
                                                                                            echo '<td> </td>';
                                                                                            foreach($all_cols['all'] as $group_name => $group_data)
                                                                                            {
                                                                                                $cspan = count($group_data);
                                                                                                if($group_name == 'Consumption')$group_name='Consumption / Morbidity / Prevalance';
                                                                                                echo '<td align="center" colspan="'.$cspan.'">'.$group_name.'</td>';
                                                                                                
                                                                                            }
                                                                                            ?>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <?php
                                                                                            $to3_count = 0;
                                                                                            $d_count =1;
                                                                                            echo '<td>Location</td>';
                                                                                            echo '<td>Product</td>';
                                                                                            foreach($all_cols['all'] as $group_name => $group_data)
                                                                                            {
                                                                                                foreach($group_data as $col_id => $col_data)
                                                                                                {
                                                                                                    if($col_id == 'cons_1')$col_data['long_name'] = 'LMIS / Manual';
                                                                                                    echo '<td align="center" >'.$col_data['long_name'].' <br/> ';
                                                                                                    
                                                                                                    echo '('.$col_data['short_name'].')';
                                                                                                    
                                                                                                    if(isset($sources_arr[$d_count]) && $group_name=='Demographic'){
                                                                                                        echo '<br/><b>'.wordwrap($sources_arr[$d_count], 40, "<br />\n").'</b>';
                                                                                                        $d_count++;
                                                                                                    }
                                                                                                    
                                                                                                    if(isset($cons_source) && $col_id=='cons_2'){
                                                                                                        echo '<br/><b>'.$cons_source.'</b>';
                                                                                                    }
                                                                                                    echo '</td>';
                                                                                                    $to3_count++;
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php
                                                                                        $old_prov='';
                                                                                        $prov_colors = array();
                                                                                        $prov_colors[1] = "#E4F5E4";
                                                                                        $prov_colors[2] = "#E4ECF5";
                                                                                        $prov_colors[3] = "#F5E4F5";
                                                                                        $prov_colors[4] = "#F5EDE4";
                                                                                           foreach($prov_arr as $pro_id => $pro_name)
                                                                                           {
                                                                                               foreach($all_prods[$task_order] as $prod_id => $prod_name)
                                                                                                {
                                                                                                    
                                                                                                    $rspan = count($all_prods[$task_order]);
                                                                                                    echo '<tr>';
                                                                                                    if($old_prov != $pro_name)
                                                                                                    echo '<td style="vertical-align:top;background-color:'.(isset($prov_colors[$pro_id])?$prov_colors[$pro_id]:'').'" rowspan="'.$rspan.'">'.$pro_name.'</td>';
                                                                                                    echo '<td  style="background-color:'.(isset($prov_colors[$pro_id])?$prov_colors[$pro_id]:'').'">'.$prod_name.'</td>';
                                                                                                    foreach($all_cols['all'] as $group_name => $group_data)
                                                                                                    {
                                                                                                        $prod_values = array();
                                                                                                        foreach($group_data as $col_id => $col_data)
                                                                                                        {
                                                                                                            
                                                                                                            if($col_id=='fc_1')
                                                                                                            {
                                                                                                                @$this_val = $fetched_data[$task_order]['Demographic']['dg_1'][$pro_id]['val'] / $fetched_data[$task_order]['Consumption']['cons_1'][$pro_id][$prod_id];
                                                                                                                $prod_values['fc_1'][$prod_id] = number_format($this_val,6);

                                                                                                            }
                                                                                                            elseif($col_id=='fc_2')
                                                                                                            {
                                                                                                                @$this_val = $fetched_data[$task_order]['Demographic']['dg_2'][$pro_id]['val'] / $fetched_data[$task_order]['Consumption']['cons_1'][$pro_id][$prod_id];
                                                                                                                $prod_values['fc_2'][$prod_id] = $this_val;    
                                                                                                            }
                                                                                                            elseif($col_id=='fc_3')
                                                                                                            {
                                                                                                                @$this_val = $fetched_data[$task_order]['Demographic']['dg_1'][$pro_id]['val'] / $fetched_data[$task_order]['Consumption']['cons_2'][$pro_id][$prod_id];
                                                                                                                $prod_values['fc_3'][$prod_id] = $this_val;
                                                                                                            }
                                                                                                            elseif($col_id=='fc_4')
                                                                                                            {
                                                                                                                @$this_val = $fetched_data[$task_order]['Demographic']['dg_2'][$pro_id]['val'] / $fetched_data[$task_order]['Consumption']['cons_2'][$pro_id][$prod_id];
                                                                                                                $prod_values['fc_4'][$prod_id] = $this_val;
                                                                                                            }
                                                                                                            
                                                                                                            
                                                                                                            if($group_name=='Remarks')
                                                                                                            {
                                                                                                               echo '<td ><textarea name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'" type="text" class="form-control"  maxlength="250" ></textarea></td>';
                                                                                                            }
                                                                                                            elseif($group_name=='Consumption' )
                                                                                                            {
                                                                                                               if($col_id == 'cons_1'){
                                                                                                                   if($task_order == 'to4'){
                                                                                                                            $this_val = 0;
                                                                                                                            if(isset($fetched_data[$task_order][$group_name][$col_id][$pro_id][$prod_id])) $this_val = $fetched_data[$task_order][$group_name][$col_id][$pro_id][$prod_id];
                                                                                                                            echo '<td ><input value="'.number_format($this_val).'"  name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'" type="number" min="0"  data-prod-id="'.$prod_id.'" class="'.$col_id.' grid_input_number  form-control right"  maxlength="12" ></td>';
                                                                                                                   }
                                                                                                                   else
                                                                                                                   {
                                                                                                                            $this_val = 0;
                                                                                                                            if(isset($fetched_data[$task_order][$group_name][$col_id][$pro_id][$prod_id])) $this_val = $fetched_data[$task_order][$group_name][$col_id][$pro_id][$prod_id];
                                                                                                                            echo '<td ><input value="'.number_format($this_val).'" readonly name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'"  data-prod-id="'.$prod_id.'"  type="text" class="'.$col_id.'  form-control right"  maxlength="12" ></td>';
                                                                                                                   }
                                                                                                                   
                                                                                                               }
                                                                                                               elseif($col_id == 'cons_2'){
                                                                                                                    $this_val = 0;
                                                                                                                    if(isset($fetched_data[$task_order][$group_name][$col_id][$pro_id][$prod_id])) $this_val = $fetched_data[$task_order][$group_name][$col_id][$pro_id][$prod_id];
                                                                                                                    echo '<td ><input value="'.number_format($this_val).'" readonly name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'"  data-prod-id="'.$prod_id.'"  type="text" class="'.$col_id.'  form-control right"  maxlength="12" ></td>';
                                                                                                            
                                                                                                               }
                                                                                                               
                                                                                                            }
                                                                                                            elseif( $group_name=='Demographic')
                                                                                                            {
                                                                                                               $this_val = 0;
                                                                                                               if(isset($fetched_data[$task_order][$group_name][$col_id][$pro_id]['val'])) $this_val = $fetched_data[$task_order][$group_name][$col_id][$pro_id]['val'];
                                                                                                               $this_val=number_format((float)$this_val);
                                                                                                               echo '<td ><input value="'.$this_val.'" readonly name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'" type="text"  data-prod-id="'.$prod_id.'"  class="'.$col_id.' grid_input_number form-control right"  maxlength="12" ></td>';
                                                                                                            }
                                                                                                            elseif($group_name=='Forecasting Calculations')
                                                                                                            {
                                                                                                               if($col_id=='adjustment')
                                                                                                               {
                                                                                                                   echo '<td ><input name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'" type="number"  data-prod-id="'.$prod_id.'"  class="grid_input_number adjustment_column do_calculations form-control right"  maxlength="12" ></td>';
                                                                                                               }
                                                                                                               elseif($col_id=='fc_1' || $col_id=='fc_2' || $col_id=='fc_3' || $col_id=='fc_4')
                                                                                                               {
                                                                                                                  echo '<td ><input value="'.number_format($prod_values[$col_id][$prod_id],2).'" readonly name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'"  data-prod-id="'.$prod_id.'"  type="text" class="'.$col_id.' grid_input_number form-control right"  maxlength="12" ></td>';
                                                                                                               }
                                                                                                               elseif($col_id=='final_fc')
                                                                                                               {
                                                                                                                   
                                                                                                                    $final_fc_val = ($prod_values['fc_1'][$prod_id] + $prod_values['fc_2'][$prod_id] + $prod_values['fc_3'][$prod_id]+ $prod_values['fc_4'][$prod_id])/4;
                                                                                                                  echo '<td ><input value="'.number_format($final_fc_val,2).'" readonly name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'" type="text"  data-prod-id="'.$prod_id.'"  class="'.$col_id.' grid_input_number  form-control right"  maxlength="12" ></td>';
                                                                                                               }
                                                                                                               elseif($col_id=='proposed_fc')
                                                                                                               {
                                                                                                                   
                                                                                                                    $proposed_fc_val = ($prod_values['fc_1'][$prod_id] + $prod_values['fc_2'][$prod_id] + $prod_values['fc_3'][$prod_id]+ $prod_values['fc_4'][$prod_id])/4;
                                                                                                                  echo '<td ><input readonly value="'.number_format($proposed_fc_val,2).'" name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'" type="text" data-prod-id="'.$prod_id.'" class="'.$col_id.'  form-control right"  maxlength="12" ></td>';
                                                                                                               }
                                                                                                               
                                                                                                            }
                                                                                                            else
                                                                                                            {
                                                                                                               echo '<td ><input name="input_'.$task_order.'_'.$pro_id.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$pro_id.'_'.$col_id.'"   data-prod-id="'.$prod_id.'"  type="text" class="'.$col_id.' grid_input_number form-control"  maxlength="12" ></td>';
                                                                                                            }
                                                                                                       }
                                                                                                    }
                                                                                                    echo '</tr>';
                                                                                                    $old_prov = $pro_name;
                                                                                                }

                                                                                            }
                                                                                           ?>
                                                                                    </tbody>
                                                                                    
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row col-md-12">
                                                                            
                                                                            <div class="col-md-8">
                                                                                <div class=" ">
                                                                                    <div class="note note-info">
                                                                                        To select different demographic subsets please change the `Demographic Data`
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4  ">
                                                                                <div class="  right">
                                                                                        <a href="" onclick="return confirm('Are you sure to discard unsaved changes, and proceed to new data entry?')"  class="btn btn-primary btn-red" >Cancel and exit</a>
                                                                                        <input class="btn btn-red" type="reset" value="Reset">
                                                                                        <input class="btn btn-green" type="submit" value="Save">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row col-md-12">
                                                                            <div class=" ">
                                                                                
                                                                                <h4><b>Forecasting Formulas</b></h4>
                                                                                <pre>Forecasting A (C1)            = A1 / Average Consumption LMIS</pre>
                                                                                <pre>Forecasting B (C2)            = A2 / Average Consumption LMIS</pre>
                                                                                <pre>Forecasting C (C3)            = A1 / Average Consumption Source X</pre>
                                                                                <pre>Forecasting D (C4)            = A2 / Average Consumption Source X</pre>
                                                                                <pre>Final Forecasting (C5)        = (C1 + C2 + C3 + C4) / 4</pre>
                                                                                <pre>Proposed Forecasting (C7)     = C5 + (C5 * (C6/100) )</pre>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                    </div>
                                                                   
								
							</div>
						</div>
                                                
					</div>
                                   
                                        </div>
                                    </div>
                                    
                                </form>
                                    <?php
                                    }
                                   
                                    ?>
                                    
                                
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
    <?php 
    //include footer
    include PUBLIC_PATH . "/html/footer.php"; ?>
    <script>
        $(document).ready(function() {
            
            $("#frm2").submit(function() {
                var c = confirm("Save all changes ?");
                return c; 
            });
            $(".grid_input_number").keyup(function(e) {
                if ($.isNumeric($(this).val())){
                    $(this).attr('style', "border-radius: 1px; border:#00000 1px solid;");
                }
                else
                {
                    $(this).attr('style', "border-radius: 5px; border:#FF0000 1px solid;");
                }
            });
            
            $(".adjustment_column").bind("keyup blur change",function(event) {
                $this = $(this);
                calculate_proposed_fc($this);
            });
            $(".cons_1").bind("keyup blur change",function(event) {
                $this = $(this);
                calculate_forecasting_cols($this);
                calculate_proposed_fc($this);
            });
            
            function calculate_forecasting_cols(){
                var prod_id     = $this.data('prod-id');
                var dg_1        = $('.dg_1[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                var dg_2        = $('.dg_2[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                var cons_1      = $('.cons_1[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                var cons_2      = $('.cons_2[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                var adjustment  = $('.adjustment_column[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                
                if (isNaN(cons_1) || cons_1=='' || cons_1=='0'){
                    var fc_1        = 0;
                    var fc_2        = 0;
                }
                else{
                    var fc_1        = (parseFloat(dg_1)/parseFloat(cons_1)).toFixed(2);
                    var fc_2        = (parseFloat(dg_2)/parseFloat(cons_1)).toFixed(2);
                }
                
                if (isNaN(cons_2) || cons_2=='' || cons_2=='0'){
                    var fc_3        = 0;
                    var fc_4        = 0;
                    
                }
                else{
                    var fc_3        = (parseFloat(dg_1)/parseFloat(cons_2)).toFixed(2);
                    var fc_4        = (parseFloat(dg_2)/parseFloat(cons_2)).toFixed(2);
                    
                }
                var final_fc    = (( parseFloat(fc_1) + parseFloat(fc_2) + parseFloat(fc_3) + parseFloat(fc_4) ) / 4).toFixed(2);
                var proposed_fc = final_fc + ( final_fc * (adjustment/100)).toFixed(2);
                
                if (isNaN(proposed_fc)) proposed_fc = 0;
                if (isNaN(fc_1)) fc_1 = 0;
                if (isNaN(fc_2)) fc_2 = 0;
                if (isNaN(fc_3)) fc_3 = 0;
                if (isNaN(fc_4)) fc_4 = 0;
                
                //console.log('DG>>>>'+dg_1+','+dg_2+','+cons_1+','+cons_2);
                //console.log('FC>>>>'+fc_1+','+fc_2+','+fc_3+','+fc_4);
                $('.fc_1[data-prod-id='+prod_id+']').val(fc_1);
                $('.fc_2[data-prod-id='+prod_id+']').val(fc_2);
                $('.fc_3[data-prod-id='+prod_id+']').val(fc_3);
                $('.fc_4[data-prod-id='+prod_id+']').val(fc_4);
                
                $('.final_fc[data-prod-id='+prod_id+']').val(final_fc);
                $('.proposed_fc[data-prod-id='+prod_id+']').val(proposed_fc);
                
                console.log('Calculating FC Cols:'+prod_id+','+cons_1+','+final_fc+','+proposed_fc);
            }
            
            function calculate_proposed_fc(){
                var prod_id     = $this.data('prod-id');
                var adjustment  = $('.adjustment_column[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                var final_fc    = $('.final_fc[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                var adj         = parseFloat(adjustment) 
                var fin         = parseFloat(final_fc);
                if (isNaN(adj)) adj = 0;
                var total       = fin + (fin *(adj/100));
                
                 if (isNaN(total)) total = 0;
                total = total.toFixed(2);
                $('.proposed_fc[data-prod-id='+prod_id+']').val(total);
                console.log(prod_id+','+adj+','+final_fc+','+total);
            }
            
        });
        $(function() {
            $('#office_level').change(function() {
                officeType($(this).val());
            });
            $('#province').change(function() {
                var provId = $(this).val();
                showDistricts(provId);
            });
            $('#task_order').change(function() {
                var toid = $(this).val();
                show_demographic_cols(toid);
            }); 
            $('#demographic_data').change(function() {
                
                var col_id = $(this).val();
                
                <?php
                if(isset($_REQUEST['submit_btn']))
                {
                ?>
                    if(confirm("New demographic values will be loaded, All unsaved data will be lost.Press OK to continue."))
                    {
                        $("#submit_btn").click();
                    }
                <?php
                }
                else
                {
                    echo '$("#submit_btn").click();';
                }
                ?>
            }); 
            

            // Submit Form
            $('#submit').click(function(e) {
                $('body').addClass("loading");
            });
        });
        function officeType(officeLevel)
        {
            if (parseInt(officeLevel) == 2)
            {
                $('#province_div').show();
                $('#district_div').hide();
            }
            else if (parseInt(officeLevel) == 3)
            {
                $('#province_div').show();
                $('#province').val('');
                $('#district_div').show();
            }
        }
        function showDistricts(provId)
        {
            var officeLevel = $('#office_level').val();
            if (officeLevel == 3)
            {
                $('#district_div').show();
                $('#district').html('<option value="">Loading...</option>');
                $.ajax({
                    type: "POST",
                    url: "load_dist.php",
                    data: {provinceId: provId, validate: 'yes'},
                    dataType: 'html',
                    success: function(data)
                    {
                        $('#district_data').html(data);
                    }
                });
            }
        }
        function show_demographic_cols(toid)
        {
            
            $('#demographic_data_div').show();
            $('#demographic_data').html('<option value="">Loading...</option>');
            $.ajax({
                type: "POST",
                url: "load_dg_cols.php",
                data: {task_order: toid},
                dataType: 'html',
                success: function(data)
                {
                    $('#demographic_data').html(data);
                }
            });
            
        }
        
        
		function showProvinces(pid) {
			var stk = $('#stk_sel').val();
			if (typeof stk !== 'undefined')
			{
				$.ajax({
					url: 'ajax_stk.php',
					type: 'POST',
					data: {stakeholder: stk, provinceId: pid, showProvinces: 1, showAllOpt: 0},
					success: function(data) {
						$('#province').html(data);
					}
				})
			}
		}
    </script>
    <?php
    
    if(!empty($_SESSION['err']['msg']))
    {
    ?>
    <script>
        var self = $('[data-toggle="notyfy"]');
        notyfy({
            force: true,
            text: '<?=$_SESSION['err']['msg']?>',
            type: '<?=$_SESSION['err']['type']?>',
            layout: self.data('layout')
        });

    </script>
    <?php
    $_SESSION['err']['msg']='';
    $_SESSION['err']['type']='';
    }
    ?>
</body>
</html>