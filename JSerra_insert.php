<?php 
include('db_class.php'); // call db.class.php
$bdd = new db(); // create a new object, class db()
// $User = $bdd->getOne('SELECT uid from users_field_data where name = "yiman.bai"'); // 1 line selection, return 1 line
// echo $User['uid']; // display the id

// $Users = $bdd->getAll('SELECT id, firstname, lastname FROM users'); // select ALL from users    
// $nbrUsers = count($Users); // return the number of lines
// echo $nbrUsers.' users in the database<br />';
// foreach($Users as $user) { // display the list
//     echo $user['id'].' - '.$user['firstname'].' - '.$user['lastname'];  
// }

// $query = $bdd->execute('UPDATE users SET firstname="firstname2", lastname="lastname2" WHERE id=20');
// $query = $bdd->execute('INSERT INTO users (firstname, lastname) VALUES ("firstname1", "lastname1")');

$MarkingPeriodId = array(
    '54' => "Q1", //FIRST QUARTER
    '55' => 'Q2', //SECOND QUARTER
    '56' => 'S1', //SEMESTER 1 GRAD
    '57' => 'MEX', //MIDYEAR EXAM
    '59' => 'Q3', //
    '60' => 'Q4', //
    '61' => 'S2', //SEMESTER 2 GRADE
    '62' => 'F2', //FINAL EXAM
    '64' => 'C1', //COMMENT ONE
    '65' => 'C2', //COMMENT TWO
);
$file="/private/var/www/oop/html-parser/grades/JSerraCatholicHS_9192_54_total.html";
$grade = file_get_contents($file);
$data = json_decode($grade, true);
foreach ($data['Data'] as $summary) {
    switch($summary['StudentId']) {
        case 9192:
            $uid = 25;
            break;
        case 10549:
            $uid = 26;
            break;
        case 9207:
            $uid = 24;
            break;
        case 9490:
            $uid = 11;
            break;   
    }
    $term = $MarkingPeriodId['54'];

    $query ='INSERT INTO sinica_grade_summary (uid, studentid, courseid, coursename, average, grade, term) VALUES ('. $uid .','. $summary['StudentId'] .','. $summary['SectionId'] .',"'. $summary['CourseName'] .'","'. $summary['Average'] .'","'. $summary['GradeSymbol'] .'","'. $term .'")';
    $bdd->execute($query);
    echo $summary['CourseName']."\n";
    echo $summary['Average']."\n";
    echo $summary['GradeSymbol']."\n";
    echo $summary['StudentId']."\n";
    echo $summary['SectionId']."\n";
    echo $summary['MarkingPeriodId']."\n";
}
?>