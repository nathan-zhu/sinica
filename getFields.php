<?php
require 'vendor/autoload.php';

$url = '/private/var/www/oop/renweb_school.html';
$sHtml = file_get_contents($url);
$html_dom = new \HtmlParser\ParserDom($sHtml);
//get name and student id
$p = $html_dom->find('ul#tab-me-student-list');

foreach ($p as $q) {
    foreach ($q->find('li') as $a) {
        $id = $a->find('a', 0)->getAttr('href');
        $studentid = preg_replace("/#tab/", "", $id);
        echo $studentid . "\n";

        $name = $a->getPlainText();
        echo $name . "\n";
    }
}
$grade = array();
//get grade info and teach mail
for($i = 1; $i<=4; $i++) {
    $termid = "#term_". $studentid ."_". $i;
    $tableid = "table#term_classes_". $studentid ."_". $i;
    $rss_term = $html_dom->find($termid);
    if(!empty($rss_term)) {
        echo "=====Q".$i."=====\n";
        $Q = "Q". $i;
        foreach ($rss_term as $term) {
            foreach ($term->find($tableid) as $table) {
                $j=0;
                foreach ($table->find('tr') as $tr) {
                    $k = 1;
                    foreach ($tr->find('td a') as $tda) {
                        $sgi_text = $tda->getPlainText();
                        $sgi_attr = $tda->getAttr('href');
                        switch($k) {
                            case 1:
                                $grade[$Q][$j]['col_subject'] = $sgi_text;
                                break;
                            case 2:
                                $grade[$Q][$j]['col_grade'] = $sgi_text;
                                $grade_url = $sgi_attr;
                                $grade[$Q][$j]['col_grade_detail'] = $sgi_attr;

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
    }
    else {
        echo "=====Q".$i."=====\n";
    }
}
var_dump($grade);
/*
//attendance
$url = '/private/var/www/oop/renweb_student_attendance.html';
$rsa_Html = file_get_contents($url);
$rsa_html_dom = new \HtmlParser\ParserDom($rsa_Html);

//get name and sid
$getId = $rsa_html_dom->find('a.student_tab', 0)->getAttr('href');
$getName = $rsa_html_dom->find('a.student_tab', 0)->getPlainText();
$studentid = preg_replace("/#tab/", "", $getId);

//get attendance details
$atid = "section#term_". $studentid ."_5";
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
        //$td->getPlainText() ."\n";
    }
}
var_dump($attend);*/
#https://cas-wa.client.renweb.com/renweb/reports/parentsweb/parentsweb_reports.cfm?District=CAS-WA&ReportType=Gradebook&sessionid=4733B358-CFA1-4C90-AAE5-FFA037932427&ReportHash=C2DC5CEEA2DBB0E8E81608EA48BBA374&SchoolCode=CAS-WA&StudentID=1208361&ClassID=18348&TermID=2
#https://cas-wa.client.renweb.com/renweb/reports/parentsweb/parentsweb_reports.cfm?District=CAS-WA&ReportType=Gradebook&sessionid=90C819E9-3E53-4B3F-96B8-5834F9930378&ReportHash=296D4FE8AFCE8272BCDF4E321BEAF063&SchoolCode=CAS-WA&StudentID=1208361&ClassID=18348&TermID=2
