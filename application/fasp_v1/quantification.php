<?php
/**
 * quantification
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
$source = (!empty($_REQUEST['source']))?$_REQUEST['source']:'';
$selYear = (!empty($_REQUEST['year']))?$_REQUEST['year']:'';
if(empty($selYear))  $selYear = date('Y');


$qry = "SELECT
            fq_forecasting_master.pk_id,
            fq_forecasting_master.`year`,
            fq_forecasting_master.reference,
            fq_forecasting_master.`level`,
            fq_forecasting_master.level_id,
            fq_forecasting_master.item_group,
            tbl_locations.LocName,
            tbl_dist_levels.lvl_name
        FROM
            fq_forecasting_master
        INNER JOIN tbl_locations ON fq_forecasting_master.level_id = tbl_locations.PkLocID
        INNER JOIN tbl_dist_levels ON fq_forecasting_master.`level` = tbl_dist_levels.lvl_id
        ORDER BY fq_forecasting_master.pk_id desc
        ";
//query result
$rsQry = mysql_query($qry) or die();
//fetch result
$forecasting_opts = $fc_name='';
$forecasting_data  = array();
while ($row = mysql_fetch_assoc($rsQry)) {
    //pipulate province combo
    $sel ='';
    if(isset($_REQUEST['forecasted_id']) && $_REQUEST['forecasted_id'] == $row['pk_id']){ 
        $sel="selected";
        $forecasting_data = $row;
    }
    
    $forecasting_opts .= '<option value="'.$row['pk_id'].'" '.$sel.'>'.$row['reference'].' ('.$row['pk_id'].') </option>';
}
$task_order = (isset($_REQUEST['forecasted_id']))?$forecasting_data['item_group']:'';
//echo '<pre>';print_r($forecasting_data);exit;
if($task_order=='to3') 
{
    $task_order_full_name = 'Family Planning';
    $itm_cat = '1';
}    
if($task_order=='to4'){
    $task_order_full_name = 'MNCH';
    $itm_cat = '5';
}






$qry = "SELECT
            list_master.pk_id as master_id,
            list_master.list_master_name,
            list_detail.list_value,
            list_detail.pk_id as list_id,
            list_detail.description,
            list_detail.rank
        FROM
             list_master
        INNER JOIN list_detail ON list_detail.list_master_id = list_master.pk_id
        WHERE
            list_master.pk_id IN (22,23)";
$rsQry = mysql_query($qry);
$list_arr = array();
while ($row = mysql_fetch_assoc($rsQry)) 
{
    $list_arr[$row['master_id']][$row['list_id']]  = $row;
}
//echo '<pre>';print_r($list_arr);exit;
$all_cols = array();
$all_cols[$task_order]['forecast']['short_name'] = 'A';
$all_cols[$task_order]['forecast']['long_name'] = 'Forecast';
$all_cols[$task_order]['soh']['short_name'] = 'B';
$all_cols[$task_order]['soh']['long_name'] = 'Stock on hand SOH';
$all_cols[$task_order]['pipeline']['short_name'] = 'C';
$all_cols[$task_order]['pipeline']['long_name'] = 'Stock in pipeline';
$all_cols[$task_order]['quantification']['short_name'] = 'D';
$all_cols[$task_order]['quantification']['long_name'] = 'Quantification';
$all_cols[$task_order]['quantification']['formula'] = 'A-(B+C)';
//$all_cols[$task_order]['unit']['short_name'] = 'E';
//$all_cols[$task_order]['unit']['long_name'] = 'Unit of Measure UOM';
//$all_cols[$task_order]['orderFrequency']['short_name'] = 'F';
//$all_cols[$task_order]['orderFrequency']['long_name'] = 'Order Frequency';
//$all_cols[$task_order]['price']['short_name'] = 'G';
//$all_cols[$task_order]['price']['long_name'] = 'Price';
//$all_cols[$task_order]['amount']['short_name'] = 'H';
//$all_cols[$task_order]['amount']['long_name'] = 'Amount';
//$all_cols[$task_order]['amount']['formula'] = 'I=DxG';
$all_cols[$task_order]['remarks']['short_name'] = 'I';
$all_cols[$task_order]['remarks']['long_name'] = 'Remarks';


$disabled = '';
if(isset($_REQUEST['submit']))
{
    $disabled = ' disabled ';
    
    
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
        $all_prods[$task_order][$row['itm_id']]  = $row['itm_name'];
    }

    $fetched_data = array();
    $qry = "SELECT
                fq_forecasting_master.pk_id,
                fq_forecasting_child.location_id,
                fq_forecasting_child.product_id,
                fq_forecasting_child.proposed_fc
            FROM
                 fq_forecasting_master
            INNER JOIN fq_forecasting_child ON fq_forecasting_child.master_id = fq_forecasting_master.pk_id
            WHERE
                fq_forecasting_master.pk_id = '".$_REQUEST['forecasted_id']."'
            ";
    //query result
    $rsQry = mysql_query($qry) or die('err forecast');
    
    while ($row = mysql_fetch_assoc($rsQry)) {
        $fetched_data[$task_order]['forecast'][$row['product_id']] = $row['proposed_fc'];
    }
    
    $qry = "
		SELECT
                        itminfo_tab.itm_name,
                        SUM(tbl_stock_detail.Qty) AS soh,
                        tbl_itemunits.UnitType,
                        itminfo_tab.itm_id
                FROM
                        stock_batch
                INNER JOIN itminfo_tab ON stock_batch.item_id = itminfo_tab.itm_id
                INNER JOIN tbl_itemunits ON itminfo_tab.itm_type = tbl_itemunits.UnitType
                INNER JOIN tbl_stock_detail ON stock_batch.batch_id = tbl_stock_detail.BatchID
                INNER JOIN tbl_stock_master ON tbl_stock_detail.fkStockID = tbl_stock_master.PkStockID
                INNER JOIN tbl_warehouse ON stock_batch.funding_source = tbl_warehouse.wh_id
                WHERE
                (
                        tbl_stock_master.WHIDFrom = 123
                        OR tbl_stock_master.WHIDTo = 123
                )
                AND stock_batch.funding_source IN (
                        SELECT
                                funding_stk_prov.funding_source_id
                        FROM
                                funding_stk_prov
                        WHERE
                                funding_stk_prov.province_id = ".$forecasting_data['level_id']."
                )
                AND tbl_stock_master.temp = 0
                GROUP BY
                        itminfo_tab.itm_id
                ORDER BY
                        itminfo_tab.frmindex
            ";
    //query result
    $rsQry = mysql_query($qry) or die('err forecast');
    
    while ($row = mysql_fetch_assoc($rsQry)) {
        $fetched_data[$task_order]['soh'][$row['itm_id']] = $row['soh'];
    }
    
    $qry_6 = "
               
            SELECT
                itminfo_tab.itm_id,
                (shipments.shipment_quantity) as shipment_quantity,
                sum(tbl_stock_detail.Qty) as received_qty
            FROM
                    shipments
            INNER JOIN tbl_locations ON shipments.procured_by = tbl_locations.PkLocID
            INNER JOIN tbl_warehouse ON shipments.stk_id = tbl_warehouse.wh_id
            INNER JOIN itminfo_tab ON shipments.item_id = itminfo_tab.itm_id
            LEFT JOIN tbl_stock_master ON tbl_stock_master.shipment_id = shipments.pk_id
            LEFT JOIN tbl_stock_detail ON tbl_stock_detail.fkStockID = tbl_stock_master.PkStockID
            WHERE
                shipments.shipment_date > '".date('Y-m-d')."' 
                AND shipments.status NOT IN ('Cancelled','Received')

                AND shipments.stk_id IN ( SELECT
                                    funding_stk_prov.funding_source_id
                                    FROM
                                    funding_stk_prov
                                    WHERE
                                    funding_stk_prov.province_id = ".$forecasting_data['level_id']." )

            GROUP BY
                shipments.pk_id,
                itminfo_tab.itm_id
                
                    ";
//echo $qry_6;exit;

    $res_6 = mysql_query($qry_6);
    $pipeline_detail_arr = array();
    while ($row_6 = mysql_fetch_assoc($res_6)) {
        $pipe_qty = $row_6['shipment_quantity'] - $row_6['received_qty'];
        
        if(empty($fetched_data[$task_order]['pipeline'][$row_6['itm_id']]))$fetched_data[$task_order]['pipeline'][$row_6['itm_id']]=0;
        $fetched_data[$task_order]['pipeline'][$row_6['itm_id']] += $pipe_qty;
    }
}
//echo '<pre>';print_r($fetched_data);exit;
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
                            Quantification
                        <?php include("back_include.php"); ?> 

                        </h3>
                        <div class="widget" data-toggle="collapse-widget">
                            <div class="widget-head">
                                <h3 class="heading">Quantification</h3>
                            </div>
                            <div class="widget-body">
                                <form name="frm" id="frm" action="" method="post" role="form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            
                                           
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>Date of Quantification</label>
                                                    <div class="controls">
                                                        <input class="form-control" readonly="" value="<?=date('d-M-Y');?>" />
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4" id="province_div" style="">
                                                <div class="control-group">
                                                    <label>Forecasted Data Reference</label>
                                                    <div class="controls">
                                                        <select <?=$disabled?> required="" name="forecasted_id" id="forecasted_id" class="form-control input-sm">
                                                            <option value="">Select</option>
                                                            <?php
                                                            echo $forecasting_opts;
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                             if(!isset($_REQUEST['submit']) )
                                             {
                                             ?>
                                             
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>&nbsp;</label>
                                                    <div class="controls">
                                                        <input type="submit" name ="submit" id="submit" value="Go" class="btn btn-primary input-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                             }
                                             ?>
                                            <?php
                                             if(isset($_REQUEST['submit']) )
                                             {
                                             ?>
                                             
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>Product Category</label>
                                                    <div class="controls">
                                                        <input disabled="" value="<?=$forecasting_data['item_group']?>" class="form-control input-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="control-group">
                                                    <label>Year</label>
                                                    <div class="controls">
                                                        <input disabled="" value="<?=$forecasting_data['year']?>" class="form-control input-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="control-group">
                                                    <label>Level</label>
                                                    <div class="controls">
                                                        <input disabled="" value="<?=$forecasting_data['lvl_name']?>" class="form-control input-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="control-group">
                                                    <label>Location</label>
                                                    <div class="controls">
                                                        <input disabled="" value="<?=$forecasting_data['LocName']?>" class="form-control input-sm">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                             }
                                             ?>
                                        </div>
                                    </div>
                                    </form>
                                    <?php
                                    if(isset($_REQUEST['submit']) )
                                    {
                                        
                                    ?>
                                <form name="frm2" id="frm2" action="quantification_action.php" method="post" role="form">
                                    <input type="hidden" name="year"      value="<?=(isset($_REQUEST['year']))?$_REQUEST['year']:''?>" >
                                    <input type="hidden" name="forecasted_id"      value="<?=(isset($_REQUEST['forecasted_id']))?$_REQUEST['forecasted_id']:''?>" >
                                    <input type="hidden" name="source"    value="<?=(isset($_REQUEST['source']))?$_REQUEST['source']:''?>" >
                                    <input type="hidden" name="office_level"     value="<?=(isset($_REQUEST['office_level']))?$_REQUEST['office_level']:''?>" >
                                    <input type="hidden" name="province"  value="<?=(isset($_REQUEST['province']))?$_REQUEST['province']:''?>" >
                                    <div class="row">
                                        <div class="col-md-12">
                                            
                                            <div class="portlet box">
						
						<div class="portlet-body">
                                                        <div class="row">
								
								<div class="col-md-12 btn green justify">Product Data</div>
								<div class="col-md-12">
                                                                    <div class=" active" id="tab11">
                                                                        <div class="portlet  ">
                                                                            <div class="portlet-body">
                                                                                <table class="table table-condensed table-hover table-bordered">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <?php
                                                                                            
                                                                                            echo '<td> </td>';
                                                                                            foreach($all_cols[$task_order] as $col_id => $col_data)
                                                                                            {
                                                                                                echo '<td align="center" >'.$col_data['long_name'].' <br/> ('.$col_data['short_name'].')';
                                                                                                if(!empty($col_data['formula'])) echo '<br/><b>'.$col_data['formula'].'</b>';
                                                                                                echo '</td>';
                                                                                                
                                                                                            }
                                                                                            ?>
                                                                                        </tr>
                                                                                       
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php
                                                                                           
                                                                                               foreach($all_prods[$task_order] as $prod_id => $prod_name)
                                                                                                {
                                                                                                    
                                                                                                    $rspan = count($all_prods[$task_order]);
                                                                                                    echo '<tr>';
                                                                                                    echo '<td  style="">'.$prod_name.'</td>';
                                                                                                    foreach($all_cols[$task_order] as $col_id => $col_data)
                                                                                                     {
                                                                                                        
                                                                                                        if($col_id=='remarks')
                                                                                                        {
                                                                                                           echo '<td ><textarea name="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'" type="text" class="form-control"  maxlength="250" ></textarea></td>';
                                                                                                        }
                                                                                                        elseif($col_id=='unit')
                                                                                                        {
                                                                                                           echo '<td ><select name="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$col_id.'" style="padding:2px !important;" class="form-control">';
                                                                                                           foreach($list_arr['22'] as $k=>$v)
                                                                                                           {
                                                                                                               echo '<option value="'.$k.'">'.$v['list_value'].'</option>';
                                                                                                           }
                                                                                                           echo '</select>';
                                                                                                        }
                                                                                                        elseif($col_id=='orderFrequency')
                                                                                                        {
                                                                                                            echo '<td ><select name="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$col_id.'" style="padding:2px !important;" class="form-control">';
                                                                                                            foreach($list_arr['23'] as $k=>$v)
                                                                                                            {
                                                                                                                echo '<option value="'.$k.'">'.$v['list_value'].'</option>';
                                                                                                            }
                                                                                                            echo '</select>';
                                                                                                            
                                                                                                        }
                                                                                                        elseif($col_id=='forecast' || $col_id=='soh'||$col_id=='pipeline' )
                                                                                                        {
                                                                                                            $this_val = 0;
                                                                                                            if(!empty($fetched_data[$task_order][$col_id][$prod_id])) $this_val = $fetched_data[$task_order][$col_id][$prod_id];
                                                                                                            
                                                                                                            if($col_id=='forecast')$round_up = 4;
                                                                                                            else $round_up = 0;
                                                                                                           
                                                                                                            echo '<td ><input readonly value="'.number_format($this_val,$round_up).'" name="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$col_id.'" type="text" class=" form-control right"></td>';
                                                                                                        }
                                                                                                        elseif( $col_id=='quantification')
                                                                                                        {
                                                                                                            //Quantification = Forecasted + (SOH - Pipeline)
                                                                                                            $this_val=0;
                                                                                                            @$this_val = $fetched_data[$task_order]['forecast'][$prod_id] - ($fetched_data[$task_order]['soh'][$prod_id] + $fetched_data[$task_order]['pipeline'][$prod_id]);
                                                                                                             
                                                                                                            $clr='';
                                                                                                            if($this_val<0)$clr='color:red';
                                                                                                            
                                                                                                            echo '<td ><input readonly value="'.number_format($this_val,2).'" name="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$col_id.'" data-prod-id="'.$prod_id.'" style="'.$clr.'" type="text" class=" form-control right '.$col_id.'_column"></td>';
                                                                                                        }
                                                                                                        elseif($col_id=='amount')
                                                                                                        {
                                                                                                            echo '<td ><input readonly name="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'"  id="input_'.$task_order.'_'.$col_id.'" type="number"  data-prod-id="'.$prod_id.'" class="grid_input_number form-control right '.$col_id.'_column"></td>';
                                                                                                        }
                                                                                                        elseif($col_id=='price')
                                                                                                        {
                                                                                                            echo '<td ><input  name="input_'.$task_order.'_'.$prod_id.'_'.$col_id.'" maxlength="8" max="999999" id="input_'.$task_order.'_'.$col_id.'" type="number" min="0" data-prod-id="'.$prod_id.'" step=".01" class="grid_input_number form-control right '.$col_id.'_column"></td>';
                                                                                                        }
                                                                                                        
                                                                                                     }
                                                                                                    echo '</tr>';
                                                                                                }

                                                                                            
                                                                                           ?>
                                                                                    </tbody>
                                                                                    
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row col-md-12">
                                                                            <div class="right"><input class="btn btn-green" type="submit" value="Save"></div>
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
                                    if(isset($_REQUEST['submit']) && isset($already_saved))
                                    {
                                        ?>
                                        <div class="row">
                                            <div class="col-md-12"> 
                                                 <div class="portlet light">
                                                    <div class="portlet-body">
                                                        Already 3 Demographic Data Entries have been saved for the year <b><?=$_REQUEST['year']?></b> of <b><?=$_REQUEST['source']?> ,  <?=($level_name)?></b> Level
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
            $(".grid_input_number").keyup(function(event) {
//                if ($.isNumeric($(this).val())){
//                    $(this).attr('style', "border-radius: 1px; border:#00000 1px solid;");
//                }
//                else
//                {
//                    $(this).attr('style', "border-radius: 5px; border:#FF0000 1px solid;");
//                }
            });
            
            
            $(".price_column").keyup(function(event) {
                var price       = $(this).val();
                var prod_id     = $(this).data('prod-id');
                var quantification    = $('.quantification_column[data-prod-id='+prod_id+']').val().replace(/,/g, '');
                var pr        = parseFloat(price) 
                var qt         = parseFloat(quantification);
                var total       = pr * qt;

                 if (isNaN(total)) total =0;

                $('.amount_column[data-prod-id='+prod_id+']').val(total);
                console.log(pr+','+qt);
            });
        });
        
        $(function() {
            $('#office_level').change(function() {
                officeType($(this).val());
            });
//            $('#province').change(function() {
//                var provId = $(this).val();
//                showDistricts(provId);
//            });
            

            // Submit Form
            $('#submit').click(function(e) {
                $('body').addClass("loading");
            });
        });
        function officeType(officeLevel)
        {
            if (parseInt(officeLevel) == 2)
            {
                $('#province_div').hide();
                $('#district_div').hide();
            }
            else if (parseInt(officeLevel) == 3)
            {
                $('#province_div').show();
                //showDistricts($('#province').val());
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
                    url: "ajax_calls.php",
                    data: {provinceId: provId, validate: 'yes'},
                    dataType: 'html',
                    success: function(data)
                    {
                        $('#district_data').html(data);
                    }
                });
            }
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
    if($_REQUEST['err']=='0')
    {
    ?>
    <script>
        var self = $('[data-toggle="notyfy"]');
        notyfy({
            force: true,
            text: 'Demographics Data Entry Saved Successfully.',
            type: 'success',
            layout: self.data('layout')
        });

    </script>
    <?php
    }
    ?>
</body>
</html>