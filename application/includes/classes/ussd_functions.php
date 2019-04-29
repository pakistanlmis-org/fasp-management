<?php

function calc_cb_ussd($on_this_date,$wh_id,$item_id=null){
        $qry = "    SELECT
                        ussd_sessions.stock_received,
                        ussd_sessions.stock_consumed,
                        ussd_sessions.stock_adjustment_p,
                        ussd_sessions.stock_adjustment_n,
                        ussd_sessions.item_id
                    FROM
                        ussd_sessions
                    INNER JOIN ussd_session_master ON ussd_sessions.ussd_master_id = ussd_session_master.pk_id
                    WHERE
                        ussd_session_master.wh_id = '$wh_id'
                        AND week_start_date <= '".$on_this_date."' ";
        if(!empty($item_id)){
            $qry .= " AND item_id = $item_id ";
        }
        //echo $qry;
        
        $result1 = mysql_query($qry);
        $temp_arr = $cb_arr = array();
        while($row = mysql_fetch_array($result1))
        {
            @$temp_arr[$row['item_id']]['rcv']   += $row['stock_received'];
            @$temp_arr[$row['item_id']]['cons']  += $row['stock_consumed'];
            @$temp_arr[$row['item_id']]['adj_p'] += $row['stock_adjustment_p'];
            @$temp_arr[$row['item_id']]['adj_n'] += $row['stock_adjustment_n'];
        }
        
        foreach($temp_arr as $item_id => $itm_data){
            $this_cb = 0;
            $this_cb = (!empty($itm_data['rcv'])?$itm_data['rcv']:0)
                                -(!empty($itm_data['cons'])?$itm_data['cons']:0)
                                +(!empty($itm_data['adj_p'])?$itm_data['adj_p']:0)
                                -(!empty($itm_data['adj_n'])?$itm_data['adj_n']:0);
            $cb_arr[$item_id] = $this_cb;
        }
        return $cb_arr;
}

function calc_ob_ussd($on_this_date,$wh_id,$item_id=null){
        $qry = "    SELECT
                        ussd_sessions.stock_received,
                        ussd_sessions.stock_consumed,
                        ussd_sessions.stock_adjustment_p,
                        ussd_sessions.stock_adjustment_n,
                        ussd_sessions.item_id
                    FROM
                        ussd_sessions
                    INNER JOIN ussd_session_master ON ussd_sessions.ussd_master_id = ussd_session_master.pk_id
                    WHERE
                        ussd_session_master.wh_id = '$wh_id'
                        AND week_start_date <  '".$on_this_date."' ";
        if(!empty($item_id)){
            $qry .= " AND item_id = $item_id ";
        }
        //echo $qry;
        
        $result1 = mysql_query($qry);
        $temp_arr = $cb_arr = array();
        while($row = mysql_fetch_array($result1))
        {
            @$temp_arr[$row['item_id']]['rcv']   += $row['stock_received'];
            @$temp_arr[$row['item_id']]['cons']  += $row['stock_consumed'];
            @$temp_arr[$row['item_id']]['adj_p'] += $row['stock_adjustment_p'];
            @$temp_arr[$row['item_id']]['adj_n'] += $row['stock_adjustment_n'];
        }
        
        foreach($temp_arr as $item_id => $itm_data){
            $this_cb = 0;
            $this_cb = (!empty($itm_data['rcv'])?$itm_data['rcv']:0)
                                -(!empty($itm_data['cons'])?$itm_data['cons']:0)
                                +(!empty($itm_data['adj_p'])?$itm_data['adj_p']:0)
                                -(!empty($itm_data['adj_n'])?$itm_data['adj_n']:0);
            $cb_arr[$item_id] = $this_cb;
        }
        return $cb_arr;
}

function phoneFormatting($requested_phone_number) {
    if ((strpos($requested_phone_number, '3')) == 1 && (strpos($requested_phone_number, '3') != FALSE)) {
        $requested_phone_number = preg_replace('/03/', '923', $requested_phone_number, 1);
        $position = 0;
    } else if ((strpos($requested_phone_number, '2')) == 1 && (strpos($requested_phone_number, '2') != FALSE)) {

        $requested_phone_number = preg_replace('/923/', '923', $requested_phone_number, 1);
        $position = 0;
    } else if ((strpos($requested_phone_number, '9')) == 1 && (strpos($requested_phone_number, '9') != FALSE)) {
        $requested_phone_number = preg_replace('/\+92/', '', $requested_phone_number, 1);
        $requested_phone_number = preg_replace('/923/', '923', $requested_phone_number, 1);
        $position = 0;
    } else if ((strpos($requested_phone_number, '92')) == 2 && (strpos($requested_phone_number, '92') != FALSE)) {

        $requested_phone_number = preg_replace('/00923/', '923', $requested_phone_number, 1);
        $position = 0;
    }
    $requested_phone_number = trim($requested_phone_number, " ");
    return $requested_phone_number;
}
?>