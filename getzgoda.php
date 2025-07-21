<?php
include_once("../includes/propis.php");
include_once("../classes/tzakaz.php");
include_once("../wcl_diaservice/tzgodacfg.php");

function Zgoda($patient, $zakaz, $zakdate)
{
	$output = '<table border="0" cellpadding="0" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse"> 
        <tr>
            <td align="center"><span class="thirdline"><b>Заказ № '.$zakaz.' від '.$zakdate.'</b><br><br><b>Інформована добровільна згода пацієнта на проведення діагностики та лікування</b>
            <br><br><b>Шановний пацієнт!</b></span>
            </td>
        </tr>
        <tr>
            <td class= "patient" align="justify">
	                         
<br>Підписанням даної інформованої згоди Ви даєте ТОВ «Діасервіс», згоду на обробку Ваших персональних даних згідно Закону України «Про захист персональних даних» від 01 червня 2010 року № 2297- VI, та інших нормативно-правових актів України, які стосуються захисту персональних даних.  Ви так само підтверджуєте, що інформована згода після її підписання зберігається в електронній формі в архівах ТОВ «Діасервіс», а копія даної згоди є доказом факту наявності Вашої згоди на збір і обробку вказаних даних.
<br>
Також своїм підписом даю згоду на проведення (необхідне підкреслити):
<br>
- взяття венозної крові;
<br>
- взяття біологічного матеріалу методом ПЦР (із цервікального каналу, заднього зведення, уретри);
<br>
- взяття букального эскрібка;
<br>
- взяття бактерійного посіву.
<br>
Також своїм підписом підтверджую, що згоден(на) з записом діагнозу у висновку, ознайомлений(а) з правилами обстеження, методами лікування, можливими варіантами медичного втручання, ризиком виникнення ускладнень, а також з правилами перебування у медичному центрі «Діасервіс». Мені роз\'яснено, що я маю право відмовитись від медичного втручання або вимагати його припинення.
';
//if(getuserid()==90002)
/*
$output.='<br>Ваш&nbspлікар:&nbspПІБ__________________________________________&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspЛЗ__________________________________________<br>
<div align="right"><sup>(лікувальний заклад)</sup></div>
Відправити результат дослідження лікарю на e-mail&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspТак &#10066; Ні &#10066;<br>
<div align="left"><sup>(за наявності данних в сиситемі)</sup></div>';
*/
$output .= '<p>Текст даної інформаційної згоди, мною прочитаний і своїм підписом я підтверджую, що згоден(а) зі всіма пунктами документа, вміст даного документа мені доступно роз\'яснені і зрозумілі.</p><br>
    <div align="left">'.$zakdate.' р.</div>
    <div align="right"> 
        <div class="">
		    <canvas id="canvas" width="450" height="300"></canvas>
	    </div>
     <b>'.$patient.'</b></div>
    </td>
</tr></table>
<button onclick="clearCanvas()">Очистить</button>
<button onclick="saveDrawing()">Сохранить</button>'; 
	return $output;
}

$error_status = 0;
$error_msg = '';
$path_to_template = '../templates/zgoda_template.html';

// $options = ['options' => ['min_range' => 0]];
if (!file_exists($path_to_template)) {
   $error_msg = [
        'msg'   => 'шаблон не найден',
    ];
    $error_status = 404; 
}
$paramJSON = json_decode(file_get_contents("php://input"), TRUE);
// if (!isset($paramJSON['get_zakaz'])) {
//     $error_msg = [
//         'msg'   => 'неизвестный запрос',
//     ];
//     $error_status = 400;
// }
if ($error_status == 0) {
    //&& trim($paramJSON['zakaz'] != '') 
//  && (filter_var($paramJSON['zakaz'], FILTER_VALIDATE_INT, $options)) !== false) {
    // $zakaz = intval($paramJSON['zakaz']);

    $ip_notepad = '';
    if (isset($_SERVER['REMOTE_ADDR']) && ($ip_notepad = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) !== false) {

        $conn = connect_to_db();
        if ($conn->IsConnected()) {
            $sql_str = "SELECT ZAKAZ FROM ZGODACFG WHERE NOTEPAD='" . $ip_notepad . "' AND ZAKAZ IS NOT NULL AND ZAKAZ<>0 AND ADMIN <> '' AND ADMIN IS NOT NULL";
            $zakaz_db = GetFieldFromSql($conn, $sql_str, 0);
            if ($zakaz_db) {
                $zak = new TZakaz();
                $zak->conn = $conn;
                $zak->zakaz = $zakaz_db;
                if (!$zak->Read()) {
                    $error_msg = [
                        'msg'   => 'заказ не найден',
                    ];
                    $error_status = 404;
                } else {
                    $patient = $zak->a_s2;
                    if ($zak->a_s3 != '') $patient .= ' ' . $zak->a_s3;
                    if (strlen($zak->a_s3) == 1) $patient .= '.';
                    if ($zak->a_s4 != '') $patient .= ' ' . $zak->a_s4;
                    if (strlen($zak->a_s4) == 1) $patient .= '.';
                    
                    $file_template = file_get_contents($path_to_template);
                    $file_out = str_replace([
                        'REPLACEZAKAZDATE', 'REPLACEDATESIGN', 'REPLACEPATIENTFIO'
                    ], [
                        iconv('utf-8', 'cp1251','Заказ № '.$zak->zakkode.' від '.$zak->zakdate), $zak->zakdate, $patient
                    ], $file_template);
                    $error_msg = [
                        'msg'   => 'заказ найден',
                        'html'  => iconv('cp1251','utf-8', $file_out),
                        'zakaz' => $zak->zakkode
                        // 'patient'   => iconv('cp1251','utf-8',$patient),
                        // 'date'      => $zak->zakdate,
                        // 'head_zgoda'=> 'Заказ № '.$zak->zakkode.' від '.$zak->zakdate
                        // 'zakaz' => Zgoda(iconv('cp1251','utf-8',$patient), $zak->zakkode, $zak->zakdate)
                    ];
                    $error_status = 200;
                }

            } else {
                $error_msg = [
                    'msg'   => 'заказ не найден',
                ];
                $error_status = 404;
            }
        } else {
            $error_msg = [
                'msg'   => 'невозможно подключиться к базе данных',
            ];
            $error_status = 500;
        }
    } else {
        $error_msg = [
                'msg'   => 'неправильный адрес клиента',
            ];
            $error_status = 400;
    }
    }
http_response_code($error_status);
// header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

echo json_encode($error_msg);//, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);