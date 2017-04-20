<?php
require 'vendor/autoload.php';
include('db_class.php'); // call db.class.php
$bdd = new db(); 

$school = "JSerra Catholic High School";
$file = "login/pp_grade.html";
$cookie_file = dirname(__FILE__)."/cookies/JSerraCatholicHS.cookie";
$base_url = "https://www.plusportals.com";
$logInUrl = "https://www.plusportals.com/JSerraCatholicHS";
// 准备提交的表单数据之账号和密码。（这个是根据表单选项来的）
$data = "UserName=miaali@hotmail.com&Password=Sinica88&RememberMe=false";
$Agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36';

$gradeYear = '11th';
//$term = 'Q3';
$Catch_All_Term_Datas = "";

//$proxy = "127.0.0.1:7070";
// $MarkingPeriodId = array(
//     '54' => "Q1", //FIRST QUARTER
//     '55' => 'Q2', //SECOND QUARTER
//     '56' => 'S1', //SEMESTER 1 GRAD
//     '57' => 'MEX', //MIDYEAR EXAM
//     '59' => 'Q3', //
//     '60' => 'Q4', //
//     '61' => 'S2', //SEMESTER 2 GRADE
//     '62' => 'F2', //FINAL EXAM
//     '64' => 'C1', //COMMENT ONE
//     '65' => 'C2', //COMMENT TWO
// );

$attendance_type = array(
    0 => 'Absent',
    1 => 'Tardy',
    2 => 'Dismissal',
    3 => 'Incident'
);
//school student id match with uid of user in db of lmh
$suid = array(
    9192 => 25,
    // 10549 => 26,
    // 9207 => 24,
    // 9490 => 11,
);

$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
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

$contents = curl_exec($ch);

//##get student list page cache token and studentid.
$pp_list_url = "https://www.plusportals.com/Account/ApplyRole?mode=0";

curl_setopt($ch, CURLOPT_URL, $pp_list_url);// 学生列表页面
$pp_list_Html = curl_exec($ch);
html_put_files("login/aaaa.html", $pp_list_Html);

//get student numbets
$pp_list_Html_dom = new \HtmlParser\ParserDom($pp_list_Html);
$pp_get_all_students = $pp_list_Html_dom->find('div.txtnme');

$pp_infos = array();

//get student id
$pp_bia = $pp_list_Html_dom->find('div.blk-imgr a');
$j = 0;
foreach($pp_bia as $alinks) {
    $get_sid[$j] = $alinks->getAttr('href');
    preg_match("/\d.*(?=\?token=)/", $get_sid[$j], $sid[$j]);
    $j++;
}

$attend_file = 'attend.html';

//get MarkingPeriodId
$markingperiod_url = "https://www.plusportals.com/ParentStudentDetails/GetMarkingPeriod";  
curl_setopt($ch, CURLOPT_URL, $markingperiod_url);// 学生列表页面
$MarkingPeriod_Html = curl_exec($ch);
$markingperiod_data = json_decode($MarkingPeriod_Html, true);
//var_dump($markingperiod_data);
foreach ($markingperiod_data as $mp) {
    $MarkingPeriodId[$mp['MarkingPeriodId']] = $mp['MarkingPeriodName'];
}

//catch information for each students.
$y = 0;
for ($i = 0; $i < count($pp_get_all_students); $i++) {
    $y = $i+$i;
    //get student name and id
    $pp_infos[$i]['name'] = $pp_list_Html_dom->find('div.txtnme', $i)->getPlainText();
    $img_link = $get_token_link = $pp_list_Html_dom->find('img.blk-img-crved', $i)->getAttr('src');
    $token[$i] = preg_replace("/.*(&token=)/", "", $get_token_link);
    $pp_infos[$i]['token'] = $token[$i];
    $pp_infos[$i]['student_id'] = $sid[$i][0];
    $pp_infos[$i]['student_img'] = $base_url.$img_link;

    //get db uid
    $uid = $suid[$pp_infos[$i]['student_id']];

    //get student detail page
    //https://www.plusportals.com/ParentStudentDetails/ParentStudentDetails/9192?isProgressReport=False&token=FB223F4CCAA8F2A4E9E72FE845C37303AA44A2DDF950074538458B6C83E4E9E0
    $pp_score_url = "https://www.plusportals.com/ParentStudentDetails/ParentStudentDetails/". $pp_infos[$i]['student_id'] ."?isProgressReport=False&token=". $token[$i];
    curl_setopt($ch, CURLOPT_URL, $pp_score_url);// 学生成绩详情页面
    $pp_score_Html = curl_exec($ch);
    //get 当前学期 id
    $score_dom = new \HtmlParser\ParserDom($pp_score_Html);
    $CurrentMarkingPeriodId = $score_dom->find('input#CurrentMarkingPeriodId', 0)->getAttr('value');
    echo "CurrentMarkingPeriodId: ". $CurrentMarkingPeriodId ."\n";
    //get current term name
    $term = $MarkingPeriodId[$CurrentMarkingPeriodId];
    
    //get attendence summary for each students
    $x = $k = 0;
    $pdn_attendance = $pp_list_Html_dom->find('div.pdn-attendance', $y);
    foreach($pdn_attendance->find('div.box-in') as $boxin) {
        $x++;
        list($ty, $boxin_text) = explode(": ", $boxin->getPlainText());
        $pp_infos[$i]['attendence_summary'][$attendance_type[$k]] = $boxin_text;
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
            '. $pp_infos[$i]['student_id'] .',
            "'. $attendance_type[$k] .'",
            '.$boxin_text.', 
            "'. $term .'",
            "'. $gradeYear .'",
            "'. $school .'",
            '.time().'
        )';        
        $bdd->execute($query);
        $k++;
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

        //insert to db
        $query = "INSERT INTO sinica_attendance_details 
            (uid, 
             studentid, 
             type,
             detail, 
             term,
             gradeyear,
             schoolname,
             createtime
            ) VALUES (
                ". $uid .",
                ". $pp_infos[$i]['student_id'] .",
                '". $type ."',
                '". $pp_infos[$i]['attendence_detail'][$type] ."',
                '". $term ."',
                '". $gradeYear ."',
                '". $school ."',
                ". time() ."
            )";
        $bdd->execute($query);
    }

    /*// get part of recent score json data for current semester
    $recent_json = $score_dom->find('div.bgf9', 1)->outerHtml();
    $tmp = explode("\n", $recent_json);
    for($j = 0; $j < count($tmp); $j ++) {
        $tmp[$j] = preg_replace("(')", "\"", $tmp[$j]);
    }
    $tmpj = implode("\n", $tmp);
    //var_dump($tmpj);
    $recent_score = $tmpj;
    //preg_match("/({\"Data\").*(])/", $recent_json, $rscores);
    preg_match("/({\"Data\").*\w}{1}/", $recent_json, $rscores);
    
    $recent_file = "login/recent_json.html";
    $recent_score_json = $rscores[0];
    // html_put_files($recent_file, $rscores[0]);

    //recent score inser to db 
    $query = "INSERT INTO sinica_grade_recent_scores (
        uid, 
        studentid, 
        recentscore, 
        recentscore_json,
        term,
        gradeyear,
        schoolname,
        createtime
        ) VALUES (
            ". $uid .",
            '". $pp_infos[$i]['student_id'] ."',
            '". $recent_score ."',
            '". $recent_score_json ."',
            '". $MarkingPeriodId[$CurrentMarkingPeriodId] ."',
            '". $gradeYear ."',
            '". $school ."',
            ".time()."
        )";
    $bdd->execute($query);*/

    //get summary and detail score datas
    $z = 0;
    foreach($MarkingPeriodId as $key => $value) {
        //get school MarkingPeriodId name
        $term = $MarkingPeriodId[$key];
        //$Catch_All_Term_Datas empty, will get current term data
        if($CurrentMarkingPeriodId == $key && empty($Catch_All_Term_Datas)) {
            //echo "Current --- ". $term ."\n";
            $coursesList[$key] = get_summary_scores_data($uid, $pp_infos[$i]['student_id'], $key, $term, $gradeYear, $school, $Agent, $ch, $bdd);
            break;
        }
        elseif (!empty($Catch_All_Term_Datas)) {
            //echo "All --- ".$term."\n";
            //get all term datas
            $coursesList[$key] = get_summary_scores_data($uid, $pp_infos[$i]['student_id'], $key, $term, $gradeYear, $school, $Agent, $ch, $bdd);
            //stop loop for not current term period id
            if($CurrentMarkingPeriodId == $key) {
                break;
            }
        }
    }
    //var_dump($coursesList);
    
    // each term get same courses cotent for coursellist,
    // so only loop current term courses, $coursesList[$CurrentMarkingPeriodId];
    foreach ($coursesList[$CurrentMarkingPeriodId] as $SectionId => $coursesName) {
        $msc = getMillisecond();
        //get Category Averages for each Course
        $pp_grade_detail = "https://www.plusportals.com/ParentStudentDetails/ShowScoresDetails?_=". $msc ."&sectionId=". $SectionId;
        echo $pp_grade_detail."\n";
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
        // $pp_infos[$i]['grades_detail'][$SectionId]['grades_summary_total'] = $category_averages_dom->find('div table', 1)->outerHtml();

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
        $pp_infos[$i]['grades_detail'][$SectionId]['scores_detail'] = $pp_grade_course_detail_Html[$SectionId];
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
            ". $pp_infos[$i]['student_id'] .",
            ". $SectionId .",            
            '". $coursesName ."',
            '". $pp_infos[$i]['grades_detail'][$SectionId]['teacher_name'] ."',
            '" . $pp_infos[$i]['grades_detail'][$SectionId]['teacher_email'] ."',
            '" . $pp_infos[$i]['grades_detail'][$SectionId]['category_averages'] ."',
            '',            
            '". $pp_infos[$i]['grades_detail'][$SectionId]['scores_detail'] ."',
            '". $term ."',
            '". $gradeYear ."',
            '". $school ."',
            ". time() ."
        )";
        $bdd->execute($query);
    }

}
//var_dump($pp_infos);
curl_close($ch);

function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());     
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);  
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

function get_summary_scores_data($uid, $student_id, $key, $term, $gradeYear, $school, $Agent, $ch, $bdd) {
    //get grade summary from json data
    $grade_data = "sort=&group=&filter=";
    $url = "https://www.plusportals.com/ParentStudentDetails/ShowGridProgressQuickViewInfo?markingPeriodId=". $key ."&studentId=". $student_id;
    curl_setopt($ch, CURLOPT_URL, $url);// 网址
    curl_setopt($ch, CURLOPT_USERAGENT, $Agent);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $grade_data);// POST数据
    $grade_summary_Html[$key] = curl_exec($ch);
    //$temfile = "grades/JSerraCatholicHS_". $pp_infos[$i]['student_id']."_".$key."_summary_grade.html";
    //file_put_contents($temfile, $grade_summary_Html[$key]);

    //grade summary insert to sinica_grade_summary table
    $grade_summary = json_decode($grade_summary_Html[$key], true);
    //var_dump($grade_summary);
    foreach ($grade_summary['Data'] as $summary) {        
        $coursesList[$summary['SectionId']] = $summary['CourseName'];
        //$term = $MarkingPeriodId[$key];
        //check grade status
        $status = get_last_summary_grade($uid, $summary['StudentId'], $summary['SectionId'], $summary['Average'], $term, $gradeYear, $bdd);
        
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
                '. $summary['StudentId'] .',
                '. $summary['SectionId'] .',
                "'. $summary['CourseName'] .'",
                "'. $summary['Average'] .'",
                "'. $summary['GradeSymbol'] .'",
                "'. $term .'",
                "'. $status .'",
                '. $key .',
                "'. $gradeYear .'",
                "'. $school .'",
                '. time() .'
                )';
        $bdd->execute($query);
    } 
    return $coursesList;
}

//attendence
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9207&showtype=Absent
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9192&showtype=Tardy
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9192&showtype=Dismissal
//https://www.plusportals.com/ParentStudentDetails/ShowDetails/?studentId=9207&showtype=Incident
//
       /*
        $z++;  
        $url = "https://www.plusportals.com/ParentStudentDetails/ShowGridProgressInfo?markingPeriodId=". $key ."&isGroup=false";  
        //get student marking Period 
        $data = "";
        // 基本配置
        curl_setopt($ch, CURLOPT_URL, $url);// 网址
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
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
                            echo $pp_grade_detail."\n";
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
                            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);// POST数据
                            $pp_grade_course_detail_Html[$SectionId] =  curl_exec($ch);
                            $pp_infos[$i]['grades_detail'][$SectionId]['course_detail'] = $pp_grade_course_detail_Html[$SectionId];

                            //$path_file = "scores_grade_detail.html";
                            //$temfile = "login/". $pp_infos[$i]['student_id']."_".$key."_".$grades['SectionId']."_".$path_file;
                            //file_put_contents($temfile, $pp_grade_course_detail_Html[$SectionId]);
                        }
                    }
                }
            }
        }
        // $temfile = "login/0000_". $pp_infos[$i]['student_id']."_".$key."_total.html";
        // file_put_contents($temfile, var_export($pp_infos, true), FILE_APPEND);
        */
