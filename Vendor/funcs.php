<?php
function ajaxRender($status, $info, $data = '') {
    $json ['status'] = $status;
    $json ['info'] = $info;
    $json ['data'] = $data;
    header ( 'Content-Type:text/html; charset=utf-8' );
    echo json_encode ( $json );
    exit ();
}
function check_empty($str, $status = 100001, $info = '', $data = null) {
    if (empty ( $str )) {
        ajaxRender ( $status, $info, $data );
    }
    return $str;
}