<?php
require 'vendor/autoload.php';

$student_infos = array();
$cookie_file = dirname(__FILE__)."/pic.cookie";
$school_type = "LA-CA";
$logInUrl = "https://la-ca.client.renweb.com/pw/index.cfm";
#$logInUrl = "http://www.renweb.com/";
$file = "grades/".$school_type."_all.html";

// 准备提交的表单数据之账号和密码。（这个是根据表单选项来的）
$data = "username=mia@liumeihui.com&password=sinica2015&UserType=PARENTSWEB-PARENT" ;
$la_ca_grade_detail_url = "https://la-ca.client.renweb.com/renweb/reports/parentsweb/parentsweb_reports.cfm?District=". $school_type ."&ReportType=Gradebook";
$proxy = "127.0.0.1:7070";

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

//open student information page
$url = "https://la-ca.client.renweb.com/pw/student/";
curl_setopt($ch, CURLOPT_URL, $url);// 网址
$rs_Html = curl_exec($ch);
$html_dom = new \HtmlParser\ParserDom($rs_Html);
//get name and student id
$getId = $html_dom->find('a.student_tab', 0)->getAttr('href');
$studentid = preg_replace("/#tab/", "", $getId);
$student_infos['id'] = $studentid;
$getName = $html_dom->find('a.student_tab', 0)->getPlainText();
$student_infos['name'] = $getName;

//get all school period number
$renweb_laca_period = $html_dom->find('ul.tab-me-school-periods li');
$period_numbers = count($renweb_laca_period);

//get sessionid and reporthash for grade detail
$renweb_sr_url = $html_dom->find('td.col_grade a', 0)->getAttr('href');
//echo $renweb_sr_url;
$grade_url = "https://la-ca.client.renweb.com/pw/student/".$renweb_sr_url;
curl_setopt($ch, CURLOPT_URL, $grade_url);// 网址
$renweb_sr_Html = curl_exec($ch);
$grade_dom = new \HtmlParser\ParserDom($renweb_sr_Html);
$grade_sessionId = $grade_dom->find('input', 3)->getAttr('value');
$grade_reportHash = $grade_dom->find('input', 4)->getAttr('value');

//get student grade info
$grade = array();
for($i = 1; $i<=$period_numbers; $i++) {
    $Q = "Q". $i;
    $tableid = "table#term_classes_". $studentid ."_". $i;
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
                        break;
                    case 2:
                        $grade[$Q][$j]['col_grade'] = $sgi_text;
                        //get sessionid and reporhash for each grades
                        // $grade_url[$j] = "https://la-ca.client.renweb.com/pw/student/". $sgi_attr;
                        // $grade[$Q][$j]['col_grade_detail'] = $sgi_attr;
                        // echo $grade_url[$j]."\n";
                        // curl_setopt($ch, CURLOPT_URL, $grade_url[$j]);// 网址
                        // $renweb_grade_Html[$j] = curl_exec($ch);
                        // //$fgrade =fopen("grades/".$grade[$Q][$j]['col_subject'].".html", "w");
                        // $fgrade =fopen("grades/".$Q."_" . $studentid ."_".$classid.".html", "w");
                        // fwrite($fgrade, $renweb_grade_Html[$j]);
                        // fclose($fgrade);

                        //get subject grade detail
                        $grade_detail_url = $la_ca_grade_detail_url . "&sessionid=". $grade_sessionId ."&ReportHash=". $grade_reportHash ."&SchoolCode=". $school_type ."&StudentID=". $studentid ."&ClassID=". $classid ."&TermID=". $i;
                        curl_setopt($ch, CURLOPT_URL, $grade_detail_url);// 网址
                        $renweb_grade_detail_Html = curl_exec($ch);
                        $grade[$Q][$j]['col_grade_detail'] = $renweb_grade_detail_Html;

                        //$fgrade =fopen("grades/renweb/Q1_1208361_18348.html", "w");
                        $fgrade =fopen("grades/renweb/".$school_type. "_" . $studentid ."_".$classid."_". $Q .".html", "w");
                        fwrite($fgrade, $renweb_grade_detail_Html);
                        fclose($fgrade);

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
    }
}
$student_infos['grade'] = $grade;

//student attendance
$url = "https://la-ca.client.renweb.com/pw/student/attendance.cfm?studentid=".$studentid;
curl_setopt($ch, CURLOPT_URL, $url);// 网址
$rsa_Html = curl_exec($ch);
$rsa_html_dom = new \HtmlParser\ParserDom($rsa_Html);

//get all attend period number
$laca_attend_period = $rsa_html_dom->find('ul.tab-me-school-periods li');
$attend_period_numbers = count($laca_attend_period);

//get attendance details
$atid = "section#term_". $studentid ."_". $attend_period_numbers;
$rsa_section = $rsa_html_dom->find($atid);
$attend = array();
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
    }
}
curl_close($ch);

$student_infos['attendance'] = $attend;
file_put_contents($file, var_export($student_infos,true));