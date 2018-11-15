<?php

$restapi_app_key = 'fe88a12a50579dcf7acda76498df804e';

function searchDaum($search)
{
    $restapi_app_key = 'fe88a12a50579dcf7acda76498df804e';

    $url = 'https://dapi.kakao.com/v2/search/web';
    $data = "query=".urlencode($search);

    $is_post = false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    $headers = array();
    $headers[] = "Authorization: KakaoAK " . $restapi_app_key;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($status_code == 200) {
        echo $response;
    } else {
        echo "Error 내용:".$response;
    }
}

$query = $_REQUEST['query'];
header('Content-type: application/json');
echo searchDaum($query);
