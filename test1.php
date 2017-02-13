<?php 
require 'vendor/autoload.php';
include('db_class.php'); // call db.class.php
$bdd = new db(); 
/*function get_last_grade($uid, $studentid, $courseid, $cgrade, $time = null) {
    $bdd = new db();
    $query = "SELECT * FROM sinica_grade_summary WHERE studentid = ". $studentid ." AND uid = ". $uid ." AND courseid = '". $courseid ."' order by id desc limit 1";
    $result = $bdd->getAll($query);
    
    $average = $result[0]['average'];
    if ($average) {
        if($cgrade > $average ) {
            $status = 'up';
        }
        elseif($cgrade < $average) {
            $status = 'down';
        }
        else {
            $status = 'equal';
        }
        return $status;
    }
    else {
        return NUll;
    }
    
}
echo get_last_grade(28, 611, 635, 80.39);*/

// $tt = "/pw/school/class.cfm?studentid=1208361&classid=23118357";
//  preg_match("/(?:\d*\.)?\d+$/", $tt, $temp);
// var_dump($temp[0]);
//require 'vendor/autoload.php';
// function getMillisecond() {
//     list($s1, $s2) = explode(' ', microtime());     
//     return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);  
// }
// $aa = getMillisecond();

// $url = '/private/var/www/oop/html-parser/login/9192_54_3074_grade_detail.html';
// $sHtml = file_get_contents($url);
// $html_dom = new \HtmlParser\ParserDom($sHtml);
// $pp = $html_dom->find('div table', 2)->outerHtml();
// $ppp = preg_replace("/<a [^>]*>|<\/a>/","",$pp);
// echo $ppp;
// foreach($pp as $p) {
//     $tt = $p->find('td', 1)->getPlainText()."\n";
// }
// $teacher_name = preg_replace("/: /", "", $tt);
// echo $teacher_name."\n";
// echo $html_dom->find('input', 3)->getAttr('value')."\n";
// echo $html_dom->find('input', 4)->getAttr('value')."\n";

// $url = '/private/var/www/oop/html-parser/login/pp_list.html';
// $sHtml = file_get_contents($url);
// $html_dom = new \HtmlParser\ParserDom($sHtml);
// $pp_get_all_students = $html_dom->find('div.txtnme');
// $pp_infos = array();

// $pp_bia = $html_dom->find('div.blk-imgr a');
// $j = 0;
// foreach($pp_bia as $alinks) {
//     $get_sid[$j] = $alinks->getAttr('href');
//     preg_match("/\d.*(?=\?token=)/", $get_sid[$j], $sid[$j]);
//     $j++;
// }
// var_dump($sid);
// for ($i = 0; $i < count($pp_get_all_students); $i++) {
//     $pp_infos[$i]['name'] = $html_dom->find('div.txtnme', $i)->getPlainText();
//     $img_link = $get_token_link = $html_dom->find('img.blk-img-crved', $i)->getAttr('src');
//     $token = preg_replace("/.*(&token=)/", "", $get_token_link);
//     $pp_infos[$i]['token'] = $token;
//     $pp_infos[$i]['student_id'] = $sid[$i][0];
//     $pp_infos[$i]['student_img'] = $img_link;
// }
// //curl_close($ch);
// var_dump($pp_infos);
/*
utf8:✓
authenticity_token:lC1L6epImfLF8svYwfY9L/Abg3wpqZfPfvdIqDcOQEtqZmqlAY2XPHIzPVfCm4knrnpeS1o9KiEsyDkOUe00iw==
username:liyan19@jcpatriot.org
password:Leon20000608
return_to:https://portals.veracross.com/jcs/session
application:Portals
remote_ip:114.251.139.39
commit:Log In
*/
$cookie_file = dirname(__FILE__)."/cookies/veracross.cookie";
$lUrl = "https://portals.veracross.com/jcs/login";
//$logInUrl = "https://accounts.veracross.com/jcs/authenticate";

$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $lUrl);// 学生列表页面
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36');

$login = curl_exec($ch);

$login_dom = new \HtmlParser\ParserDom($login);
$a = $login_dom->find('form');
echo count($a);
// foreach($a as $b) {
//     var_dump($b);
//    echo  $b->getAttr('value')."\n";
// }
exit();
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36');
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 学生列表页面
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 不输出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
# Only if you need to bypass SSL certificate validation
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$login_Html = curl_exec($ch);

$login_dom = new \HtmlParser\ParserDom($login_Html);
$VIEWSTATE = $login_dom->find('input#__VIEWSTATE', 0)->getAttr('value');
$VIEWSTATEGENERATOR = $login_dom->find('input#__VIEWSTATEGENERATOR', 0)->getAttr('value');
$EVENTVALIDATION = $login_dom->find('input#__EVENTVALIDATION', 0)->getAttr('value');
$VIEWSTATE = urlencode($VIEWSTATE);
$VIEWSTATEGENERATOR = urlencode($VIEWSTATEGENERATOR);
$EVENTVALIDATION = urlencode($EVENTVALIDATION);

$data = "__VIEWSTATE=".$VIEWSTATE."&__VIEWSTATEGENERATOR=".$VIEWSTATEGENERATOR."&__EVENTVALIDATION=".$EVENTVALIDATION."&frmLogin%24UserName=lcui522&frmLogin%24Password=yinChuan1993&frmLogin%24Login=Login&DisableEnrollmentReminders=0&pgclient=&OTP=&LoginAnswer1=&LoginAnswer2=&LoginAnswer3=&LoginAnswer4=&LoginAnswer5=&TOUAction=";

curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
$logined1_Html = curl_exec($ch);
$logined1_dom = new \HtmlParser\ParserDom($logined1_Html);
$potoken = $logined1_dom->find('#PGToken', 0)->getAttr('value');

$logInUrl2 = "https://pg.4cd.edu/sso/go.ashx";
$data = "PGToken=".$potoken."&id=http%3A%2F%2F4cd%2Einstructure%2Ecom%2Fsaml2&Auth=";
curl_setopt($ch, CURLOPT_URL, $logInUrl2);
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
$logined2_Html = curl_exec($ch);
$logined2_dom = new \HtmlParser\ParserDom($logined2_Html);
$SAMLResponse = $logined2_dom->find('input', 1)->getAttr('value');

$cookie_saml_file = dirname(__FILE__)."/cookies/4cd_saml.cookie";

$data = "RelayState=&SAMLResponse=".$SAMLResponse;
$login_saml_url = "https://4cd.instructure.com/login/saml";
//$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $login_saml_url);
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_exec($ch);
// $grade_file = "login/saml.html";
// html_put_files($grade_file, $saml);

// $dvc_url ="https://4cd.instructure.com/?login_success=1";
// curl_setopt($ch, CURLOPT_URL, $dvc_url);// 学生列表页面
// $dvc_Html = curl_exec($ch);
// $grade_file = "grades/4cd_detail.html";
// html_put_files($grade_file, $dvc_Html);

$dvc_grade_url ="https://4cd.instructure.com/courses/10519/grades";
curl_setopt($ch, CURLOPT_URL, $dvc_grade_url);// 学生列表页面
$dvc_grade_Html = curl_exec($ch);
$grade_file = "grades/4cd_grade_detail.html";
html_put_files($grade_file, $dvc_grade_Html);
$student_infos = array();

function html_put_files($fname = NULL, $data) {
    if(empty($fname)) {
        $fname = "login/normal_login.html";
    }
    if(is_array($data)) {
        $data = var_export($data, true);
    }
    file_put_contents($fname, $data, FILE_APPEND);
}