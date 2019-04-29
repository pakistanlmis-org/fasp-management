<?php
include("../includes/classes/AllClasses.php");
include(PUBLIC_PATH . "html/header.php");

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
<body class="page-header-fixed page-quick-sidebar-over-content" onLoad="doInitGrid()">
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
                                <h3 class="heading">All Districts</h3>
                            </div>
                            <div class="widget-body">
<input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search here ..." title="Type in to search">
<table id="myTable" class="table table-bordered table-condensed table-hover" width="100%" cellpadding="0" cellspacing="0" align="center">
                                    <tr class="info">
                                        <td>#</td>
                                        <td>Province</td>
                                        <td>District</td>
                                        <td>Stakeholder</td>
                                        <td>Total SDPs</td>
                                        <td>Action</td>
                                    </tr>
                                         <?php
                                         $qry = "SELECT
                                                    pros.LocName AS prov_name,
                                                    pros.PkLocID as prov_id,
                                                    dists.PkLocID as dist_id,
                                                    dists.LocName AS dist_name,
                                                    stk.stkid,
                                                    stk.stkname,
                                                    Count(tbl_warehouse.wh_id) as total_wh
                                                FROM
                                                tbl_locations AS dists
                                                INNER JOIN tbl_warehouse ON dists.PkLocID = tbl_warehouse.dist_id
                                                INNER JOIN stakeholder AS stk ON tbl_warehouse.stkid = stk.stkid
                                                INNER JOIN stakeholder AS stk_off ON tbl_warehouse.stkofficeid = stk_off.stkid
                                                INNER JOIN tbl_locations AS pros ON dists.ParentID = pros.PkLocID
                                                WHERE
                                                    dists.LocLvl = 3 AND
                                                    stk_off.lvl = 7
                                                GROUP BY
                                                    dists.PkLocID,
                                                    dists.LocName,
                                                    stk.stkid,
                                                    stk.stkname
                                                ORDER BY
                                                    prov_id,
                                                    dist_name ASC
";
//                                        echo $qry;exit;
                                        $qryRes = mysql_query($qry);
                                        $xmlstore = " ";
                                        $counter = 1;
                                        while ($row = mysql_fetch_array($qryRes)) {
                                            $xmlstore .="<tr>";
                                            $xmlstore .="<td>" . $counter++ . "</td>";
                                            $xmlstore .="<td>" . $row['prov_name'] . "</td>";
                                            $xmlstore .="<td>" . $row['dist_name'] . "</td>";
                                            $xmlstore .="<td>" . $row['stkname'] . "</td>";
                                            $xmlstore .="<td align=\"right\">" . $row['total_wh'] . "</td>";
                                            $xmlstore .="<td><a target=\"\" href=\"bulk_edit_wh_screen.php?dist_id=".$row['dist_id']."&stk_id=".$row['stkid']."\">Edit</a></td>";
                                            $xmlstore .="</tr>";
                                        }
                                        $xmlstore .=" ";
                                        
                                        echo $xmlstore;
                                        ?>
                                     
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    //Including Required files
    include PUBLIC_PATH . "/html/footer.php";
    //include PUBLIC_PATH . "/html/reports_includes.php";
    ?>
    
<script>
function myFunction() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[1];
    //td += tr[i].getElementsByTagName("td")[5];
    
    
    if (td) {
      txtValue = td.textContent || td.innerText;
    
        td = tr[i].getElementsByTagName("td")[2];
        txtValue += td.textContent || td.innerText;
        td = tr[i].getElementsByTagName("td")[3];
        txtValue += td.textContent || td.innerText;
        td = tr[i].getElementsByTagName("td")[4];
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