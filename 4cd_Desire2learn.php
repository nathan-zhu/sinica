<?php
require 'vendor/autoload.php';
//https://desire2learn.4cd.edu/d2l/lp/auth/login/login.d2l
$login_file = "login/Desire2learn_login.html";
$home_file = "login/Desire2learn_home.html";
$cookie_file = dirname(__FILE__)."/cookies/desire2learn.cookie";
$base_url = "https://desire2learn.4cd.edu/";
$logInUrl = $base_url. "d2l/lp/auth/login/login.d2l";
// 准备提交的表单数据之账号和密码。（这个是根据表单选项来的）
$data = "userName=lcui522&password=yinChuan1993";

//$proxy = "127.0.0.1:7070";

$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36');
// 设置代理
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
 
// 基本配置
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 不输出
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);

# Only if you need to bypass SSL certificate validation
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$d2l_login_Html = curl_exec($ch);

$student_infos = array();

//get student name and id
$d2l_login_Html_dom = new \HtmlParser\ParserDom($d2l_login_Html);
$sname = $d2l_login_Html_dom->find('span.d2l-menuflyout-text', 4)->getPlainText(); 
$d2l_list = $d2l_login_Html_dom->find('ul.d2l-list');
foreach($d2l_list as $alist) {
    $id = $alist->find('a.d2l-link', 0)->getAttr('href')."\n";
}
preg_match("/\d{2,}/", $id, $sid);
$student_infos['name'] = $sname;
$student_infos['student_id'] = $sid[0];

//get course classid 
$d2l_home_url = "https://desire2learn.4cd.edu/d2l/lp/courseSelector/6606/InitPartial?_d2l_prc%24headingLevel=2&_d2l_prc%24scope=&_d2l_prc%24hasActiveForm=false&isXhr=true&requestId=3";
curl_setopt($ch, CURLOPT_URL, $d2l_home_url);// 学生列表页面
$d2l_home_Html = curl_exec($ch);
preg_match_all("/(pinOrgUnitId=)\d+/", $d2l_home_Html, $classid);
foreach($classid[0] as $cid) {
    preg_match("/\d+/", $cid, $getcids);
    $courseids[] = $getcids[0];
}
//var_dump($cids);
foreach ($courseids as $cid) {
    $d2l_grade_url = "https://desire2learn.4cd.edu/d2l/lms/grades/my_grades/main.d2l?ou=".$cid;
    curl_setopt($ch, CURLOPT_URL, $d2l_grade_url);// 学生列表页面
    $d2l_grade_Html = curl_exec($ch);
    $grade_file = "grades/". $student_infos['student_id']."_".$cid ."_grade_detail.html";
    //html_put_files($grade_file, $d2l_grade_Html);
    $d2l_grade_Html_dom = new \HtmlParser\ParserDom($d2l_grade_Html);

    $student_infos['grades'][$cid]['course_id'] = $cid;
    $student_infos['grades'][$cid]['course_name'] = $d2l_grade_Html_dom->find('a.d2l-menuflyout-link-link', 0)->getPlainText();
    $student_infos['grades'][$cid]['grade_detail'] = $d2l_grade_Html_dom->find('table', 0)->outerHtml();
}
//var_dump($student_infos);


//get student info page
//$url ="https://desire2learn.4cd.edu/d2l/lp/courseSelector/6606/InitPartial?_d2l_prc%24headingLevel=2&_d2l_prc%24scope=&_d2l_prc%24hasActiveForm=false&isXhr=true&requestId=2";
//https://desire2learn.4cd.edu/d2l/lp/courseSelector/6606/InitPartial?_d2l_prc%24headingLevel=2&_d2l_prc%24scope=&_d2l_prc%24hasActiveForm=false&isXhr=true&requestId=3
// $url = "https://desire2learn.4cd.edu/d2l/le/manageCourses/widget/myCourses/6606/ContentPartial?defaultLeftRightPixelLength=10&defaultTopBottomPixelLength=7"; 
// $data = "widgetId=37&_d2l_prc%24headingLevel=3&_d2l_prc%24scope=&_d2l_prc%24hasActiveForm=false&isXhr=true&requestId=2&d2l_referrer=xKidycu5YCcZL7IxMUz2ROnCD4sds9YO";
// // 基本配置
// curl_setopt($ch, CURLOPT_URL, $url);// 网址
// curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
// curl_setopt($ch, CURLOPT_HEADER, 0);
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
// $d2l_class_Html = curl_exec($ch);

//html_put_files($home_file, $d2l_class_Html);
//$d2l_list_Html_dom = new \HtmlParser\ParserDom($d2l_list_Html);

/**
 * [html_put_files description]
 * @param  [type] $fname [description]
 * @param  [type] $data  [description]
 * @return [type]        [description]
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

