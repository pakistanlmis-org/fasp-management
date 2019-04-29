<?php
include("../includes/classes/AllClasses.php");
include(PUBLIC_PATH . "html/header.php");

$dist_id = $_REQUEST['dist_id'];
$stk_id = $_REQUEST['stk_id'];
?>
<style>
* {
  box-sizing: border-box;
}

#myInput {
  background-image: url('/css/searchicon.png');
  background-position: 10px 10px;
  background-repeat: no-repeat;
  width: 80%;
  font-size: 16px;
  padding: 12px 20px 12px 40px;
  border: 1px solid #ddd;
  margin-bottom: 12px;
}

#myTable {
  border-collapse: collapse;
  width: 80%;
  border: 1px solid #ddd;
  font-size: 14px;
}

#myTable th, #myTable td {
  text-align: left;
  padding: 5px;
}

#myTable tr {
  border-bottom: 1px solid #ddd;
}

#myTable tr.header, #myTable tr:hover {
  background-color: #f1f1f1;
}
</style>
</head>
<!-- BEGIN body -->
<body class="page-header-fixed page-quick-sidebar-over-content" >
    <!-- BEGIN HEADER -->
    <div class="page-container">
        <?php include $_SESSION['menu']; ?>
        <?php include PUBLIC_PATH . "html/top_im.php"; ?>
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="page-title row-br-b-wp">Bulk Edit Warehouses</h3>
                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget">
                            <div class="widget-head">
                                <h3 class="heading">All Facilities / Warehouses</h3>
                            </div>
                            <div class="widget-body">
                                
<input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search here ..." title="Type in to search">
                                <form action="bulk_edit_wh_action.php" method="post">
                                <table id="myTable" class="table table-bordered table-condensed table-hover" width="100%" cellpadding="0" cellspacing="0" align="center">
                                    <tr class="info">
                                        <td>#</td>
                                        <td>District</td>
                                        <td>Stakeholder</td>
                                        <td>HF Type</td>
                                        <td>SDP / Warehouse</td>
                                        <td>Reporting Started from</td>
                                        <td>Open DE Months</td>
                                        <td>Data Entry (Enabled/Locked)</td>
                                        <td>Status (Active/Closed)</td>
                                        <td>Order of display</td>
                                        <td>DHIS Code</td>
                                        <td></td>
                                    </tr>    
                                    <?php
                                        $qry = " SELECT
                                                        tbl_warehouse.wh_id,
                                                        tbl_hf_type.hf_type,
                                                        tbl_warehouse.wh_name,
                                                        tbl_warehouse.dhis_code,
                                                        tbl_warehouse.wh_rank,
                                                        tbl_warehouse.reporting_start_month,
                                                        tbl_warehouse.editable_data_entry_months,
                                                        tbl_warehouse.is_lock_data_entry,
                                                        stk.stkname,
                                                        tbl_warehouse.is_active,
                                                        tbl_locations.LocName AS dist_name
                                                    FROM
                                                        tbl_warehouse
                                                    INNER JOIN stakeholder AS stk ON tbl_warehouse.stkid = stk.stkid
                                                    INNER JOIN stakeholder AS stk2 ON tbl_warehouse.stkofficeid = stk2.stkid
                                                    INNER JOIN tbl_hf_type ON tbl_warehouse.hf_type_id = tbl_hf_type.pk_id
                                                    INNER JOIN tbl_locations ON tbl_warehouse.dist_id = tbl_locations.PkLocID
                                                    WHERE
                                                        tbl_warehouse.dist_id = $dist_id AND
                                                        stk2.lvl = 7 AND
                                                        tbl_warehouse.stkid = $stk_id
                                                    ORDER BY
                                                        -tbl_warehouse.wh_rank desc,
                                                        tbl_warehouse.wh_name ASC
                                            ";
//                                        echo $qry;exit;
                                        $qryRes = mysql_query($qry);
                                        $xmlstore = " ";
                                        $counter = 1;
                                        while ($row = mysql_fetch_array($qryRes)) {
                                            $wh_status = 'Closed';
                                            $wh_status_cls = 'danger';
                                            if($row['is_active']=='1')
                                            {
                                                $wh_status = 'Active';  
                                                $wh_status_cls = '';
                                            }
                                            $de_status = 'Enabled';
                                            $de_status_cls = '';
                                            if($row['is_lock_data_entry']=='1')
                                            {
                                                $de_status = 'Locked';  
                                                $de_status_cls = 'danger';
                                            }
                                                    
                                            $xmlstore .="<tr>";
                                            $xmlstore .="<td>" . $counter++ . "</td>";
                                            $xmlstore .="<td>" . $row['dist_name'] . "</td>";
                                            $xmlstore .="<td>" . $row['stkname'] . "</td>";
                                            $xmlstore .="<td>" . $row['hf_type'] . "</td>";
                                            $xmlstore .="<td>" . $row['wh_name'] . "</td>";
                                            $xmlstore .="<td>" . (date('Y-M-d',strtotime($row['reporting_start_month']))) . "</td>";
                                            $xmlstore .="<td><input  data-whid=\"". $row['wh_id']."\"   name=\"editable_de_mon[". $row['wh_id']."]\" class=\"form-control inputs\" value=\"" . $row['editable_data_entry_months'] . "\"></td>";
                                            $xmlstore .="<td class=\"".$de_status_cls."\">" . $de_status . "</td>";
                                            $xmlstore .="<td class=\"".$wh_status_cls."\">" . $wh_status . "</td>";
                                            $xmlstore .="<td><input  data-whid=\"". $row['wh_id']."\"   name=\"rank[". $row['wh_id']."]\"  class=\"form-control inputs\" value=\"" . $row['wh_rank'] . "\"></td>";
                                            $xmlstore .="<td align=\"right\">" . $row['dhis_code'] . "</td>";
                                            $xmlstore .="<td><input type=\"hidden\"  data-whid=\"". $row['wh_id']."\"   name=\"updated[". $row['wh_id']."]\"  class=\"form-control update_tracker\" value=\"0\"> </td>";
                                            $xmlstore .="</tr>";
                                        }
                                        $xmlstore .=" ";
                                        
                                        echo $xmlstore;
                                        ?>
                                     
                                </table>
                                    <input type="submit" value="Save all changes">
                                    <input type="hidden" name="dist_id" value="<?=$_REQUEST['dist_id']?>">
                                    <input type="hidden" name="stk_id" value="<?=$_REQUEST['stk_id']?>">
                                    
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include PUBLIC_PATH . "/html/footer.php";
//    include PUBLIC_PATH . "/html/reports_includes.php";
    ?>
   
<script>
    $(function(){
        $('.inputs').keydown(function(){
            var whid = $(this).data('whid');
            
            $('.update_tracker[data-whid='+whid+']').val('1');
            $(this).css("background-color","#b8f9b8");
        });
    });
</script>
<script>
function myFunction() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[4];
    //td += tr[i].getElementsByTagName("td")[5];
    
    
    if (td) {
      txtValue = td.textContent || td.innerText;
    
        td = tr[i].getElementsByTagName("td")[5];
        txtValue += td.textContent || td.innerText;
        td = tr[i].getElementsByTagName("td")[7];
        txtValue += td.textContent || td.innerText;
        td = tr[i].getElementsByTagName("td")[8];
        txtValue += td.textContent || td.innerText;
        td = tr[i].getElementsByTagName("td")[10];
        txtValue += td.textContent || td.innerText;
        td = tr[i].getElementsByTagName("td")[3];
        txtValue += td.textContent || td.innerText;
      //console.log(txtValue);
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}
</script>
</body>
<!-- END body -->
</html>