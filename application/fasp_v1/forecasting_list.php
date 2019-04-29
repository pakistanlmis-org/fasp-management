<?php
include("../includes/classes/AllClasses.php");
//include header
include(PUBLIC_PATH . "html/header.php");

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
                            Forecasting List
                            <?php include("back_include.php"); ?>
                        </h3>
                        <div class="widget" data-toggle="collapse-widget">
                            <div class="widget-head">
                                <h3 class="heading">Forecasting List</h3>
                            </div>
                            <div class="widget-body">
                               <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-bordered table-condensed"f>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Purpose</td>
                                                    <td>Province</td>
                                                    <td>Product Category</td>
                                                    <td>Source</td>
                                                    <td>Start Year</td>
                                                    <td>End Year</td>
                                                    <td>Action</td>
                                                </tr>
                                <?php
                                $qry = "SELECT
                                            fq_master_data.pk_id,
                                            fq_master_data.base_year,
                                            fq_master_data.start_year,
                                            fq_master_data.end_year,
                                            fq_master_data.purpose,
                                            fq_master_data.source,
                                            fq_master_data.stakeholders,
                                            fq_master_data.province_id,
                                            fq_master_data.forecasting_methods,
                                            fq_master_data.item_group,
                                            tbl_locations.LocName
                                        FROM
                                            fq_master_data
                                        INNER JOIN tbl_locations ON fq_master_data.province_id = tbl_locations.PkLocID";
                                //query result
                                $rsQry = mysql_query($qry) or die();
                                //fetch result
                                $c=1;
                                while ($row = mysql_fetch_array($rsQry)) {
                                    echo '<tr>';
                                    echo '<td>'.$c++. '</td>';
                                    echo '<td>'.$row['purpose']. '</td>';
                                    echo '<td>'.(($row['item_group']=='to3')?"FP":"MNCH"). '</td>';
                                    echo '<td>'.$row['LocName']. '</td>';
                                    echo '<td>'.$row['source']. '</td>';
                                    echo '<td>'.$row['start_year']. '</td>';
                                    echo '<td>'.$row['end_year']. '</td>';
                                    echo '<td>';
                                    echo ' <a class="btn btn-xs green-haze" href="mnch_forecasting_calculator.php?id='.$row['pk_id']. '">Forecast Calculator</a>';
                                    echo ' <a class="btn btn-xs green"  >Adjustments</a>';
                                    echo ' <a class="btn btn-xs blue"  >QUantification</a>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                ?>
                                            </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
    <?php 
    //include footer
    include PUBLIC_PATH . "/html/footer.php"; 
    ?>
</body>
</html>