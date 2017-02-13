<?php
require 'vendor/autoload.php';
$school_name ="MarianHS";
$login_file = "login/".$school_name."_login.html";
$grade_file = "login/".$school_name."_grade.html";

$cookie_file = dirname(__FILE__)."/cookies/".$school_name.".cookie";
$base_url = "https://www.plusportals.com";
$logInUrl = $base_url."/MarianHS";
// 准备提交的表单数据之账号和密码。（这个是根据表单选项来的）
$data = "UserName=mia@liumeihui.com&Password=Zhang2000&RememberMe=false";

$Agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36";
//$proxy = "127.0.0.1:7070";
$attendance_type = array(
    0 => 'Absent',
    1 => 'Tardy',
    2 => 'Dismissal',
    3 => 'Incident'
);

$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
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

$login_html = curl_exec($ch);
html_put_files($login_file, $login_html);


$pp_get_all_students = 1;
$pp_infos = array();
// $pp_lar_url = "https://www.plusportals.com/ParentStudentDetails/ParentStudentDetails/780";
// curl_setopt($ch, CURLOPT_URL, $pp_lar_url);// 学生列表页面
// $pp_list_Html = curl_exec($ch);

$login_Html_dom = new \HtmlParser\ParserDom($login_html);

/*
//##get student list page cache token and studentid.
$pp_list_url = "https://www.plusportals.com/Account/ApplyRole?mode=2";

curl_setopt($ch, CURLOPT_URL, $pp_list_url);// 学生列表页面
$pp_list_Html = curl_exec($ch);

$pp_list_Html_dom = new \HtmlParser\ParserDom($pp_list_Html);
$pp_get_all_students = $pp_list_Html_dom->find('div.txtnme');

$pp_bia = $pp_list_Html_dom->find('div.blk-imgr a');
$j = 0;
foreach($pp_bia as $alinks) {
    $get_sid[$j] = $alinks->getAttr('href');
    preg_match("/\d.*(?=\?token=)/", $get_sid[$j], $sid[$j]);
    $j++;
}*/
$attend_file = 'attendance.html';

//catch information for each students.
$y = 0;
for ($i = 0; $i < count($pp_get_all_students); $i++) {
    $y = $i+$i;
    //get student name and id
    $pp_infos[$i]['name'] = $login_Html_dom->find('div.txtnme', $i)->getPlainText();
    $img_link = $get_token_link = $login_Html_dom->find('img.blk-img-crved', $i)->getAttr('src');
    preg_match("/(sid=)\d.*(?=\&token=)/", $img_link, $sid);
    $token[$i] = preg_replace("/.*(&token=)/", "", $get_token_link);
    $pp_infos[$i]['token'] = $token[$i];
    $pp_infos[$i]['student_id'] = preg_replace("/sid=/", "", $sid[0]);
    $pp_infos[$i]['student_img'] = $base_url.$img_link;

    //get attendence summary for each students
    $x = 0;
    $pdn_attendance = $login_Html_dom->find('div.pdn-attendance', $y);
    foreach($pdn_attendance->find('div.box-in') as $boxin) {
        $x++;
        $boxin_text = $boxin->getPlainText();
        switch($x) {
            case 1:
                $pp_infos[$i]['attendence_summary']['Absences'] = $boxin_text;
                break;
            case 2:
                $pp_infos[$i]['attendence_summary']['Tardies'] = $boxin_text;
                break;
            case 3:
                $pp_infos[$i]['attendence_summary']['Dismissals'] = $boxin_text;
                break;
            case 4:
                $pp_infos[$i]['attendence_summary']['Incidents'] = $boxin_text;
                break;
        }
    }
    
    //get attendence detail page content
    foreach ($attendance_type as $type_key => $type) {
        $pp_attend_url = "https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=". $pp_infos[$i]['student_id'] ."&showtype=". $type;
        curl_setopt($ch, CURLOPT_URL, $pp_attend_url);// 学生成绩详情页面
        $pp_attend_Html[$type_key] = curl_exec($ch);
        $pp_attend_Html_dom[$type_key] = new \HtmlParser\ParserDom($pp_attend_Html[$type_key]);
        $pp_attend_Html_body[$type_key] = $pp_attend_Html_dom[$type_key]->find('div.main-cont', 0)->outerHtml();
        $temfile = "login/". $pp_infos[$i]['student_id']."_".$type."_".$attend_file;
        //file_put_contents($temfile, $pp_attend_Html_body[$type_key]);
        $pp_infos[$i]['attendence_detail'][$type] = $pp_attend_Html_body[$type_key];
    }
    
    //get MarkingPeriodId
    $markingperiod_url = "https://www.plusportals.com/ParentStudentDetails/GetMarkingPeriod";  
    curl_setopt($ch, CURLOPT_URL, $markingperiod_url);// 学生列表页面
    $MarkingPeriod_Html = curl_exec($ch);
    $markingperiod_data = json_decode($MarkingPeriod_Html, true);
    //var_dump($markingperiod_data);
    foreach ($markingperiod_data as $mp) {
        $MarkingPeriodId[$mp['MarkingPeriodId']] = $mp['MarkingPeriodName'];
    }

    //get student detail page
    //https://www.plusportals.com/ParentStudentDetails/ParentStudentDetails/9192?isProgressReport=False&token=FB223F4CCAA8F2A4E9E72FE845C37303AA44A2DDF950074538458B6C83E4E9E0
    $pp_score_url = "https://www.plusportals.com/ParentStudentDetails/ParentStudentDetails/". $pp_infos[$i]['student_id'] ."?isProgressReport=False&token=". $token[$i];
    curl_setopt($ch, CURLOPT_URL, $pp_score_url);// 学生成绩详情页面
    $pp_score_Html = curl_exec($ch);
    $z = 0;
    foreach($MarkingPeriodId as $key => $value) {
        $z++;  
        $url = "https://www.plusportals.com/ParentStudentDetails/ShowGridProgressInfo?markingPeriodId=". $key ."&isGroup=false";  
        //get student marking Period 
        $data = "";
        // 基本配置
        curl_setopt($ch, CURLOPT_URL, $url);// 网址
        curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
        $pp_grade_Html[$key] = curl_exec($ch);
        $php_grade_Html_array[$key] = json_decode($pp_grade_Html[$key], true);
        $path_file = "pp_grade.html";
        foreach ($php_grade_Html_array[$key] as $Datas) {
            if(is_array($Datas)) {
                foreach($Datas as $grades) {
                    if(is_array($grades)) {
                        //get grade summary
                        if(is_numeric($grades['Average'])) {
                            $grades['Average'] = round($grades['Average'], 2);
                        }
                        $pp_infos[$i]['grades_summary'][$value][] = $grades;

                        //get all course from first MarkingPeriodId
                        if($z == 1) {
                            $msc = getMillisecond();
                            $SectionId = $grades['SectionId'];
                            //get Category Averages for each Course
                            $pp_grade_detail = "https://www.plusportals.com/ParentStudentDetails/ShowScoresDetails?_=". $msc ."&sectionId=". $grades['SectionId'];
                            //echo $pp_grade_detail."\n";
                            curl_setopt($ch, CURLOPT_URL, $pp_grade_detail);// 学生成绩详情页面
                            $pp_category_averages_Html[$SectionId] = curl_exec($ch);

                            $category_averages_dom = new \HtmlParser\ParserDom($pp_category_averages_Html[$SectionId]);
                            //get teacher name
                            $category_averages_html = $category_averages_dom->find('div.graybox');
                            foreach($category_averages_html as $ca_td) {
                                $teacher[$SectionId] = $ca_td->find('td', 1)->getPlainText();
                            }
                            $pp_infos[$i]['grades_detail'][$SectionId]['teacher_name'] = preg_replace("/: /", "", $teacher[$SectionId]);
                            $pp_infos[$i]['grades_detail'][$SectionId]['teacher_email'] = '';

                             //get course grades_summary_total
                            $pp_infos[$i]['grades_detail'][$SectionId]['grades_summary_total'] = $category_averages_dom->find('div table', 1)->outerHtml();

                            //get course category_averages
                            $category_averages_table = $category_averages_dom->find('div table', 2)->outerHtml();
                            $pp_infos[$i]['grades_detail'][$SectionId]['category_averages'] = preg_replace("/<a [^>]*>|<\/a>/","",$category_averages_table);


                            //$path_file = "grade_detail.html";
                            //$temfile = "login/". $pp_infos[$i]['student_id']."_".$key."_".$grades['SectionId']."_".$path_file;
                            //file_put_contents($temfile, $pp_grade_detail_Html[$SectionId]);

                            //get detail Scores for each Course 
                            $url = "https://www.plusportals.com/ParentStudentDetails/ShowScoresGridInfo";
                            curl_setopt($ch, CURLOPT_URL, $url);// 网址
                            curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
                            $pp_grade_course_detail_Html[$SectionId] =  curl_exec($ch);
                            $pp_infos[$i]['grades_detail'][$SectionId]['course_detail'] = $pp_grade_course_detail_Html[$SectionId];

                            //$path_file = "scores_grade_detail.html";
                            //$temfile = "login/". $pp_infos[$i]['student_id']."_".$key."_".$grades['SectionId']."_".$path_file;
                            //file_put_contents($temfile, $pp_grade_course_detail_Html[$SectionId]);
                            
                            //get all datas array to check
                            // $temfile = "login/0000_". $pp_infos[$i]['student_id']."_".$key."_total.html";
                            // file_put_contents($temfile, var_export($pp_infos, true), FILE_APPEND);
                        }
                    }
                }
            }
        }
    }
}
curl_close($ch);

//var_dump($pp_infos);
$file = "grades/MarianHS_all.html";
html_put_files($file, var_export($pp_infos, true));

function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());     
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);  
}

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
//attendence
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9207&showtype=Absent
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9192&showtype=Tardy
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9192&showtype=Dismissal
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9207&showtype=Incident
