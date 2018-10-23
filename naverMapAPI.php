<?php

require_once 'env.php';

function pointToAddress($point)
{
    global $clientId, $clientSecret;

    $x = $point['x'];
    $y = $point['y'];

    $url = "https://openapi.naver.com/v1/map/reversegeocode?query={$x},{$y}";
    $is_post = false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = array();
    $headers[] = "X-Naver-Client-Id: ".$clientId;
    $headers[] = "X-Naver-Client-Secret: ".$clientSecret;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // echo "status_code:".$status_code."<br>";
    curl_close ($ch);

    if($status_code == 200) {
        // echo $response;
        $json = json_decode($response)->result;
        $address = $json->items[0]->address;

        // $x = $json->result->items[0]->point->x;
        // $y = $json->result->items[0]->point->y;

        // return Array("x" => $x, "y" => $y);
        return $address;
    } else {
        echo "Error 내용:".$response;
    }

}

function addressToPoint($address)
{
    global $clientId, $clientSecret;

    $encText = urlencode($address);
    $url = "https://openapi.naver.com/v1/map/geocode?query=" . $encText; // json
    // $url = "https://openapi.naver.com/v1/map/geocode.xml?query=".$encText; // xml

    $is_post = false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = array();
    $headers[] = "X-Naver-Client-Id: ".$clientId;
    $headers[] = "X-Naver-Client-Secret: ".$clientSecret;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // echo "status_code:".$status_code."<br>";
    curl_close ($ch);
    if($status_code == 200) {
        // echo $response;
        $json = json_decode($response);

        $x = $json->result->items[0]->point->x;
        $y = $json->result->items[0]->point->y;

        return Array("x" => $x, "y" => $y);

    } else {
        echo "Error 내용:".$response;
    }
}

function getPromisePoint()
{
    $x = 0;
    $y = 0;

    $count = func_num_args();

    for ( $i = 0; $i < $count; $i++ )
    {
        $x += func_get_arg($i)['x'];
        $y += func_get_arg($i)['y'];
    }

    $x /= $count;
    $y /= $count;

    return Array("x" => $x, "y" => $y);
}

