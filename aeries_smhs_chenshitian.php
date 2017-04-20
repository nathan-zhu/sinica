<?php
require 'vendor/autoload.php';
include('db_class.php'); // call db.class.php
$bdd = new db(); 

$login_file = "login/lp15.html";
$home_file = "login/lp15_home.html";
$cookie_file = dirname(__FILE__)."/cookies/lp15.cookie";
$base_url = "https://aeries.smhs.org/";
$logInUrl = $base_url. "Parent/LoginParent.aspx";
// 准备提交的表单数据之账号和密码。（这个是根据表单选项来的）
$data = "checkCookiesEnabled=true&checkMobileDevice=false&checkStandaloneMode=false&checkTabletDevice=false&portalAccountUsername=shitian.chen%40smhsstudents.org&portalAccountPassword=Chen2000927&portalAccountUsernameLabel=&submit=";

$Agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36';

$uid = 54;
$gradeYear = '10th';
//$term = 'Q3';
$Catch_All_Term_Datas = "";
$school = "Santa Margarita Catholic High School";

$proxy = "127.0.0.1:7070";
$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
// 设置代理
// curl_setopt($ch, CURLOPT_PROXY, $proxy);
// curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
 
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
$aeries_login_Html = curl_exec($ch);
// html_put_files($login_file, $aeries_login_Html);

//get name grd school and attendance summary
$student_infos = array();
$aeries_dom = new \HtmlParser\ParserDom($aeries_login_Html);
$infos = $aeries_dom->find('div#Sub_7 a', 0)->getPlainText();
list($student_infos['name'], $grd, $student_infos['school']) = explode(' - ', $infos);
preg_match("/\d+/", $grd, $gy);
$gradeYear = $student_infos['grd'] = $gy[0].'th';
//get courses number and grade summary
$grade_url = "https://aeries.smhs.org/Parent/GradebookSummary.aspx";
curl_setopt($ch, CURLOPT_URL, $grade_url);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
$grade_Html = curl_exec($ch);
//$grade_file = "grades/aeries_home.html";
//html_put_files($grade_file, $grade_Html);
preg_match_all("/(var assignData_)\d+[_][S]/", $grade_Html, $courses_ids);

//get all courses ids 
foreach($courses_ids[0] as $key => $ids) {
    $key++;
    preg_match("/\d+[_][S]{1}/", $ids, $matches);
    //preg_match("/\d+/", $matches[0], $courseId);
    $cids[$key] = $matches[0];
    preg_match("/\d+/", $matches[0], $courseId);
    $student_infos['grades'][$key]['course_id'] = $courseId[0];
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

$gradesUrl = "https://aeries.smhs.org/Parent/Grades.aspx";
curl_setopt($ch, CURLOPT_URL, $gradesUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
$gradesHtml = curl_exec($ch);
$gradesDom = new \HtmlParser\ParserDom($gradesHtml);
//html_put_files("grades/aeries_grades_home.html", $grade_Html);

//get current term
for($i =1; $i <= count($cids); $i++) {
    $tag = "#ctl00_MainContent_subGRD_DataDetails_ctl00_lblM".$i;
    $aTemp = $gradesDom->find($tag, 0)->getAttr('style');
    if($aTemp) {
        $text = $gradesDom->find($tag, 0)->getPlainText();
        $term = str_replace("Mk", "", $text);
        $student_infos['term'] = $term;
    }
}
// $gradesDom = new \HtmlParser\ParserDom($gradesHtml);
// $gradeTables = $gradesDom->find('#ctl00_MainContent_subStuTop_DataDetails_tblColor table table tr', 1);
// $a = $gradeTables->find('td.Data', 0)->getPlainText();
// echo $a ."\n";
// exit();

//$student_infos['attendance_summary'] = $aeries_dom->find('#ctl00_MainContent_dlAttSummaryPeriod', 0)->outerHtml();
$attend = $aeries_dom->find('#ctl00_MainContent_dlAttSummaryPeriod', 0)->find('tr', 10);
$a = $attend->find('td', 3)->getPlainText();
$attend = $aeries_dom->find('#ctl00_MainContent_dlAttSummaryPeriod', 0)->find('tr', 12);
$b = $attend->find('td', 3)->getPlainText();
$absences_num = $a+$b;
$query = 'INSERT INTO sinica_attendance_summary (
        uid, 
        studentid, 
        category_description, 
        excuse_count, 
        term,
        gradeyear,
        schoolname,
        createtime
    ) VALUES (
        '. $uid .',
        '. $student_infos['student_id'] .',
        "Absent",
        '.$absences_num.', 
        "'. $term .'",
        "'. $gradeYear .'",
        "'. $school .'",
        '.time().'
    )';
    $bdd->execute($query);

//get summary data 
$GN=$Term='';
foreach($grade_table as $table) {
    $grade_table_summary = $table->outerHtml();
    $student_infos['grade_table_summary'] = $grade_table_summary;
    foreach ($cids as $key => $value) {
        if(strlen($key) == 1) {
            $td_id = "#ctl00_MainContent_subGBS_DataDetails_ctl0".$key."_trGBKItem .Data";
        }else {
            $td_id = "#ctl00_MainContent_subGBS_DataDetails_ctl".$key."_trGBKItem .Data";
        }
        $x = 0;
        //echo $td_id."\n";
        foreach($grade_dom->find($td_id) as $tr) {            
            $cdata = $tr->getPlainText();
            // echo $cdata."\n";
            //echo "i=". $x."\n";
            switch($x) {
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
            $x++;
        }
         //echo $key." -------------------\n";
        
        //get grade detail for each courses;
        list($GN, $Term) = array_pad(explode('_', $value, 2), -2, null);
        $grede_detail_url = "https://aeries.smhs.org/Parent/Widgets/ClassSummary/RedirectToGradebook?GradebookNumber=".$GN."&Term=".$Term;
        curl_setopt($ch, CURLOPT_URL, $grede_detail_url);
        $grade_detail_Html = curl_exec($ch);
        //$grade_file = "grades/". $student_infos['student_id']."_".$value ."_grade_detail.html";
        //html_put_files($grade_file, $grade_detail_Html);
        $grade_detail_dom = new \HtmlParser\ParserDom($grade_detail_Html);
        $student_infos['grades'][$key]['courses_teacheremail'] = $grade_detail_dom->find('#ctl00_MainContent_subGBS_EMailTeacher', 0)->getPlainText();
        $y = 1;
        foreach($grade_detail_dom->find('#ctl00_MainContent_subGBS_assignmentsView table') as $table) {
            $detail = str_replace("'", "", $table->outerHtml());
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
$all = 'grades/aeries_'.$student_infos['student_id']."_all_grades.html";
html_put_files($all, var_export($student_infos, true));
//var_dump($student_infos['grades']);

//into db
foreach($student_infos['grades'] as $key => $summary) { 
    $status = get_last_summary_grade($uid, $student_infos['student_id'], $summary['course_id'], $summary['courses_score'], $term, $gradeYear, $bdd);
    $query ='INSERT INTO sinica_grade_summary (
        uid, 
        studentid, 
        courseid, 
        coursename, 
        average, 
        grade, 
        term,
        status,
        markingperiodid,
        gradelevel,
        schoolname,
        createtime
        ) VALUES (
            '. $uid .',
            '. $student_infos['student_id'] .',
            '. $summary['course_id'] .',
            "'. $summary['courses_name'] .'",
            "'. $summary['courses_score'] .'",
            "'. $summary['courses_grade'] .'",
            "'. $term .'",
            "'. $status .'",
            NULL,
            "'. $gradeYear .'",
            "'. $school .'",
            '. time() .'
            )';
    $bdd->execute($query);

    //grade detail insert to db
    $query = "INSERT INTO sinica_grade_recent_scores (
            uid, 
            studentid, 
            leadcourseid, 
            coursename, 
            teacher, 
            teacher_email, 
            category,
            recentscore, 
            recentscore_json,
            term,
            gradeyear,
            schoolname,
            createtime
        ) VALUES (
            ". $uid .",
            ". $student_infos['student_id'] .",
            ". $summary['course_id'] .",            
            '". $summary['courses_name'] ."',
            '". $summary['courses_teacher'] ."',
            '". $summary['courses_teacheremail'] ."',
            '". $summary['grade_detail_2'] ."',
            '". $summary['grade_detail_1'] ."',            
            '',
            '". $term ."',
            '". $gradeYear ."',
            '". $school ."',
            ". time() ."
        )";
        $bdd->execute($query);      
}
$all = 'grades/aeries_'.$student_infos['student_id']."_all.html";
//html_put_files($all, var_export($student_infos, true));

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
function get_last_summary_grade($uid, $studentid, $courseid, $cgrade, $term = null, $gradeyear = null, $bdd)
{
    //$bdd = new db();
    $query = "SELECT average 
        FROM sinica_grade_summary 
        WHERE 
            studentid = ". $studentid ." 
            AND uid = ". $uid ." 
            AND courseid = '". $courseid ."' 
            AND term = '". $term ."'
            AND gradelevel = '". $gradeyear ."'
            order by id desc 
            limit 1";
    //echo"check grade summary sql :: ". $query."\n";
    $result = $bdd->getOne($query);
    
    //check grade under 75 will send email to supervisor
    //$send = self::check_mail_to_teacher($uid, $cgrade, $bdd);
    //email log
    
    $average = $result['average'];
    // echo $cgrade."\n";
    // echo $average."\n";
    if ($average) {
        if ($cgrade > $average) {
            $status = 'up';
        } elseif ($cgrade < $average) {
            $status = 'down';
        } else {
            $status = 'equal';
        }
        return $status;
    } else {
        return null;
    }
}

function check_mail_to_teacher($uid, $cgrade, $bdd)
{
    //get email alert for supervisor
    //$bdd = new db();     
    $teacher_name = "Nathan";
    $student_name = "";
    $query = "SELECT name from users_field_data 
        where uid = ". $uid;
    $student_name = $bdd->getOne($query);
    
    $query = "SELECT ufd.name , ufd.mail 
        from users_field_data ufd 
        left join user__field_supervisor ufs on ufd.uid = ufs.field_supervisor_target_id
        where ufs.entity_id = ".$uid." and bundle = 'user'";
        // $query = "SELECT manager_teaher from users ";
    $teacher = $bdd->getAll($query);
    if (!empty($cgrade) && $cgrade < 75) {
        $subject = "Grade Notice - ". $student_name;
        $message = "Hello! ".$teacher[0]['name'] ." This is a simple Grade score under 70 email message test !!";
        $to = $teacher[0]['mail'];
        $from = "snowwind.z@gmail.com";
        $headers = "From:" . $from;
        @mail($to, $subject, $message, $headers);
        return true;
    } else {
        return false;
    }
}

function assoc_unique($arr, $key)
{
    $tmp_arr = array();
    foreach ($arr as $k => $v) {
        if (in_array($v[$key], $tmp_arr)) {
            //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true

            unset($arr[$k]);
        } else {
            $tmp_arr[] = $v[$key];
        }
    }
    sort($arr); //sort函数对数组进行排序
    return $arr;
}
