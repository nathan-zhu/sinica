<?php
require 'vendor/autoload.php';
include('db_class.php'); // call db.class.php
$bdd = new db();
/*
utf8:✓
authenticity_token:lC1L6epImfLF8svYwfY9L/Abg3wpqZfPfvdIqDcOQEtqZmqlAY2XPHIzPVfCm4knrnpeS1o9KiEsyDkOUe00iw==
username:liyan19@jcpatriot.org
password:Leon20000608
return_to:https://portals.veracross.com/jcs/session
application:Portals
remote_ip:114.251.139.39
commit:Log In

https://portals.veracross.com/jcs/session
utf8:✓
authenticity_token:nKwbXTc/NMZNgeBYjeT9p7TMP7BzIT1ZlPM6BaWeoOnWuGTS9b1to2MyddQekF/WzNdpXds5AsVi4cRr8bJ9tA==
account:eyJwZXJzb25faWQiOjE2MzU3LCJ1c2VybmFtZSI6ImxpeWFuMTlAamNwYXRyaW90Lm9yZyIsImRpc3BsYXlfdXNlcm5hbWUiOiJsaXlhbjE5QGpjcGF0cmlvdC5vcmciLCJpbXBlcnNvbmF0b3IiOm51bGwsImlzX2JyZXVlciI6ZmFsc2UsImlzX2ltcGVyc29uYXRpbmciOmZhbHNlLCJpc19kYXRhYmFzZV91c2VyIjpmYWxzZSwic2VjdXJpdHlfcm9sZXMiOiJTdHVkZW50Iiwic3RhdHVzIjoxLCJzdWNjZXNzIjp0cnVlLCJtZXNzYWdlIjoiU3VjY2VzcyIsInBhc3N3b3JkX3N5bmNpbmdfZW5hYmxlZCI6ZmFsc2UsImxvZ2luX2xvZ19pZCI6NjM1Mzk0LCJjb25uZWN0aW9uX2lkIjoiNGU2ZjI1ZGQtNDJjYi00MDlkLWFkMzEtMDJhYTdlZGRkMGM2IiwiZXhwaXJlcyI6IjIwMTctMDItMTBUMDg6Mzc6NDQrMDA6MDAiLCJobWFjIjoiYzUwMjk4Yzc0MzliN2E1ZTY2MjJhZGQ2ZTAxZjU0ODc0NjNmOTdkMiJ9
 */
$fname = "veracross.jcs";
$login_file = "login/".$fname.".html";
$home_file = "login/".$fname."_home.html";
$userName = "liyan19@jcpatriot.org";
$passWord = "Leon20000608";
$proxy = '127.0.0.1:7070';

$cookie_file = dirname(__FILE__)."/cookies/jcs.cookie";
//$logInUrl = "https://accounts.veracross.com/jcs/authenticate";
$base_url = "https://portals.veracross.com/jcs/login";
$logInUrl = "https://accounts.veracross.com/jcs/authenticate";
$Agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36";
$studentUrl = "https://portals.veracross.com/jcs/student";
$sessionUrl = "https://portals.veracross.com/jcs/session";

$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $base_url);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 不输出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
$base_login_Html = curl_exec($ch);
html_put_files($login_file, $base_login_Html);

$base_login_dom = new \HtmlParser\ParserDom($base_login_Html);
$ainput = $base_login_dom->find('input');
$i = 1;
foreach ($ainput as $vinput) {
    $tvalue = $vinput->getAttr('value');
    switch($i) {
        case 1:
            $utf8 = $tvalue;
            break;
        case 2:
            $authenticity_token = $tvalue;
            break;
        case 5:
            $return_to = $tvalue;
            break;
        case 6:
            $application = $tvalue;
            break;
        case 7:
            $remote_ip = $tvalue;
            break;
        case 8:
            $commit = $tvalue;
            break;
    }
    $i++;
}
$data = "utf8=".$utf8."&authenticity_token=".$authenticity_token."&username=".$userName."&passowrd=".$passWord."&return_to=".$return_to."&application=".$application."&remote_ip=".$remote_ip."&commit=".$commit;

//$proxy = "127.0.0.1:7070";
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
// 设置代理
// curl_setopt($ch, CURLOPT_PROXY, $proxy);
// curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
// 基本配置
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 不输出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

# Only if you need to bypass SSL certificate validation
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$login_Html = curl_exec($ch);
html_put_files("login/logined_veracross.html", $login_Html);

$login_dom = new \HtmlParser\ParserDom($login_Html);
$binput = $login_dom->find('input');
$i = 1;
foreach ($binput as $vvinput) {
    $tvalue = $vvinput->getAttr('value');
    switch($i) {
        case 1:
            $utf8 = $tvalue;
            break;
        case 2:
            $authenticity_token = $tvalue;
            break;
        case 3:
            $account = $tvalue;
            break;
    }
    $i++;
}
$udata = "utf8=".$utf8."&authenticity_token=".$authenticity_token."&account=".$account;
$sdata = urlencode($udata);

curl_setopt($ch, CURLOPT_URL, $sessionUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 不输出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $sdata);// POST数据
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

// # Only if you need to bypass SSL certificate validation
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$user_Html = curl_exec($ch);
$att_file = "login/user_session.html";
html_put_files($att_file, $user_Html);

curl_setopt($ch, CURLOPT_URL, $studentUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
$sHtml = curl_exec($ch);
$sfile = "login/user.html";
html_put_files($sfile, $sHtml);

exit();

$att_dom = new \HtmlParser\ParserDom($attendance_Html);
$student_infos['attendance'] = $att_dom->find('#ctl00_ContentPlaceHolder1_grdAttendance', 0)->outerHtml();
$student_infos['student_name'] = $att_dom->find('#ctl00_ddStudentList', 0)->getPlainText();
$student_infos['student_id'] = $att_dom->find('#ctl00_ddStudentList option', 0)->getAttr('value');
//get grade page
$grade_url = "https://500203.stiinformationnow.com/InformationNow/ParentPortal/Sti.Home.UI.Web/Student/Grades.aspx";
curl_setopt($ch, CURLOPT_URL, $grade_url);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
$grade_Html = curl_exec($ch);

//$grade_file = "grades/500203_grade.html";
//html_put_files($grade_file, $grade_Html);
$grade_dom = new \HtmlParser\ParserDom($grade_Html);

//get Grading Period 学期
$terms_list = array();
foreach($grade_dom->find('#ctl00_ContentPlaceHolder1_ddGradingPeriodList option') as $terms) {
    //if(($terms->getAttr('value') != 0) && empty($terms->getAttr('selected'))) {
    if($terms->getAttr('value') != 0) {
        $terms_list[$terms->getAttr('value')] = $terms->getPlainText();
    }
}
//var_dump($student_infos['grade']);
//get grade table
$grade_period_url = "https://500203.stiinformationnow.com/InformationNow/ParentPortal/Sti.Home.UI.Web/Student/Grades.aspx";
//get post data elements
$__EVENTTARGET = $grade_dom->find('#__EVENTTARGET', 0)->getAttr('value');
$__EVENTARGUMENT = $grade_dom->find('#__EVENTARGUMENT', 0)->getAttr('value');
$__LASTFOCUS = $grade_dom->find('#__LASTFOCUS', 0)->getAttr('value');
$__VIEWSTATE = $grade_dom->find('#__VIEWSTATE', 0)->getAttr('value');
$__EVENTVALIDATION = $grade_dom->find('#__EVENTVALIDATION', 0)->getAttr('value');

foreach($terms_list as $period_id => $term) {
    //prepare data of post
    $data ="ctl00%24ScriptManager1=ctl00%24MainContentUpdatePanel%7Cctl00%24ContentPlaceHolder1%24ddGradingPeriodList&__EVENTTARGET=".urlencode($__EVENTTARGET)."&__EVENTARGUMENT=".urlencode($__EVENTARGUMENT)."&__LASTFOCUS=".urlencode($__LASTFOCUS)."&__VIEWSTATE=".urlencode($__VIEWSTATE)."&__EVENTVALIDATION=".urlencode($__EVENTVALIDATION)."&ctl00%24ddStudentList=".$student_infos['student_id']."&ctl00%24ddStudentAcadSession=15&ctl00%24ContentPlaceHolder1%24ddGradingPeriodList=".$period_id."&__ASYNCPOST=true";

    curl_setopt($ch, CURLOPT_URL, $grade_period_url);// 网址
    curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
    $grade_period_Html = curl_exec($ch);

    $period_file = "grades/".$student_infos['student_id']."_".$period_id.".html";
    html_put_files($period_file, $grade_period_Html);

    $grade_period_dom = new \HtmlParser\ParserDom($grade_period_Html);
    $student_infos['grade'][$period_id] = $grade_period_dom->find('#ctl00_ContentPlaceHolder1_grdGrades', 0)->outerHtml();
}
/*
$sin_dom = new \HtmlParser\ParserDom($sin_login_Html);
$infos = $sin_dom->find('div#Sub_7 a', 0)->getPlainText();
list($student_infos['name'], $student_infos['grd'], $student_infos['school']) = explode(' - ', $infos);
$student_infos['attendance_summary'] = $lp15_dom->find('#ctl00_MainContent_dlAttSummaryPeriod', 0)->outerHtml();

//get courses number and grade summary
$grade_url = "https://aeries.smhs.org/Parent/GradebookSummary.aspx";
curl_setopt($ch, CURLOPT_URL, $grade_url);// 网址
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
$grade_Html = curl_exec($ch);
//$grade_file = "grades/lp15_home.html";
//html_put_files($grade_file, $grade_Html);
preg_match_all("/(var assignData_)\d+[_][A-Z]/", $grade_Html, $courses_ids);

//get all courses ids 
foreach($courses_ids[0] as $key => $ids) {
    $key++;
    preg_match("/\d+[_][A-Z]{1}/", $ids, $matches);
    $cids[$key] = $student_infos['grades'][$key]['course_id'] = $matches[0];
}

//get content for each courses
$grade_dom = new \HtmlParser\ParserDom($grade_Html);
$grade_table = $grade_dom->find('#ctl00_MainContent_subGBS_tblEverything');
//$__VIEWSTATE = $grade_dom->find('#__VIEWSTATE', 0)->getAttr('value');
//$__VIEWSTATEGENERATOR = $grade_dom->find('#__VIEWSTATEGENERATOR', 0)->getAttr('value');
//$ctl00_SC = $grade_dom->find('#ctl00_SC', 0)->getAttr('value');
$ctl00_SN = $grade_dom->find('#ctl00_SN', 0)->getAttr('value');
//$ctl00_PID = $grade_dom->find('#ctl00_PID', 0)->getAttr('value');

$student_infos['student_id'] = $ctl00_SN;

foreach($grade_table as $table) {
    $grade_table_summary = $table->outerHtml();
    $student_infos['grade_table_summary'] = $grade_table_summary;
    foreach ($cids as $key => $value) { 
        if(strlen($key) == 1) {
            $td_id = "tr#ctl00_MainContent_subGBS_DataDetails_ctl0".$key."_trGBKItem .Data";
        }else {
            $td_id = "tr#ctl00_MainContent_subGBS_DataDetails_ctl".$key."_trGBKItem .Data";
        }
        $i = 0;
        foreach($table->find($td_id) as $tr) {
            $cdata = $tr->getPlainText();
            switch($i) {
                case 1:
                    $student_infos['grades'][$key]['courses_name'] = $cdata;
                    break;
                case 4:
                    $student_infos['grades'][$key]['courses_teacher'] = $cdata;
                    break;
                case 5: 
                    $student_infos['grades'][$key]['courses_score'] = $cdata;
                    break;
                case 7:
                    $student_infos['grades'][$key]['courses_grade'] = $cdata;
                    break;
            }
            $i++;
        }

        //get grade detail for each courses;
        list($GN, $Term) = explode('_', $value);
        $grede_detail_url = "https://aeries.smhs.org/Parent/Widgets/ClassSummary/RedirectToGradebook?GradebookNumber=".$GN."&Term=".$Term;
        curl_setopt($ch, CURLOPT_URL, $grede_detail_url);
        $grade_detail_Html = curl_exec($ch);
        //$grade_file = "grades/". $student_infos['student_id']."_".$value ."_grade_detail.html";
        //html_put_files($grade_file, $grade_detail_Html);
        $grade_detail_dom = new \HtmlParser\ParserDom($grade_detail_Html);
        $student_infos['grades'][$key]['courses_teacheremail'] = $grade_detail_dom->find('#ctl00_MainContent_subGBS_EMailTeacher', 0)->getPlainText();
        $y = 1;
        foreach($grade_detail_dom->find('#ctl00_MainContent_subGBS_assignmentsView table') as $table) {
            $detail = $table->outerHtml();
            if($y == 1) {
                $student_infos['grades'][$key]['grade_detail_1'] = $detail;
                //$ff = "grades/detail1.html";                
                //html_put_files($ff, $detail);
            }
            if($y == 3) {
                $student_infos['grades'][$key]['grade_detail_2'] = $detail;
                //$ff = "grades/detail2.html";
                //html_put_files($ff, $detail);
            }
            $y++;
        }
    }
}
$all = 'grades/lp15_'.$student_infos['student_id']."_all.html";
html_put_files($all, var_export($student_infos, true));
*/

function html_put_files($fname = NULL, $data) {
    if(empty($fname)) {
        $fname = "login/normal_login.html";
    }
    if(is_array($data)) {
        $data = var_export($data, true);
    }
    file_put_contents($fname, $data, FILE_APPEND);
}

