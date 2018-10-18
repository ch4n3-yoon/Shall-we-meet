<?php

function xss($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}


function arraySort(&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

function sanity_check($data) {
    return addslashes(preg_replace("/\'|\"|\)|\(|\`|\$/i", "", $data));
}
