<?php

$app_key = '9edfd445d55cc9c2ca654d5c2a2717cb';

function searchDaum($search)
{
    global $app_key;

    $url = 'https://dapi.kakao.com/v2/search/web';
    $data = "query=".urlencode($search);

    $is_post = true;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = array();
    $hearers[] = "Authorization: KakaoAK ${app_key}";

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // echo "status_code:".$status_code."<br>";
    curl_close($ch);
    if($status_code == 200) {
         echo $response;
    } else {
        echo "Error 내용:".$response;
    }
}

searchDaum("과천시 막계동 맛집");