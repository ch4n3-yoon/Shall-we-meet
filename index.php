<?php

require_once 'error.php';
require_once 'naverMapAPI.php';
require_once 'lib.php';
require_once 'metro.php';
require_once 'env.php';

// 전달된 위치가 없으면 setPoint.php 로 이동
$array = $_REQUEST['addr'];
if (!isset($array))
{
    header('Location: /map/setPoint.php');
}


// 약속 장소의 위도, 경도를 구하는 코드
$code = "\$promisePoint = getPromisePoint(";

$points = [];
foreach ($array as $key => $val) {
    if (strlen($val) != 0)
        $points[] = "addressToPoint('" . sanity_check($val) . "')";
}
$code .= join(',', $points);
$code .= ");";

eval($code);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ㅇㄷ로가?</title>
    <script type="text/javascript" src="https://openapi.map.naver.com/openapi/v3/maps.js?clientId=<?php echo $clientId; ?>&submodules=geocoder"></script>
    <script type="text/javascript" src="https://openapi.map.naver.com/openapi/v3/maps.js?clientId=<?php echo $clientId; ?>&submodules=drawing"></script>
    <style>
        .container {
            width: 88%;
            margin: 0 auto;
        }

        .header {
            top: 32%;
            position: relative;
            margin-top: 1em;
            margin-bottom: 1em;
            padding-top: 1em;
            padding-bottom: 1em;
            background-color: #96dd3b;
            background-size:cover;
        }

        .title {
            font-family: 'Apple SD Gothic Neo', 'NanumGothic';
            text-align: center;
            text-decoration-style: double;

        }

    </style>
    <?php require_once 'bootstrap.php'; ?>
</head>
<body>
<div class="container">


    <div class="header">
        <h1 class="title">어디로가?</h1>
    </div>

    <p>
        <strong>
            <?php
            $array = $_REQUEST['addr'];
            echo join(',', $array);
            ?>
        </strong> 사이의 만날 장소
    </p>



    <?php
    $closeStation = getCloseStation($promisePoint);
    //$closeStationPoint = point($closeStation['ypoint_wgs'], $closeStationPoint['xpoint_wgs']);
    $closeStationPoint = Array('x' => $closeStation['ypoint_wgs'], 'y' => $closeStation['xpoint_wgs']);
    ?>
    <p>
        만날 주소 : <?php echo pointToAddress($promisePoint); ?>
    </p>
    <p>
        가장 가까운 역 : <?php echo $closeStation['line_num'] ?>(호)선
        <?php echo getCloseStation($promisePoint)['station_nm']; ?>역
        (<?php echo $closeStationPoint['x'] . ',' . $closeStationPoint['y']; ?>)
    </p>
    <br>

    <div id="map" style="width:100%;height:400px;"></div>
    <br>
</div>
<script type="text/javascript">

    let map = new naver.maps.Map('map', {
        zoomControl: true,
        zoomControlOptions: {
            style: naver.maps.ZoomControlStyle.LARGE,
            position: naver.maps.Position.TOP_RIGHT
        },
        mapTypeControl: true,
        zoom: 5
    });

    var marker = new naver.maps.Marker({
        position: new naver.maps.Point(<?php echo $promisePoint['x'] ?>, <?php echo $promisePoint['y']; ?>),
        map: map
    });

    var marker1 = new naver.maps.Marker({
        position: new naver.maps.Point(<?php echo $closeStationPoint['x'] ?>,<?php echo $closeStationPoint['y']; ?>),
        map: map
    });

    marker.setMap(map);
    marker1.setMap(map);

    var drawingManager = new naver.maps.drawing.DrawingManager({map: map});
    // 오버레이 추가
    var polygon = new naver.maps.Polygon({
        map: map,
        paths: [
            <?php

            $result = [];
            for ($i = 0; $i < count($array); $i++)
            {
                $point = xss($array[$i]);
                $code = "\$result[] = addressToPoint('{$point}');";
                eval($code);
            }

            // x 좌표로 주소 정렬
            arraySort($result, 'x');

            foreach ($result as $key => $value)
            {
                $distance = [];
                foreach ($result as $key1 => $value1)
                {
                    if ($key !== $key1)
                    {
                        $distance[$key1] = getDistance($value, $value1);
                    }
                }
                asort($distance);
                array_shift($distance);

//                print_r($tmp_result);
//                echo '/*[' . $tmp_result['x'] . ',' . $result[$i]['y'] . "],*/\n";
            }


            for ($i = 0; $i < count($result); $i++)
            {
                echo '[' . $result[$i]['x'] . ',' . $result[$i]['y'] . "],\n";
            }

            ?>


        ],
        fillColor: '#ff0000',
        fillOpacity: 0.4,
        strokeWeight: 2,
        strokeColor: '#ff0000'
    });

    drawingManager.addDrawing(polygon, naver.maps.drawing.DrawingMode.POLYGON);

</script>
</body>
</html>
