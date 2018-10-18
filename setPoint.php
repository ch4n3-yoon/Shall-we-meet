<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>약속 장소 정하기</title>
    <?php require_once 'bootstrap.php'; ?>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>어디로가?</h1>
    </div>

    <div class="form-group">
        <form action="/map/" method="post" id="form">
            <div id="position">

                <p id="position_input_bar1">
                    <div class="row">
                        <div class="col-2">
                            위치1 :
                        </div>
                        <input type='text' class='form-control col-7' name='addr[]'>
                        <div class="col-1" onclick="javascript: alert('첫번째 위치는 삭제할 수 없습니다.');">
                            ❌
                        </div>
                    </div>
                </p>

            </div>
        </form>
    </div>

    <div>
        <button onclick="javascript: addPosition();" class="btn btn-primary">위치 추가</button>&nbsp;
        <button onclick="javascript: sendData();" class="btn btn-default">전송</button>
    </div>
</div>
<script type="text/javascript">

    let i = 1;

    let addPosition = async () => {

        i++;
        let position = `
        <p id="position_input_bar${i}">
            <div class="row" id="position_input_bar${i}">
                    <div class="col-2">
                        위치 :
                    </div>
                    <input type='text' class='form-control col-7' name='addr[]'>
                    <div class="col-1" onclick="javascript: deletePosition(${i});">
                        ❌
                    </div>
            </div>
        </p>`;

        $("#position").append( position );
    };

    let deletePosition = async (position_id) => {
        $(`#position_input_bar${position_id}`).remove();
    };

    let sendData = async () => {
        $('#form').submit();
    };

</script>
<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>
<!--autoload=false 파라미터를 이용하여 자동으로 로딩되는 것을 막습니다.-->
<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js?autoload=false"></script>
<script>
    //load함수를 이용하여 core스크립트의 로딩이 완료된 후, 우편번호 서비스를 실행합니다.
    daum.postcode.load(function(){
        new daum.Postcode({
            oncomplete: function(data) {
                // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분입니다.
                // 예제를 참고하여 다양한 활용법을 확인해 보세요.
            }
        }).open();
    });
</script>

</body>
</html>
<?php
session_start();
?>
