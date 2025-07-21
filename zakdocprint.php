<?

include_once("../includes/propis.php");
include_once("../classes/tzakaz.php");
include_once("../wcl_diaservice/dia_include.php");
include_once("../wcl_diaservice/gen_result_inc.php");
include_once("../adodb212/tohtml.inc.php");
include_once("../wcl_diaservice/tzgodacfg.php"); //zgoda
  include_once("qrlib.php");

$userid = 101107;
$TimeWork=20;
if (@strlen($ZAKAZ) == 0) $ZAKAZ = 0;
if (@strlen($PAGE) == 0) $PAGE = 3;
if (@strlen($PRINT) == 0) $PRINT = 1;



function ZakazHead($patient, $filial, $zakaz, $zakdate, $age, $pol, $vrach, $vydat='',$addr='',$tel='',$prim='',$page=1,$email='')
{
	$output .= '<table border="0" cellpadding="0" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse"> 
	                   <tr>
	                         <td colspan = 6 align="center"><span class="thirdline"><b>Заказ № '.$zakaz.' від '.$zakdate.'</b></span></td>
	                   </tr>
	                   <tr>
	                         <td class= "patient" colspan = 6>&nbsp </td>
	                   </tr>
	                   <tr>
	                         <td class= "patient" width="15%"><b>П.І.Б.:</b></td>
	                         <td class= "patient" width="30%">'.$patient.'</td>
	                         <td class= "patient" width="15%"><b>Пункт забору:</b> </td>
	                         <td class= "patient" width="30%">'.$filial.'</td>
	                   </tr>
	                   <tr>
	                         <td class= "patient" width="15%"><b>Вік:</b> </td>
	                         <td class= "patient" width="30%">'.$age.'</td>
	                         <td class= "patient" width="15%"><b>Пункт видачі:</b> </td>
	                         <td class= "patient" width="30%">'.($vydat == '' ? $filial : $vydat).'</td>
	                   </tr>
	                   <tr>
	                         <td class= "patient" width="15%" ><b>Стать:</b></td>
	                         <td class= "patient" width="30%">'.$pol.'</td>';
  if($page ==1)	 
	 $output .=     '<td class= "patient" width="15%"><b>&nbsp</b></td>
	                         <td class= "patient" width="30%">&nbsp</td>';
  else
	 $output .=     '<td class= "patient" width="15%"><b>Лікар</b></td>
	                         <td class= "patient" width="30%">'.$vrach.'</td>';
	 $output .='</tr>
	                   <tr>
	                         <td class= "patient" width="15%"><b>Телефон:</b> </td>
	                         <td class= "patient" width="30%">'.$tel.'</td>
	                         <td class= "patient" width="15%"><b>Email:</b> </td>
	                         <td class= "patient" width="30%">'.$email.'</td>
	                   </tr>';
	 $output .= '<tr>
	                         <td class= "patient" colspan = 6 align="left"><b>Адреса:</b> '.$addr.'</td>
	                   </tr>';
	 if($page==2) 
	   $output .= '<tr>
	                         <td class= "patient" colspan = 6 align="left"><b>Примітки '.$prim.'</b></td>
	                   </tr>';
	 $output.= '<tr>
	                         <td class= "patient" colspan = 6>&nbsp </td>
	                   </tr>
	                   </table>';            
  return $output;
}

function ZakazSp($sp,$srok=false)
{
	$output .= '<table border="1" cellpadding="2" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse"> ';
	
	 $output .= "
	 <tr>
	     <th>№ п/п</th>
	     <th>Код</th>
	     <th>Найменування</th>
	     <th>Кол-во</th>
	     <th>Ціна, грн</th>
	     <th>Знижка, %</th>
	     <th>Ціна зі знижкою, грн</th>
	     <th>Сума, грн</th>";
	 if($srok) $output .= "<th>Орієнтовне виконання</th>";
	 $output .="</tr>";
	 $i=0; $sum = 0;
	 
	 for($i=0; count($sp) > $i; $i++)
	 {
	 	$sum = $sum + $sp[$i][7];
		$s ='<tr>
		          <td class= "patient" width="5%" align="left" valign="top">'. ($i+1).'</td> 
		          <td class= "patient" width="8%" align="left" valign="top">'.$sp[$i][0].'</td>
		          <td class= "patient" width="50%" align="left" valign="top">'.$sp[$i][1].'</td>
		          <td class= "patient" width="5%" align="center" valign="top">'. $sp[$i][2]. '</td>
	             <td class= "patient" width="5%" align="center" valign="top">'.$sp[$i][4]. '</td> 
	             <td class= "patient" width="5%" align="center" valign="top">'.$sp[$i][5]. '</td> 
	             <td class= "patient" width="15%" align="right" valign="top">'.number_format($sp[$i][6], 2). '</td> 
	             <td class= "patient" width="15%" align="right" valign="top">'.number_format($sp[$i][7], 2). '</td>'; 
	   if($srok) {
	     if($sp[$i][8] == '0000-00-00 00:00:00') $sd=''; else $sd=date('d.m.Y', strtotime($sp[$i][8]));
	   	$s .= '<td class= "patient" width="15%" align="right" valign="top">'.$sd. '</td>';
	   }        
	   $output .= $s.'</tr>';;
	 }
	 $colsp=7;
	 $output .= '<tr>
	                      <td class= "patient" colspan='.$colsp.' align="left" valign="top"><b>Всього до сплати</b></td>
	                      <td class= "patient" align="right" valign="top"><b>'.number_format($sum, 2). '</b></td>
	                    </tr>';
	 $output .= '</table>';
	 return $output;
	
}

function PodpisAdm()
{
	$output = '<div class="foot">
	                   <table border="0" cellpadding="2" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse"> ';
	$output .= '<tr><td class= "patient"><b>Відскануйте QR-код та отримайте результат аналізів</b> </td></tr>
	                    <tr><td class= "patient">&nbsp </td></tr>
	                    <tr><td class= "tbl">&nbsp </td></tr>
	                    <tr><td class= "patient">Адміністратор _______________________________ </td></tr>';
	$output .= '</table></div>';
  return $output;
}

function PodpisAdm1($patient)
{
	$output = '<div class="foot">
	                   <table border="0" cellpadding="2" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse"> ';
	$output .= '<tr><td class= "patient">&nbsp </td></tr>
	                    <tr><td class= "patient">Всі данні внесено вірно (П.І.Б., вік, стать., телефон, email, перелік досліджень) </td></tr>
	                    <tr><td class= "tbl">&nbsp </td></tr>
	                    <tr><td class= "patient">Пацієнт _______________________________'.$patient.' </td></tr>';
	$output .= '</table></div>';
  return $output;
}

function SetHeadNapr($barcode, $barcodemat)
{
   $output = '<div class="pagebreak"><div class="header" align="center">
                      <table border="0" cellpadding="0" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse" >
                          <tr>
                            <td rowspan = "4" valign="center" align="left"><div class="pict" ><img src="barcode.php?BARCODE='.$barcodemat.'" width="70%" height="70%" alt=""></div></td>
                            <td align="center"><span class="firstline">Бланк-замовлення для лабораторії</span></td>
                            <td width="15%" rowspan="4" align="center"><div class="pict" ><img src="barcode.php?BARCODE='.$barcode.'" width="70%" height="70%" alt=""></div></td> 
                          </tr>
                          <tr>
                            <td align="center"><span class="secline">&nbsp</span></td>
                          </tr>
                          <tr>
                            <td align="center"><span class="thirdline">&nbsp</span></td>
                          </tr>
                          <tr>
                            
                           <td align="center"><span class="fourline">&nbsp<br>&nbsp</span></td>
                         </tr> 
                       </table>
                       </div>';

   return $output;

}

function SetHeadPZ($conn, $lab, $barcode)
{
	$rs = $conn->Execute("SELECT LABNAME, DOZVIL, ADDR, TEL, WEB, FILELOGO FROM LABORATORY WHERE ID=$lab");
   $output = '<div class="pagebreak"><div class="header" align="center">
                      <table border="0" cellpadding="0" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse" >
                          <tr>
                            <td rowspan = "4" valign="center" align="left"><div class="pict" ><img src="altey/'.$rs->fields[5].'" width="100%" height="70%" alt=""> </div></td>
                            <td align="center"><span class="firstline">Список материалів</span></td>
                            <td width="15%" rowspan="4" align="center"><div class="pict" >'.($barcode != '' ? '<img src="barcode.php?BARCODE='.$barcode.'" width="70%" height="70%" alt="">' : '&nbsp').'</div></td> 
                          </tr>
                          <tr>
                            <td align="center"><span class="secline">&nbsp</span></td>
                          </tr>
                          <tr>
                            <td align="center"><span class="thirdline">&nbsp</span></td>
                          </tr>
                          <tr>
                            
                           <td align="center"><span class="fourline">&nbsp</span></td>
                         </tr> 
                       </table>
                       </div>';

   return $output;
}

function SetMaterial($conn, $rs)
{
   $output .= '<table border="1" cellpadding="1" cellspacing="1" bordercolor="black" width="100%" style="border-collapse: collapse" >';
   while(!$rs->EOF)
	{      
	  $output .= '<tr>
	                       <td class= "patient"><b>'.$rs->fields[2].'</b></td>
	                       <td class= "patient" colspan = "3"><b>'.$rs->fields[1].'<b></td>
	                     </tr>';
   	$sql = "SELECT T.NAZVANTOV, S.KOLICHFACT, E.A_S2 AS EDIZM 
   	             FROM ZAKAZSP S, TOVARY T, UNIPROPS E 
   	             WHERE S.TOVAR = T.TOVAR AND S.FATHER = ".$rs->fields[0]." AND S.EDIZM = E.PROPCNT AND T.PRIZNAK = 101815 AND S.KOLICHFACT <> 0";
   	$cs=$conn->Execute($sql);
   	if(!$cs->EOF)
   	{
			$output .= '<tr>
			                       <td class= "patient">&nbsp</td>
			                       <td class= "patient" colspan = "3"><b>Пробирки<b></td>
			                    </tr>';
			$i=1;
   		while(!$cs->EOF)
   		{
				$output .= '<tr>
				                       <td class= "patient">'.$i.'</td>
				                       <td class= "patient" >'.$cs->fields[0].'</td>
				                       <td class= "patient" >'.$cs->fields[1].'</td>
				                       <td class= "patient" >'.$cs->fields[2].'</td>
				                    </tr>';
   			$cs->MoveNext();
   			$i++;
   		}
   	}
   	$sql = "SELECT T.NAZVANTOV, S.KOLICHFACT, E.A_S2 AS EDIZM 
   	             FROM ZAKAZSP S, TOVARY T, UNIPROPS E 
   	             WHERE S.TOVAR = T.TOVAR AND S.FATHER = ".$rs->fields[0]." AND S.EDIZM = E.PROPCNT AND T.PRIZNAK <> 101815 AND S.KOLICHFACT <> 0";
   	$cs=$conn->Execute($sql);
   	if(!$cs->EOF)
   	{
			$output .= '<tr>
			                       <td class= "patient">&nbsp</td>
			                       <td class= "patient" colspan = "3"><b>Другие<b></td>
			                    </tr>';
			$i=1;
   		while(!$cs->EOF)
   		{
				$output .= '<tr>
				                       <td class= "patient">'.$i.'</td>
				                       <td class= "patient" >'.$cs->fields[0].'</td>
				                       <td class= "patient" >'.$cs->fields[1].'</td>
				                       <td class= "patient" >'.$cs->fields[2].'</td>
				                    </tr>';
   			$cs->MoveNext();
   			$i++;
   		}
   	}
		$rs->MoveNext();
	}
	$output .= '</table></div>';
	return $output;
}

function Zgoda($patient, $zakaz, $zakdate)
{
	$output .= '<table border="0" cellpadding="0" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse"> 
	                   <tr>
	                         <td align="center"><span class="thirdline"><b>Заказ № '.$zakaz.' від '.$zakdate.'</b><br><br><b>Інформована добровільна згода пацієнта на проведення діагностики та лікування</b>
	                         <br><br><b>Шановний пацієнт!</b></span></td>
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
$output.='<p>Текст даної інформаційної згоди, мною прочитаний і своїм підписом я підтверджую, що згоден(а) зі всіма пунктами документа, вміст даного документа мені доступно роз\'яснені і зрозумілі.</p>
</p><br><div align="left">'.$zakdate.' р.</div><div align="right"> ______________________________<b>'.$patient.'</b></div>
                             </td>
	                   </tr></table>'; 
	return $output;
}

function SetLabHead($conn, $lab, $zak, $lang='ua')
{
	
	$rs = $conn->Execute("SELECT LABNAME, DOZVIL, ADDR, TEL, WEB, FILELOGO, ENLABNAME, ENDOZVIL, ENADDR FROM LABORATORY WHERE ID=$lab");
//   if(getuserid()==90002) {
  	  $h=md5($zak->zakaz.$zak->zakdate.'Srn7s_58frtAqw2Bnk');
  	  $ar=array('zak' => $zak->zakaz, 'date' => $zak->zakdate, 'hash'=>$h);
  	  $p=base64_encode(json_encode($ar));
     QRcode::png('http://verifylab.diaservis.ua/getqrcode.php?par='.$p,'qr/'.$zak->zakaz.'.png');
  	  $d=base64_encode(file_get_contents('qr/'.$zak->zakaz.'.png'));
  	  unlink('qr/'.$zak->zakaz.'.png');
     $bar='
            <td width="15%" rowspan="4" align="center"> <div  ><img src="data:image/png;base64,'.print_r($d,true).'" width="100" height="100" alt=""></div></td>
     ';

//   } else
//     $bar = '<td width="15%" rowspan="4" align="center"><div class="pict" ><img src="barcode.php?BARCODE='.$zak->zakkode.'" width="70%" height="70%" alt=""></div></td>';
   $output = '<div class="pagebreak"><div class="header" align="center">
                      <table border="0" cellpadding="0" cellspacing="0" bordercolor="black" width="100%" style="border-collapse: collapse" >
                          <tr>
                            <td rowspan = "4" valign="center" align="right"><div class="pict" ><img src="altey/'.$rs->fields[5].'" width="100%" height="70%" alt=""> </div></td>
                            <td align="center"><span class="firstline">'.($lang=='ua' ? $rs->fields[0]:$rs->fields[6]).'</span></td>
                            '.$bar.' 
                          </tr>
                          <tr>
                            <td align="center"><span class="secline">'.($lang=='ua' ? $rs->fields[1]:$rs->fields[7]).'</span></td>
                          </tr>
                          <tr>
                            <td align="center"><span class="thirdline">'.($lang=='ua' ? $rs->fields[2]:$rs->fields[8]).'</span></td>
                          </tr>
                          <tr>
                            
                           <td align="center"><span class="fourline">'.$rs->fields[3].'<br>
                                                                                                       '.$rs->fields[4].'</span></td>
                         </tr> 
                       </table>
                       </div>';

   return $output;
}

// *********************************** Основная программа **************************************
  check_user(true) or die("Нет прав на эту страницу");

  $conn = connect_to_db();
  if (!$conn->IsConnected()) {echo "Невозможно подключиться к базе данных ДелоПро 1".date('d.m.y H:i')."\n"; exit(); }

  if(!$ZAKAZ) die('Нет заказа.');
  $zak = new TZakaz();
  $zak->conn = $conn;
  $zak->zakaz = $ZAKAZ;
  if(!$zak->Read()) die('Нет заказа.');
  
  $lab=GetFieldFromSql($conn, "SELECT L.LAB FROM ZAKAZSP S, TOVARY T, KATEG K, LABFORMTOVAR L
                                         WHERE S.ZAKAZ=".$ZAKAZ." AND T.TOVAR = S.TOVAR AND S.FATHER=0 AND K.KATEGORY = T.KATEGORY AND K.RAZDEL = 7 AND L.TOVAR = T.TOVAR", 0 );

   $output = SetHtmlHead();
   $output .= SetLabHead($conn, $lab, $zak);   
   $output .= '<hr noshade width="100%" align="left" size="3" color="black"> ';
   
  if($zak->a_r1) 
  {
  	  $vrach=GetFieldFromSql($conn, "SELECT SOKRASH FROM FIRMS WHERE FIRMA = ".$zak->a_r1, '');
  	  $vr = explode(' ', $vrach);
  	  $vrach=$vr[0];
  	  if($vr[1] != '')
  	  {
  	  	  $vrach .= ' '.substr($vr[1], 0, 1).'. ';
  	  	  if($vr[2] != '' ) $vrach .=substr($vr[2], 0, 1).'.';
  	  }
  } // $vrach = '';
  if($zak->filial == 9668) 
    $filial = GetFieldFromSql($conn, "SELECT SOKRASH FROM FIRMS WHERE FIRMA =".$zak->firma, '');
  else 
    $filial = GetFieldFromSql($conn, "SELECT SOKRASH FROM FIRMS WHERE FIRMA =".$zak->filial, '');
  if($zak->a_r6) $vydat = GetFieldFromSql($conn, "SELECT SOKRASH FROM FIRMS WHERE FIRMA =".$zak->a_r6, ''); else $vydat='';
  switch($zak->a_r2)
  {
  	 case 100044: $pol='Жіночій'; break;
  	 case 100043: $pol='Чоловічий'; break;
  	 default: $pol='Не определен';
  }
  $patient = $zak->a_s2;
  if($zak->a_s3 != '') $patient .= ' '.$zak->a_s3;
  if(strlen($zak->a_s3) == 1) $patient .='.';
  if($zak->a_s4 != '') $patient .= ' '.$zak->a_s4;
  if(strlen($zak->a_s4) == 1) $patient .='.';
  $addr=$zak->a_s5;
  $tel=$zak->a_s1;
  $email=$zak->a_s7;
  if($tel and $zak->a_s6) $tel .= ','.$zak->a_s6; else $tel .= $zak->a_s6;
  $prim=$zak->zakprim;
   
  $output .= ZakazHead($patient, $filial, $zak->zakkode, $zak->zakdate, $zak->a_f4, $pol, $vrach, $vydat,$addr,$tel,$prim,1,$email);

  $sql="SELECT T.ARTIKUL, T.SHORTNAZ, S.KOLICH, E.A_S2 AS EDIZM, S.TAXCENAVAL, S.DISCOUNT, S.TAXSKIDCENA, S.TAXSKIDCENASUM, T.TOVAR, S.SROKDATE
              FROM ZAKAZSP S, TOVARY T, UNIPROPS E
              WHERE S.ZAKAZ = $ZAKAZ AND S.TOVAR = T.TOVAR AND S.FATHER = 0 AND S.EDIZM = E.PROPCNT ";
  $sp=$conn->Execute($sql);  // Спецификация заказа
  $spec=array();
  $i=0; $srok=false;
  while(!$sp->EOF)
  {
     $spec[$i][0] = $sp->fields[0];
     $spec[$i][1] = $sp->fields[1];
     $spec[$i][2] = $sp->fields[2];
     $spec[$i][3] = $sp->fields[3];
     if(GetFieldFromSql($conn, "SELECT SEKRET FROM FIRMS WHERE FIRMA=".$zak->firma) == 100653)
     {
 	    $cena=GetCena($conn, $zak, $sp->fields[8]);
  	    $spec[$i][4] = $cena;
  	    if($zak->a_f2) $spec[$i][5] = 100 - $zak->a_f2; else $spec[$i][5] = 100;
  	    $spec[$i][6] = okrval($cena*(1-$spec[$i][5]/100), 0.01);
  	    $spec[$i][7] = okrval($spec[$i][6]*$sp->fields[2], 0.01);
  	  } else 
  	  {
       $spec[$i][4] = $sp->fields[4];
       $spec[$i][5] = $sp->fields[5];
       $spec[$i][6] = $sp->fields[6];
       $spec[$i][7] = $sp->fields[7];
  	  }
  	  $spec[$i][8]=$sp->fields[9];
  	  $sp->MoveNext();
  	  $i++;
  }

  $output .= ZakazSp($spec,($zak->zaksrc==300));
  
  $output .= PodpisAdm();
  if($PAGE != 1)
  { 
//     if(getuserid() == 90002)
//     {
        $output .= SetLabHeader($conn, $lab, $zak->zakkode);   
        $output .= '<hr noshade width="100%" align="left" size="3" color="black"> ';
     	  $output .= '<div class="pagebreak">';
		//zgoda mod
		$zgoda_skip = false;
		if (getuserid() == 103154) { //Artem
			$ip_admin = $_SERVER['REMOTE_ADDR'];
			$id_cfg = GetFieldFromSql($conn, "SELECT ID FROM ZGODACFG WHERE ADMIN='" . $ip_admin . "' AND NOTEPAD <> '' AND NOTEPAD IS NOT NULL", 0);
			if ($id_cfg) {
				$oper = new TZgodacfg();	
				$oper->conn = $conn;
				$oper->id = $id_cfg;
				if(!$oper->Read()) die(ShowMes('Нет такой записи'));
				$oper->zakaz = $zak->zakaz;
				$mess = $oper->Update();
				if ($mess == '') {
					$zgoda_skip = true;
				}
			}
		}
		if (!$zgoda_skip) {
			$output .= Zgoda($patient, $zak->zakkode, $zak->zakdate);
		}
		   $sql="SELECT S.NUMPOSZAK, T.NAZVANTOV, T.ARTIKUL FROM ZAKAZSP S, TOVARY T WHERE S.ZAKAZ = $ZAKAZ AND S.TOVAR = T.TOVAR AND T.KATEGORY = 31 AND S.FATHER = 0";
		   $pos = $conn->Execute($sql); // Заборы биоматериала по заказу для ведомости расходных материалов
		   if(!$pos->EOF)
		   {
			  $output .= '<hr noshade width="100%" align="left" size="3" color="black"> ';
			  $output .= ZakazHead($patient, $filial, $zak->zakkode, $zak->zakdate, $zak->a_f4, $pol, $vrach, $vydat,$addr,$tel,$prim,3,$email);
		     $output .= SetMaterial($conn, $pos);	
		    }
//     }  
  
  
	  $output .= SetHeadNapr($zak->zakkode, $zak->ordernum);
	  $output .= '<hr noshade width="100%" align="left" size="3" color="black"> ';
	  $output .= ZakazHead($patient, $filial, $zak->zakkode, $zak->zakdate, $zak->a_f4, $pol, $vrach, $vydat,$addr,$tel,$prim,2,$email);
	//  $sp->MoveFirst();  
	  $output .= ZakazSp($spec);
	  $output .= PodpisAdm1($patient);
 /*    
     if($PAGE != 2)	
     {	
		   $sql="SELECT S.NUMPOSZAK, T.NAZVANTOV, T.ARTIKUL FROM ZAKAZSP S, TOVARY T WHERE S.ZAKAZ = $ZAKAZ AND S.TOVAR = T.TOVAR AND T.KATEGORY = 31 AND S.FATHER = 0";
		   $pos = $conn->Execute($sql); // Заборы биоматериала по заказу для ведомости расходных материалов
		   if(!$pos->EOF)
		   {
			  $output .= SetHeadPZ($conn, $lab, $zak->zakkode);
			  $output .= '<hr noshade width="100%" align="left" size="3" color="black"> ';
			  $output .= ZakazHead($patient, $filial, $zak->zakkode, $zak->zakdate, $zak->a_f4, $pol, $vrach, $vydat,$addr,$tel,$prim,3);
		     $output .= SetMaterial($conn, $pos);	
		   }
	  } */
   } 
   $output .= '</body></html>';   
   if($PRINT) print $output; 
  
?>