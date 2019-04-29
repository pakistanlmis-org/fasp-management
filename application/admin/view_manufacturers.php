<?php
include("../includes/classes/Configuration.inc.php");
include(APP_PATH . "includes/classes/db.php");
include(PUBLIC_PATH . "html/header.php");
 
?>

<div class="page-content" style="">
<div class="container" >
<div class="widget " style="">    
    
    <div class="widget-head text-center">Manufacturers</div>
    <div class="widget-body" >
        
        <div  class="">
            <table class="table table-striped table-hover table-condensed">
                    <thead style="font-size: 10px">
                        <tr> 
                            <td>#</td>
                            <td>Item Name</td>
                            <td>Manufacturer</td>
                            <td>Brand</td>
                            <td>Pack Length</td>
                            <td>Pack Width</td>
                            <td>Pack Height</td>
                            <td>Net Capacity</td>
                            <td>Carton Per Pallet</td>
                            <td>Quantity Per Pack</td>
                            <td>GTIN</td>
                            <td>Gross Capacity</td>
                        </tr>
                </thead>
                <tbody  style="font-size: 10px">
                    <?php
                            $qry_sel= "SELECT
                                            itminfo_tab.itm_id,
                                            itminfo_tab.itm_name,
                                            stakeholder.stkname,
                                            stakeholder_item.brand_name,
                                            stakeholder_item.stk_id AS brand_id,
                                            stakeholder.stkid AS manuf_id,
                                            stakeholder_item.pack_length,
                                            stakeholder_item.pack_width,
                                            stakeholder_item.pack_height,
                                            stakeholder_item.net_capacity,
                                            stakeholder_item.carton_per_pallet,
                                            stakeholder_item.quantity_per_pack,
                                            stakeholder_item.gtin,
                                            stakeholder_item.gross_capacity
                                        FROM
                                            itminfo_tab
                                        INNER JOIN stakeholder_item ON itminfo_tab.itm_id = stakeholder_item.stk_item
                                        INNER JOIN stakeholder ON stakeholder_item.stkid = stakeholder.stkid
                                        WHERE
                                            itminfo_tab.itm_id = ".$_REQUEST['prod_id']."  AND
stakeholder.stk_type_id = 3
                                        ORDER BY
                                            stakeholder.stkname ASC,
                                            stakeholder_item.brand_name ASC
                                         ";
//                                    echo $fqry_sel;exit;
                            $rsp  = mysql_query($qry_sel) or die();
                            $c=1;
                            while ($row= mysql_fetch_array($rsp)) {
                                    echo '<tr>';
                                        echo '<td>'.$c++.'</td>';
                                        echo '<td>'.$row['itm_name'].'</td>';
                                        echo '<td>'.$row['stkname'].'</td>';
                                        echo '<td>'.$row['brand_name'].'</td>';
                                        echo '<td>'.$row['pack_length'].'</td>';
                                        echo '<td>'.$row['pack_width'].'</td>';
                                        echo '<td>'.$row['pack_height'].'</td>';
                                        echo '<td>'.$row['net_capacity'].'</td>';
                                        echo '<td>'.$row['carton_per_pallet'].'</td>';
                                        echo '<td>'.$row['quantity_per_pack'].'</td>';
                                        echo '<td>'.$row['gtin'].'</td>';
                                        echo '<td>'.$row['gross_capacity'].'</td>';
                                    echo '</tr>';
                                    }

                            ?> 
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</div>