<?php
include_once("../includes/propis.php");
include_once("../classes/tzakaz.php");
include_once("../wcl_diaservice/tzgodacfg.php");
include_once("../wcl_diaservice/tzgodadoc.php");
include_once("../pdf/mpdf.php");
// include_once("../mpdf6/mpdf.php");

$error_status = 0;
$error_msg = '';
$path_to_template = '../templates/zgoda_template.html';

$options = ['options' => ['min_range' => 0]];

if (!file_exists($path_to_template)) {
   $error_msg = [
        'msg'   => 'шаблон не найден',
    ];
    $error_status = 404; 
}

$zakaz = 0;

$paramJSON = json_decode(file_get_contents("php://input"), TRUE);
if (isset($paramJSON['get_zakaz']) && trim($paramJSON['get_zakaz'] != '') 
  && (filter_var($paramJSON['get_zakaz'], FILTER_VALIDATE_INT, $options)) !== false) {
    $zakaz = intval($paramJSON['get_zakaz']);
} else {
    $error_msg = [
        'msg'   => 'заказ не найден',
    ];
    $error_status = 404;
}
if (isset($paramJSON['sign_img'])) {
    $image = $paramJSON['sign_img'];
} else {
    $error_msg = [
        'msg'   => 'подпись не принята',
    ];
    $error_status = 404;
}
$ip_notepad = '';
if (!isset($_SERVER['REMOTE_ADDR']) || ($ip_notepad = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) === false) {
    $error_msg = [
        'msg'   => 'неправильный адрес клиента',
    ];
    $error_status = 400;
}

if ($error_status == 0) {
    $conn = connect_to_db();
    if ($conn->IsConnected()) {
        $sql_str = "SELECT ID FROM ZGODACFG WHERE NOTEPAD='" . $ip_notepad . "' AND ZAKAZ=" . $zakaz . " AND ADMIN <> '' AND ADMIN IS NOT NULL";
        $id_cfg = GetFieldFromSql($conn, $sql_str, 0);
        if ($id_cfg) {
            $oper = new TZgodacfg();	
            $oper->conn = $conn;
            $oper->id = $id_cfg;
            $oper->Read();
            $oper->zakaz = 0;
            // $mess = $oper->Update();
            $mess = '';
            if ($mess !== '') {
                $error_msg = [
                    'msg'   => $mess,
                ];
                $error_status = 500;
            } else {
                $zak = new TZakaz();
                $zak->conn = $conn;
                $zak->zakaz = $zakaz;
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
                        'REPLACEZAKAZDATE', 'REPLACEDATESIGN', 'REPLACEPATIENTFIO', 'REPLACEPATIENTSIGN'
                    ], [
                        mb_convert_encoding('Заказ № '.$zakaz.' від '.$zak->zakdate, 'Windows-1251', 'UTF-8'), $zak->zakdate, $patient, $image
                        // iconv('utf-8', 'cp1251','Заказ № '.$zakaz.' від '.$zak->zakdate), $zak->zakdate, $patient
                        // 'Заказ № '.$zakaz.' від '
                    ], $file_template);
                    $temp_file = tempnam(sys_get_temp_dir(), 'zgoda_out');
                    $mpdf = new mPDF();
                    $mpdf->WriteHTML($file_out);
                    $mpdf->Output($temp_file);
                    $ss = file_get_contents($temp_file);
                    $zgoda_obj = new TZgodadoc();
                    $zgoda_obj->conn = $conn;
                    $zgoda_obj->zakaz = $zakaz;
                    $zgoda_obj->doc = base64_encode(gzcompress($ss, 9));
                    $mess = $zgoda_obj->Add();
                    if ($mess !== '') {
                        $error_msg = [
                            'msg'   => $mess,
                        ];
                        $error_status = 500;
                    } else {
                        $error_msg = [
                            'msg'   => 'подпись сохранена',
                        ];
                        $error_status = 200;
                    }
                }
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
}


http_response_code($error_status);
header('Content-Type: application/json; charset=utf-8');

echo json_encode($error_msg);
