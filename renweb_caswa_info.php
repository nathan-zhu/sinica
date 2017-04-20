<?php
require 'vendor/autoload.php';
include('db_class.php'); // call db.class.php
$bdd = new db();

$student_infos = array();
$cookie_file = dirname(__FILE__)."/pic.cookie";
$school_type = "CAS-WA";
$logInUrl = "https://cas-wa.client.renweb.com/pw/index.cfm";
#$logInUrl = "http://www.renweb.com/";
$file = "grades/".$school_type."_all.html";

// 准备提交的表单数据之账号和密码。（这个是根据表单选项来的）
$data = "username=mia@liumeihui.com&password=sinica2015&UserType=PARENTSWEB-PARENT" ;
$cas_wa_grade_detail_url = "https://cas-wa.client.renweb.com/renweb/reports/parentsweb/parentsweb_reports.cfm?District=". $school_type ."&ReportType=Gradebook";
$proxy = "127.0.0.1:7070";

//catch all data will input All, current data will empty
$getAllData = "";
$uid = 49;
$gradeYear = '12th';
$school = 'Cascade Christian Schools';

$ch = curl_init();// 初始化
curl_setopt($ch, CURLOPT_URL, $logInUrl);// 网址
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
// 设置代理
curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
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
$rs_Login = curl_exec($ch);
$login_dom = new \HtmlParser\ParserDom($rs_Login);
if(!empty($login_dom->find('section.logout'))) {
    echo "Curl login ~~~\n";
}
https://cas-wa.client.renweb.com/pw/student/attendance.cfm?studentid=1208361#term_1208361_3
//open student information page
$url = "https://cas-wa.client.renweb.com/pw/student/";
curl_setopt($ch, CURLOPT_URL, $url);// 网址
$rs_Html = curl_exec($ch);
$html_dom = new \HtmlParser\ParserDom($rs_Html);
//get name and student id
$getId = $html_dom->find('a.student_tab', 0)->getAttr('href');
$studentid = preg_replace("/#tab/", "", $getId);
$student_infos['studentid'] = $studentid;
$getName = $html_dom->find('a.student_tab', 0)->getPlainText();
$student_infos['name'] = $getName;

//get all school period number
$renweb_laca_period = $html_dom->find('ul.tab-me-school-periods li');
$period_numbers = count($renweb_laca_period);
echo "period numbers: ". $period_numbers."\n";
//get sessionid and reporthash for grade detail
$renweb_sr_url = $html_dom->find('td.col_grade a', 0)->getAttr('href');
//echo $renweb_sr_url;
$grade_url = "https://cas-wa.client.renweb.com/pw/student/".$renweb_sr_url;
curl_setopt($ch, CURLOPT_URL, $grade_url);// 网址
$renweb_sr_Html = curl_exec($ch);
$grade_dom = new \HtmlParser\ParserDom($renweb_sr_Html);
$grade_sessionId = $grade_dom->find('input', 3)->getAttr('value');
$grade_reportHash = $grade_dom->find('input', 4)->getAttr('value');

/*//get grade detail
$grade_detail_url = "https://cas-wa.client.renweb.com/renweb/reports/parentsweb/parentsweb_reports.cfm?District=CAS-WA&ReportType=Gradebook&sessionid=".$grade_sessionId."&ReportHash=".$grade_reportHash."&SchoolCode=CAS-WA&StudentID=1208361&ClassID=18348&TermID=2";
curl_setopt($ch, CURLOPT_URL, $grade_detail_url);// 网址
$renweb_grade_detail_Html = curl_exec($ch);
//$grade_detail_dom = new \HtmlParser\ParserDom($renweb_grade_detail_Html);
$fgrade =fopen("grades/renweb/Q1_1208361_18348.html", "w");
fwrite($fgrade, $renweb_grade_detail_Html);
fclose($fgrade);*/

//get grade info and teach mail
$grade = array();
$i = 1;
if(empty($getAllData)) {
    $i = $period_numbers;
}
exit();
for($i; $i<=$period_numbers; $i++) {
    $tableid = "table#term_classes_". $studentid ."_". $i;
    $term = $Q = "Q". $i;
    foreach ($html_dom->find($tableid) as $table) {
        $j=0;        
        foreach ($table->find('tr') as $tr) {
            $k = 1;
            foreach ($tr->find('td a') as $tda) {
                $sgi_text = $tda->getPlainText();
                $sgi_attr = $tda->getAttr('href');
                switch($k) {
                    case 1:
                        $grade[$Q][$j]['col_subject'] = $sgi_text;
                        preg_match("/(?:\d*\.)?\d+$/i", $sgi_attr, $temp);
                        $classid = $temp[0];
                        $grade[$Q][$j]['classid'] = $classid;
                        break;
                    case 2:
                        $grade[$Q][$j]['col_grade'] = $sgi_text;
                        $letter_grade = get_gpa_grade($sgi_text);
                        $grade[$Q][$j]['letter_grade'] = $letter_grade['grade'];
                        $status = get_last_summary_grade($uid, $studentid, $classid, $sgi_text, $Q, $gradeYear, $bdd);
                        //get sessionid and reporhash for each grades
                        // $grade_url[$j] = "https://cas-wa.client.renweb.com/pw/student/". $sgi_attr;
                        // $grade[$Q][$j]['col_grade_detail'] = $sgi_attr;
                        // echo $grade_url[$j]."\n";
                        // curl_setopt($ch, CURLOPT_URL, $grade_url[$j]);// 网址
                        // $renweb_grade_Html[$j] = curl_exec($ch);
                        // //$fgrade =fopen("grades/".$grade[$Q][$j]['col_subject'].".html", "w");
                        // $fgrade =fopen("grades/".$Q."_" . $studentid ."_".$classid.".html", "w");
                        // fwrite($fgrade, $renweb_grade_Html[$j]);
                        // fclose($fgrade);

                        //get subject grade detail
                        $grade_detail_url = $cas_wa_grade_detail_url . "&sessionid=". $grade_sessionId ."&ReportHash=". $grade_reportHash ."&SchoolCode=". $school_type ."&StudentID=". $studentid ."&ClassID=". $classid ."&TermID=". $i;
                        curl_setopt($ch, CURLOPT_URL, $grade_detail_url);// 网址
                        $renweb_grade_detail_Html = curl_exec($ch);
                        $renweb_grade_detail_Html = preg_replace("(')", "\"", $renweb_grade_detail_Html);
                        $grade[$Q][$j]['col_grade_detail'] = $renweb_grade_detail_Html;

                        // $fgrade =fopen("grades/renweb/". $school_type. "_" . $studentid ."_".$classid."_". $Q .".html", "w"); 
                        // fwrite($fgrade, $renweb_grade_detail_Html);
                        // fclose($fgrade);

                        break;
                    case 3:
                        $grade[$Q][$j]['col_instructor'] = $sgi_text;
                        $mail = preg_replace("/mailto:/", "", $sgi_attr);
                        $grade[$Q][$j]['col_instructor_mail'] = $mail;
                        break;
                }
                $k++;
            }
            $j++;
        }
        // foreach ($table->find('td a') as $tda) {
        //     echo $tda->getPlainText() ."\n";
        //     $amail = $tda->getAttr('href');
        //     if(preg_match("/mailto/", $amail)) {
        //         $mail = preg_replace("/mailto:/", "", $amail);
        //         echo $mail."\n";
        //     }
        // }
    }
}
$student_infos['grade'] = $grade;
foreach($grade as $term => $value) {
    foreach($value as $key => $summary) {
        //recent score inser to db 
        
$query = "INSERT INTO sinica_grade_recent_scores (
        uid, 
        studentid,
        leadcourseid,
        coursename,
        recentscore, 
        recentscore_json,
        term,
        gradeyear,
        schoolname,
        createtime
        ) VALUES (
            ". $uid .",
            ". $studentid .",
            ". $summary['classid'] .",
            '". $summary['col_subject'] ."',
            '". $summary['col_grade_detail'] ."',
            '',
            '". $term ."',
            '". $gradeYear ."',
            '". $school ."',
            ".time()."
        )";
        $bdd->execute($query);

        //summary score inser to db 
        $query ='INSERT INTO sinica_grade_summary (
        uid,
        studentid,
        courseid,
        coursename,
        teacher,
        teacher_email,
        average,
        grade,
        term,
        status,
        gradelevel,
        schoolname,
        createtime
        ) VALUES (
            '. $uid .',
            '. $studentid .',
            '. $summary['classid'] .',
            "'. $summary['col_subject'] .'",
            "'. $summary['col_instructor'] .'",
            "'. $summary['col_instructor_mail'] .'",
            "'. $summary['col_grade'] .'",
            "'. $summary['letter_grade'] .' ",
            "'. $term .'",
            "'. $status .'",
            "'. $gradeYear .'",
            "'. $school .'",
            '. time() .'
            )';
        $bdd->execute($query); 
    }
}

//student attendance
$url = "https://cas-wa.client.renweb.com/pw/student/attendance.cfm?studentid=".$studentid;
curl_setopt($ch, CURLOPT_URL, $url);// 网址
$rsa_Html = curl_exec($ch);
$rsa_html_dom = new \HtmlParser\ParserDom($rsa_Html);

if($getAllData) {
    //get all attend period number last one will show all data
    $caswa_attend_period = $rsa_html_dom->find('ul.tab-me-school-periods li');
    $attend_period_numbers = count($caswa_attend_period);
}
else {
    //get attendance data of current term
    $attend_period_numbers = $period_numbers;
}
//get attendance details
$atid = "section#term_". $studentid ."_". $attend_period_numbers;
$rsa_section = $rsa_html_dom->find($atid);
$attend = array();
$attend_type = array(
    'PA' => 'Pre-Arranged Absence',
    'UT' => 'Tardy - Unexcused',
    'UA' => 'Absent - Unexcused',
    'ET' => 'Tardy - Excused',
    'EA' => 'Absent - Excused',
    'SA' => 'School Absence',    
);
$PA=$UT=$UA=$ET=$EA=$SA=0;
foreach ($rsa_section as $table) {
    $j = 0;
    foreach ($table->find('tr') as $tr) {
        $i = 1;
        foreach($tr->find('td') as $td) {
            $content = $td->getPlainText();
            switch($i) {
                case 1:
                    $attend[$j]['col_date'] = $content;
                    break;
                case 2:
                    $attend[$j]['col_class'] = $content;
                    break;
                case 3:
                    $attend[$j]['col_code'] = $content;
                    if($content == 'PA') {
                        $PA++;
                    }
                    elseif($content == 'UT' || $content == 'ET') {
                        $UT++;
                    }
                    elseif($content == 'UA' || $content == 'EA') {
                        $UA++;
                    }
                    // elseif($content == 'ET') {
                    //     $ET++;
                    // }
                    // elseif($content == 'EA') {
                    //     $EA++;
                    // }
                    elseif($content == 'SA') {
                        $SA++;
                    }
                    break;
                case 4:
                    $attend[$j]['col_description'] = $content;
                    break;
                default: 
                    $attend[$j]['col_comment'] = $content;
            }
            $i++;
            //echo $td->find('.col_date', 0)->getPlainText() ."\n";
        }
        $j++;
        //echo $td->find('.col_date', 0)->getPlainText() ."\n";
        //$td->getPlainText() ."\n";
    }
}
$attTotal['total']['Pre-Arranged Absence'] = $PA;
$attTotal['total']['Absent'] = $UA;
$attTotal['total']['Tardy'] = $UT;
// $attTotal['total']['EA'] = $EA;
// $attTotal['total']['ET'] = $ET;
$attTotal['total']['School Absence'] = $SA;

curl_close($ch);

$student_infos['attendance'] = $attend;
$student_infos['attendance_total'] = $attTotal;
//insert db attend summary
foreach($attTotal['total'] as $type => $aValue) {
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
        '. $studentid .',
        "'. $type .'",
        '. $aValue .', 
        "'. $term .'",
        "'. $gradeYear .'",
        "'. $school .'",
        '. time() .'
    )';        
    $bdd->execute($query);
}

    $output = "<table>";
    $output.="<thead>";
    $output.="<tr><th>Attendance Date</th><th>Class Name</th><th>Attendance Description</th><th>Comments</th></tr>";
    $output.="</thead>";
    $output.="<tbody>";
    
    foreach ($attend as $rows) {
        $output.="<tr>";
        $output.="<td>". $rows['col_date'] ."</td>";
        $output.="<td>". $rows['col_class'] ."</td>";
        $output.="<td>". $rows['col_description'] ."</td>";
        $output.="<td>". $rows['col_comment'] ."</td>";
        $output.="</tr>";
    }
    $output.="</tbody>";
    $output.="</table>";

    $attendence_json = json_encode($student_infos['attendance']);
    //insert attendance detail to db
    $query = "INSERT INTO sinica_attendance_details 
        (uid, 
         studentid,
         type,
         detail,
         detail_json,
         term,
         gradeyear,
         schoolname,
         createtime
        ) VALUES (
            ". $uid .",
            ". $studentid .",
            '',
            '". $output ."',
            '". $attendence_json ."',
            '". $term ."',
            '". $gradeYear ."',
            '". $school ."',
            ". time() ."
        )";
    $bdd->execute($query);


file_put_contents($file, var_export($student_infos,true));


function get_gpa_grade($score) {
    $data['grade'] = '';
    $data['gpa'] = '';
    if(!empty($score)) {
        if($score >= 97) {
            $grade = 'A+';
            $gpa = '4.0';
        }
        elseif ($score >=93 && $score < 97) {
            $grade = 'A';
            $gpa = '4.0';
        }
        elseif ($score >= 90 && $score < 93) {
            $grade = 'A-';
            $gpa = '3.7';
        }
        elseif ($score >= 87 && $score < 90) {
            $grade = 'B+';
            $gpa = '3.3';
        }
        elseif ($score >= 83 && $score < 87) {
            $grade = 'B';
            $gpa = '3.0';
        }
        elseif ($score >= 80 && $score < 83) {
            $grade = 'B-';
            $gpa = '2.7';
        }
        elseif ($score >= 77 && $score < 80) {
            $grade = 'C+';
            $gpa = '2.3';
        }
        elseif ($score >= 73 && $score < 77) {
            $grade = 'C';
            $gpa = '2.0';
        }
        elseif ($score >= 70 && $score < 73) {
            $grade = 'C-';
            $gpa = '1.7';
        }
        elseif ($score >= 67 && $score < 70) {
            $grade = 'D+';
            $gpa = '1.3';
        }
        elseif ($score >= 65 && $score < 67) {
            $grade = 'D';
            $gpa = '1.0';
        }
        else {
            $grade = 'F';
            $gpa = '0.0';
        }
        // elseif ($score >= 60 && $score < 64) {
        //     $grade = 'D-';
        //     $gpa = '0.7';
        // }
        $data['grade'] = $grade;
        $data['gpa'] = $gpa;
    }
    return $data;
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


