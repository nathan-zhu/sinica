<?php
require 'vendor/autoload.php';
//http://lp15.smhs.org/login/index.php
//$data = "username=shitian.chen&password=wee34rrt&anchor=";
//https://aeries.smhs.org/Parent/LoginParent.aspx
$fname = "sin";
$login_file = "login/".$fname.".html";
$home_file = "login/".$fname."_home.html";
$username = "jlin452";
$password = "TimRCDS452";

$cookie_file = dirname(__FILE__)."/cookies/stiinformationnow.cookie";
$base_url = "https://500203.stiinformationnow.com";
$logInUrl = $base_url. "/InformationNow/Login.aspx";
$Agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36";

$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
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
//html_put_files($login_file, $login_Html);

$login_dom = new \HtmlParser\ParserDom($login_Html);
$__VIEWSTATE = $login_dom->find('#__VIEWSTATE', 0)->getAttr('value');
$__EVENTTARGET = $login_dom->find('#__EVENTTARGET', 0)->getAttr('value');
$__EVENTARGUMENT = $login_dom->find('#__EVENTARGUMENT', 0)->getAttr('value');
$__EVENTVALIDATION = $login_dom->find('#__EVENTVALIDATION', 0)->getAttr('value');
$REQUEST_TICKET = $login_dom->find('#REQUEST_TICKET', 0)->getAttr('value');
$hidRandomNumber = $login_dom->find('#hidRandomNumber', 0)->getAttr('value');
$IsAddSchoolAcad = $login_dom->find('#IsAddSchoolAcad', 0)->getAttr('value');
$btnLogin = $login_dom->find('#btnLogin', 0)->getAttr('value');

//$ctl00_PID = $grade_dom->find('#ctl00_PID', 0)->getAttr('value');
// 准备提交的表单数据之账号和密码。（这个是根据表单选项来的）
//$data = "__EVENTTARGET=".$__EVENTTARGET."&__EVENTARGUMENT=".$__EVENTARGUMENT."&REQUEST_TICKET=".$REQUEST_TICKET."&__VIEWSTATE=".$__VIEWSTATE."&__EVENTVALIDATION=".$__EVENTVALIDATION."&txtUsername=".$username."&txtPassword=".$password."&btnLogin=".$btnLogin."&IsAddSchoolAcad=".$IsAddSchoolAcad."&hidRandomNumber=".$hidRandomNumber;
$data = "__EVENTTARGET=&__EVENTARGUMENT=&REQUEST_TICKET=".urlencode($REQUEST_TICKET)."&__VIEWSTATE=".urlencode($__VIEWSTATE)."&__EVENTVALIDATION=".urlencode($__EVENTVALIDATION)."&txtUsername=".$username."&txtPassword=".$password."&btnLogin=".$btnLogin."&IsAddSchoolAcad=".$IsAddSchoolAcad."&hidRandomNumber=".$hidRandomNumber;

//$proxy = "127.0.0.1:7070";
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
// 设置代理
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
 
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
$sin_login_Html = curl_exec($ch);
//html_put_files($login_file, $sin_login_Html);

//get name grd school and attendance summary
$student_infos = array();
$attendance_url = "https://500203.stiinformationnow.com/InformationNow/ParentPortal/Sti.Home.UI.Web/Student/Attendance.aspx";
curl_setopt($ch, CURLOPT_URL, $attendance_url);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
$attendance_Html = curl_exec($ch);
$att_file = "login/500203.html";
//html_put_files($att_file, $attendance_Html);

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

