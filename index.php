<?php

require_once 'error.php';
require_once 'naverMapAPI.php';
require_once 'lib.php';
require_once 'metro.php';
require_once 'env.php';

$appkey = '9edfd445d55cc9c2ca654d5c2a2717cb';
// 전달된 위치가 없으면 setPoint.php 로 이동
$addresses = $_REQUEST['addr'];
if (!isset($addresses))
{
    header('Location: /map/setPoint.php');
}


// 약속 장소의 위도, 경도를 구하는 코드
$code = "\$promisePoint = getPromisePoint(";

$points = [];
foreach ($addresses as $key => $val) {
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

    <script src="//cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="/map/assets/css/index.css">

</head>
<body>
<div class="container_local">


    <div class="header">
        <h1 align="center"><b class="rainbow gungseo">어디로가 써-비쓰</b></h1>
    </div>

    <ul class="menu" style="z-index: 100;">
        <li class="nav-li active"><a class="nav-a" href="default.asp">어디로가?</a></li>
        <li class="nav-li"><a class="nav-a" href="news.asp">설립 이념</a></li>
        <li class="nav-li"><a class="nav-a" href="contact.asp">알고리즘</a></li>
        <li class="nav-li"><a class="nav-a" href="https://www.gg.go.kr/humanrights">경기도민 인권 향상을 위한 발악</a></li>
    </ul>

    <div class="print-points">
        <?php
        $array = $_REQUEST['addr'];
        for ($i = 0; $i < count($array); $i++)
        {
            echo "<a class=\"waves-effect waves-light btn white\">{$array[$i]}</a>&nbsp;";
//            echo "<a class=\"btn-floating btn-large waves-effect waves-light red\">{$array[$i]}</a>&nbsp;";
        }
        ?>
        사이의 만날 장소
    </div>



    <?php
    $closeStation = getCloseStation($promisePoint);
    //$closeStationPoint = point($closeStation['ypoint_wgs'], $closeStationPoint['xpoint_wgs']);
    $closeStationPoint = Array('x' => $closeStation['ypoint_wgs'], 'y' => $closeStation['xpoint_wgs']);
    ?>
    <div class="print-points">
        만날 주소 : <a class="waves-effect waves-light btn" style="color: white; z-index: 5;"><?php echo pointToAddress($promisePoint); ?></a>
        (<?php echo $promisePoint['y'] . ', ' . $promisePoint['x']; ?>)
        <br><br>
        가장 가까운 역 : <?php echo $closeStation['line_num'] ?>(호)선
        <?php echo getCloseStation($promisePoint)['station_nm']; ?>역
        <!-- (<?php echo $closeStationPoint['x'] . ',' . $closeStationPoint['y']; ?>) -->
    </div>
    <br>

    <br>

    <div class="map_wrap">
        <div id="map"></div>
        <ul id="category">
            <li id="BK9" data-order="0" class="category">
                <span class="category_bg bank"></span>
                은행
            </li>
            <li id="MT1" data-order="1" class="category">
                <span class="category_bg mart"></span>
                마트
            </li>
            <li id="PM9" data-order="2" class="category">
                <span class="category_bg pharmacy"></span>
                약국
            </li>
            <li id="OL7" data-order="3" class="category">
                <span class="category_bg oil"></span>
                주유소
            </li>
            <li id="CE7" data-order="4" class="category">
                <span class="category_bg cafe"></span>
                카페
            </li>
            <li id="CS2" data-order="5" class="category">
                <span class="category_bg store"></span>
                편의점
            </li>
        </ul>
    </div>

    <div class="restaurant">

    </div>
</div>
<script type="text/javascript" src="http://dapi.kakao.com/v2/maps/sdk.js?appkey=<?php echo $appkey; ?>&libraries=services"></script>
<script type="text/javascript">

    // 마커를 클릭했을 때 해당 장소의 상세정보를 보여줄 커스텀오버레이입니다
    let placeOverlay = new daum.maps.CustomOverlay({zIndex:1}),
        contentNode = document.createElement('div'), // 커스텀 오버레이의 컨텐츠 엘리먼트 입니다
        markers = [], // 마커를 담을 배열입니다
        currCategory = ''; // 현재 선택된 카테고리를 가지고 있을 변수입니다

    let mapContainer = document.getElementById('map'), // 지도를 표시할 div
        mapOption = {
            center: new daum.maps.LatLng(<?php echo $promisePoint['y'] . ', ' . $promisePoint['x']; ?>), // 지도의 중심좌표
            level: 10 // 지도의 확대 레벨
        };

    // 지도를 생성합니다
    let map = new daum.maps.Map(mapContainer, mapOption);

    // 장소 검색 객체를 생성합니다
    let ps = new daum.maps.services.Places(map);

    // 지도에 idle 이벤트를 등록합니다
    daum.maps.event.addListener(map, 'idle', searchPlaces);

    // 커스텀 오버레이의 컨텐츠 노드에 css class를 추가합니다
    contentNode.className = 'placeinfo_wrap';

    // 커스텀 오버레이의 컨텐츠 노드에 mousedown, touchstart 이벤트가 발생했을때
    // 지도 객체에 이벤트가 전달되지 않도록 이벤트 핸들러로 daum.maps.event.preventMap 메소드를 등록합니다
    addEventHandle(contentNode, 'mousedown', daum.maps.event.preventMap);
    addEventHandle(contentNode, 'touchstart', daum.maps.event.preventMap);


    // 커스텀 오버레이 컨텐츠를 설정합니다
    placeOverlay.setContent(contentNode);

    // 각 카테고리에 클릭 이벤트를 등록합니다
    addCategoryClickEvent();

    // 주소-좌표 변환 객체를 생성합니다
    let geocoder = new daum.maps.services.Geocoder();





    /* 역 정보를 지도에 표시하는 코드 */
    let imageSrc = 'http://ch4n3.tk:8080/map/marker/station.png', // 마커이미지의 주소입니다
        imageSize = new daum.maps.Size(48, 64), // 마커이미지의 크기입니다
        imageOption = {offset: new daum.maps.Point(27, 69)}; // 마커이미지의 옵션입니다. 마커의 좌표와 일치시킬 이미지 안에서의 좌표를 설정합니다.

    // 마커의 이미지정보를 가지고 있는 마커이미지를 생성합니다
    let markerImage = new daum.maps.MarkerImage(imageSrc, imageSize, imageOption),
        markerPosition = new daum.maps.LatLng(<?php echo $closeStationPoint['y'] ?>,
            <?php echo $closeStationPoint['x'] ?>); // 마커가 표시될 위치입니다

    // 마커를 생성합니다
    let marker = new daum.maps.Marker({
        position: markerPosition,
        image: markerImage // 마커이미지 설정
    });

    // 마커가 지도 위에 표시되도록 설정합니다
    marker.setMap(map);



    /* 역 정보를 지도에 표시하는 코드 */

    let marker1 = new daum.maps.Marker(), // 클릭한 위치를 표시할 마커입니다
        infowindow = new daum.maps.InfoWindow({zindex:1}); // 클릭한 위치에 대한 주소를 표시할 인포윈도우입니다
    // 현재 지도 중심좌표로 주소를 검색해서 지도 좌측 상단에 표시합니다

    function displayCenterInfo(result, status) {
        if (status === daum.maps.services.Status.OK) {
            var infoDiv = document.getElementById('centerAddr');

            for(var i = 0; i < result.length; i++) {
                // 행정동의 region_type 값은 'H' 이므로
                if (result[i].region_type === 'H') {
                    infoDiv.innerHTML = result[i].address_name;
                    break;
                }
            }
        }
    }

    function searchAddrFromCoords(coords, callback) {
        // 좌표로 행정동 주소 정보를 요청합니다
        geocoder.coord2RegionCode(coords.getLng(), coords.getLat(), callback);
    }

    searchAddrFromCoords(map.getCenter(), displayCenterInfo);

    // 엘리먼트에 이벤트 핸들러를 등록하는 함수입니다
    function addEventHandle(target, type, callback) {
        if (target.addEventListener) {
            target.addEventListener(type, callback);
        } else {
            target.attachEvent('on' + type, callback);
        }
    }

    // 카테고리 검색을 요청하는 함수입니다
    function searchPlaces() {
        if (!currCategory) {
            return;
        }

        // 커스텀 오버레이를 숨깁니다
        placeOverlay.setMap(null);

        // 지도에 표시되고 있는 마커를 제거합니다
        removeMarker();

        ps.categorySearch(currCategory, placesSearchCB, {useMapBounds:true});
    }

    // 장소검색이 완료됐을 때 호출되는 콜백함수 입니다
    function placesSearchCB(data, status, pagination) {
        if (status === daum.maps.services.Status.OK) {

            // 정상적으로 검색이 완료됐으면 지도에 마커를 표출합니다
            displayPlaces(data);
        } else if (status === daum.maps.services.Status.ZERO_RESULT) {
            // 검색결과가 없는경우 해야할 처리가 있다면 이곳에 작성해 주세요

        } else if (status === daum.maps.services.Status.ERROR) {
            // 에러로 인해 검색결과가 나오지 않은 경우 해야할 처리가 있다면 이곳에 작성해 주세요

        }
    }

    // 지도에 마커를 표출하는 함수입니다
    function displayPlaces(places) {

        // 몇번째 카테고리가 선택되어 있는지 얻어옵니다
        // 이 순서는 스프라이트 이미지에서의 위치를 계산하는데 사용됩니다
        let order = document.getElementById(currCategory).getAttribute('data-order');


        for ( var i=0; i<places.length; i++ ) {

            // 마커를 생성하고 지도에 표시합니다
            var marker = addMarker(new daum.maps.LatLng(places[i].y, places[i].x), order);

            // 마커와 검색결과 항목을 클릭 했을 때
            // 장소정보를 표출하도록 클릭 이벤트를 등록합니다
            (function(marker, place) {
                daum.maps.event.addListener(marker, 'click', function() {
                    displayPlaceInfo(place);
                });
            })(marker, places[i]);
        }
    }

    // 마커를 생성하고 지도 위에 마커를 표시하는 함수입니다
    function addMarker(position, order) {
        let imageSrc = 'http://t1.daumcdn.net/localimg/localimages/07/mapapidoc/places_category.png', // 마커 이미지 url, 스프라이트 이미지를 씁니다
            imageSize = new daum.maps.Size(27, 28),  // 마커 이미지의 크기
            imgOptions =  {
                spriteSize : new daum.maps.Size(72, 208), // 스프라이트 이미지의 크기
                spriteOrigin : new daum.maps.Point(46, (order*36)), // 스프라이트 이미지 중 사용할 영역의 좌상단 좌표
                offset: new daum.maps.Point(11, 28) // 마커 좌표에 일치시킬 이미지 내에서의 좌표
            },
            markerImage = new daum.maps.MarkerImage(imageSrc, imageSize, imgOptions),
            marker = new daum.maps.Marker({
                position: position, // 마커의 위치
                image: markerImage
            });

        marker.setMap(map); // 지도 위에 마커를 표출합니다
        markers.push(marker);  // 배열에 생성된 마커를 추가합니다

        return marker;
    }

    // 지도 위에 표시되고 있는 마커를 모두 제거합니다
    function removeMarker() {
        for ( var i = 0; i < markers.length; i++ ) {
            markers[i].setMap(null);
        }
        markers = [];
    }

    // 클릭한 마커에 대한 장소 상세정보를 커스텀 오버레이로 표시하는 함수입니다
    function displayPlaceInfo (place) {
        var content = '<div class="placeinfo">' +
            '   <a class="title" href="' + place.place_url + '" target="_blank" title="' + place.place_name + '">' + place.place_name + '</a>';

        if (place.road_address_name) {
            content += '    <span title="' + place.road_address_name + '">' + place.road_address_name + '</span>' +
                '  <span class="jibun" title="' + place.address_name + '">(지번 : ' + place.address_name + ')</span>';
        }  else {
            content += '    <span title="' + place.address_name + '">' + place.address_name + '</span>';
        }

        content += '    <span class="tel">' + place.phone + '</span>' +
            '</div>' +
            '<div class="after"></div>';

        contentNode.innerHTML = content;
        placeOverlay.setPosition(new daum.maps.LatLng(place.y, place.x));
        placeOverlay.setMap(map);
    }


    // 각 카테고리에 클릭 이벤트를 등록합니다
    function addCategoryClickEvent() {
        var category = document.getElementById('category'),
            children = category.children;

        for (var i=0; i<children.length; i++) {
            children[i].onclick = onClickCategory;
        }
    }

    // 카테고리를 클릭했을 때 호출되는 함수입니다
    function onClickCategory() {
        var id = this.id,
            className = this.className;

        placeOverlay.setMap(null);

        if (className === 'on') {
            currCategory = '';
            changeCategoryClass();
            removeMarker();
        } else {
            currCategory = id;
            changeCategoryClass(this);
            searchPlaces();
        }
    }

    // 클릭된 카테고리에만 클릭된 스타일을 적용하는 함수입니다
    function changeCategoryClass(el) {
        var category = document.getElementById('category'),
            children = category.children,
            i;

        for ( i=0; i<children.length; i++ ) {
            children[i].className = '';
        }

        if (el) {
            el.className = 'on';
        }
    }


    // 주소로 좌표를 검색합니다
    // 만날 장소를 Marker를 사용해서 표시해줍니다.
    geocoder.addressSearch('<?php echo pointToAddress($promisePoint); ?>', async (result, status) => {

        // 정상적으로 검색이 완료됐으면
        if (status === daum.maps.services.Status.OK) {

            let coords = new daum.maps.LatLng(<?php echo $promisePoint['y'] . ', ' . $promisePoint['x']; ?>);

            // 결과값으로 받은 위치를 마커로 표시합니다
            let marker = new daum.maps.Marker({
                map: map,
                position: coords
            });

            // 인포윈도우로 장소에 대한 설명을 표시합니다
            let infowindow = new daum.maps.InfoWindow({
                content: '<div style="width:150px;text-align:center;padding:6px 0;">여기서만나!</div>'
            });
            infowindow.open(map, marker);

            // 지도의 중심을 결과값으로 받은 위치로 이동시킵니다
            map.setCenter(coords);
        }
    });



    /*
    입력한 장소들을 Polyline으로 연결해주는 코드.
    */

    let selectedPoints = [<?php

        $positionCount = count($addresses);
        $points = [];

        for ($i = 0; $i < $positionCount; $i++) {
            $points[$i] = addressToPoint($addresses[$i]);
        }

        for ($i = 0; $i < $positionCount; $i++){
            echo "new daum.maps.LatLng(";
            echo $points[$i]['y'];
            echo ",";
            echo $points[$i]['x'];
            echo "),";
        }

        echo "new daum.maps.LatLng(";
        echo $points[0]['y'];
        echo ',';
        echo $points[0]['x'];
        echo '),';

        ?>];

    new daum.maps.Polyline({
        map: map, // 선을 표시할 지도입니다
        path: [selectedPoints], // 선을 구성하는 좌표 배열입니다 클릭한 위치를 넣어줍니다
        strokeWeight: 3, // 선의 두께입니다
        strokeColor: '#db4040', // 선의 색깔입니다
        strokeOpacity: 1, // 선의 불투명도입니다 0에서 1 사이값이며 0에 가까울수록 투명합니다
        strokeStyle: 'solid' // 선의 스타일입니다
    });


    // 입력된 장소와 약속 장소를 Polyline으로 연결해주는 코드
    function connectToPromisePoint(points) {
        let promisePoint = new daum.maps.LatLng(<?php echo $promisePoint['y'] . ', ' . $promisePoint['x']; ?>);

        for (let i = 0; i < points.length; i++) {
            new daum.maps.Polyline({
                map: map, // 선을 표시할 지도입니다
                path: [promisePoint, points[i]], // 선을 구성하는 좌표 배열입니다 클릭한 위치를 넣어줍니다
                strokeWeight: 3, // 선의 두께입니다
                strokeColor: '#5cdb54', // 선의 색깔입니다
                strokeOpacity: 1, // 선의 불투명도입니다 0에서 1 사이값이며 0에 가까울수록 투명합니다
                strokeStyle: 'solid' // 선의 스타일입니다
            });
        }
    }

    connectToPromisePoint(selectedPoints);


    function showDistance(content, position) {

        if (distanceOverlay) { // 커스텀오버레이가 생성된 상태이면

            // 커스텀 오버레이의 위치와 표시할 내용을 설정합니다
            distanceOverlay.setPosition(position);
            distanceOverlay.setContent(content);

        } else { // 커스텀 오버레이가 생성되지 않은 상태이면

            // 커스텀 오버레이를 생성하고 지도에 표시합니다
            distanceOverlay = new daum.maps.CustomOverlay({
                map: map, // 커스텀오버레이를 표시할 지도입니다
                content: content,  // 커스텀오버레이에 표시할 내용입니다
                position: position, // 커스텀오버레이를 표시할 위치입니다.
                xAnchor: 0,
                yAnchor: 0,
                zIndex: 3
            });
        }
    }

    
    // 선택된 장소에 동그라미를 그리는 코드
    function displayCircleDot(position, distance) {

        // 클릭 지점을 표시할 빨간 동그라미 커스텀오버레이를 생성합니다
        var circleOverlay = new daum.maps.CustomOverlay({
            content: '<span class="dot"></span>',
            position: position,
            zIndex: 1
        });

        // 지도에 표시합니다
        circleOverlay.setMap(map);

        if (distance > 0) {
            // 클릭한 지점까지의 그려진 선의 총 거리를 표시할 커스텀 오버레이를 생성합니다
            var distanceOverlay = new daum.maps.CustomOverlay({
                content: '<div class="dotOverlay">거리 <span class="number">' + distance + '</span>m</div>',
                position: position,
                yAnchor: 1,
                zIndex: 2
            });

            // 지도에 표시합니다
            distanceOverlay.setMap(map);
        }

        // 배열에 추가합니다
        // dots.push({circle:circleOverlay, distance: distanceOverlay});
    }

    



</script>



<!-- 맛집을 검색하기 위한 스크립트 코드 -->
<script type="text/javascript">

    let address = "<?php echo pointToAddress($promisePoint); ?>";

    let parseAddress = async (address) => {
        let addressArray = address.split(' ');
        addressArray[addressArray.length - 1] = '';
        return addressArray.join(' ');
    };

    let search = async (query) => {
        const xhr = new XMLHttpRequest();
        const url = '/map/lib/daumWebSearch.php';
        let params = '?query='+query;
        await xhr.open('GET', url+params, false);
        await xhr.send(params);
        return JSON.parse(xhr.response);
    };

    let main = async () => {
        let html = '';

        let resultArray = (await search(await parseAddress(address) + "맛집"))['documents'];

        for (let i = 0; i < resultArray.length; i++) {
            let title = resultArray[i]['title'];
            let link = resultArray[i]['url'];
            let temporary_code = `<div><h3><a href='${link}'>${title}</a></h3></div>`;
            html += temporary_code;
        }

        $('.restaurant').append(html);
    };

    main();

</script>

</body>
</html>
