<?php

function dojo_admin_get_data() {
    $query_data = array();

    $db_instance = dojo_create_db_instance();

    $query_data['attendanceData'] = dojo_admin_attendance_data($db_instance);

    $query_data['attendanceTopScoresData'] = dojo_admin_attendance_top_scores($db_instance);

    $query_data['register'] = dojo_admin_register($db_instance);

    $query_data['users'] = dojo_get_users($db_instance);

    return $query_data;
}

function dojo_create_db_instance() {
    $host = exlog_get_option("external_login_option_db_host");
    $port = exlog_get_option("external_login_option_db_port");
    $user = exlog_get_option("external_login_option_db_username");
    $password = exlog_get_option("external_login_option_db_password");
    $dbname = exlog_get_option("external_login_option_db_name");

    $host .= ":" . $port;

    return new wpdb(
        $user,
        $password,
        $dbname,
        $host
    );
}

function dojo_admin_attendance_top_scores($db_instance) {
    $query_string = 'SELECT User.NickName, User.UserType, COUNT(DISTINCT Register.DojoID) AS count ' .
        'FROM Register LEFT JOIN User ON Register.UserID = User.UserID ' .
        'GROUP BY Register.UserID ' .
        'ORDER BY count DESC;';

    return $db_instance->get_results($query_string, ARRAY_A);
}

function dojo_admin_attendance_data($db_instance) {
    $data = array();

//    All
    $query_string = 'SELECT Register.DojoID, Dojo.DojoDate, COUNT(DISTINCT Register.UserID) AS count ' .
        'FROM Register LEFT JOIN Dojo ON Dojo.DojoID = Register.DojoID GROUP BY DojoID;';

    $data['all'] =  $db_instance->get_results($query_string, ARRAY_A);

//    Student
    $query_string = 'SELECT Register.DojoID, COUNT(DISTINCT Register.UserID) AS count ' .
        'FROM Register LEFT JOIN User ON Register.UserID = User.UserID ' .
        'WHERE User.UserType = "Student" GROUP BY Register.DojoID;';

    $data['student'] =  $db_instance->get_results($query_string, ARRAY_A);

//    Mentor
    $query_string = 'SELECT Register.DojoID, COUNT(DISTINCT Register.UserID) AS count ' .
        'FROM Register LEFT JOIN User ON Register.UserID = User.UserID ' .
        'WHERE User.UserType = "Mentor" GROUP BY Register.DojoID;';

    $data['mentor'] =  $db_instance->get_results($query_string, ARRAY_A);

//    RETURN DATA
    return $data;
}

function dojo_get_users($db_instance) {
    $requiredFields = "NickName, FirstName, LastName, UserType, DOB, ContactNumber";

    $query_string = 'SELECT ' . $requiredFields . ' FROM USER;';
    return $db_instance->get_results($query_string, ARRAY_A);
}

function dojo_admin_register($db_instance) {
    $requiredFields = "User.NickName, User.FirstName, User.LastName, User.UserType, R1.Login, R1.Logout, User.DOB, User.ContactNumber";

    $query_string = 'SELECT ' . $requiredFields . ' ' .
            'FROM Register AS R1 ' .
            'LEFT JOIN User ON User.UserID = R1.UserID ' .
            'WHERE R1.Login = (SELECT MAX(R2.Login) ' .
            'FROM Register AS R2 ' .
            'WHERE R2.UserID = R1.UserID)';

    return $db_instance->get_results($query_string, ARRAY_A);
}

function dojo_front_end_register() {

    $db_instance = dojo_create_db_instance();

    $requiredFields = "User.NickName, User.FirstName, User.LastName, User.UserType, R1.Login, R1.Logout";

    $query_string = 'SELECT ' . $requiredFields . ' ' .
        'FROM Register AS R1 ' .
        'LEFT JOIN User ON User.UserID = R1.UserID ' .
        'WHERE R1.Login = (SELECT MAX(R2.Login) ' .
        'FROM Register AS R2 ' .
        'WHERE R2.UserID = R1.UserID AND R2.Login > CURDATE())';

    return $db_instance->get_results($query_string, ARRAY_A);
}