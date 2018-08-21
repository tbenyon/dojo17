<?php

function dojo_admin_get_data() {
    $query_data = array();

    $host = exlog_get_option("external_login_option_db_host");
    $port = exlog_get_option("external_login_option_db_port");
    $user = exlog_get_option("external_login_option_db_username");
    $password = exlog_get_option("external_login_option_db_password");
    $dbname = exlog_get_option("external_login_option_db_name");

    $host .= ":" . $port;

    $db_instance = new wpdb(
        $user,
        $password,
        $dbname,
        $host
    );

    $query_data['attendanceData'] = dojo_admin_attendance_data($db_instance);

    $query_data['attendanceTopScoresData'] = dojo_admin_attendance_top_scores($db_instance);

    return $query_data;
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

/*
Attendance top scores

const queryString = 'SELECT User.NickName, User.UserType, COUNT(DISTINCT Register.DojoID) AS count ' +
            'FROM Register LEFT JOIN User ON Register.UserID = User.UserID ' +
            'GROUP BY Register.UserID ' +
            'ORDER BY count DESC;';

Attendance count ALL
            const queryString = 'SELECT Register.DojoID, Dojo.DojoDate, COUNT(DISTINCT Register.UserID) AS count ' +
                'FROM Register LEFT JOIN Dojo ON Dojo.DojoID = Register.DojoID GROUP BY DojoID;';


Attendance count Students
            const queryString = 'SELECT Register.DojoID, COUNT(DISTINCT Register.UserID) AS count ' +
                'FROM Register LEFT JOIN User ON Register.UserID = User.UserID ' +
                'WHERE User.UserType = "Student" GROUP BY Register.DojoID;';

Attendance count Mentors
            const queryString = 'SELECT Register.DojoID, COUNT(DISTINCT Register.UserID) AS count ' +
                'FROM Register LEFT JOIN User ON Register.UserID = User.UserID ' +
                'WHERE User.UserType = "Mentor" GROUP BY Register.DojoID;';




Days to birthday

const daysToBirthdayQueryString = 'SELECT ' +
                'NickName, ' +
                'abs(IF(' +
                'right(curdate(), 5) >= right(DOB, 5), ' +
                'datediff(curdate(), ' +
                'concat(year(curdate() + INTERVAL 1 YEAR), ' +
                'right(DOB, 6))), ' +
                'datediff(concat(year(curdate()), ' +
                'right(DOB, 6)), ' +
                'curdate()))) ' +
                'AS DaysToBirthday ' +
                'FROM User ' +
                'ORDER BY DaysToBirthday;';


Users

        if (requiredFor === "register") {
            requiredFields = "User.NickName, User.FirstName, User.LastName, User.UserType, R1.Login, R1.Logout";
        } else if (requiredFor === "registerDetailed") {
            requiredFields = "User.NickName, User.FirstName, User.LastName, User.UserType, R1.Login, R1.Logout, User.DOB, User.ContactNumber";
        } else if (requiredFor === "members") {
            requiredFields = "User.NickName, R1.Login, User.UserType"
        } else {
            console.error("Required parameter for getUsers query is invalid.");
            reject(new Error("Required parameter for getUsers query is invalid."));
        }

        var userPromise = new Promise(function(resolve, reject) {
            const userQueryString = 'SELECT ' + requiredFields + ' ' +
                'FROM Register AS R1 ' +
                'LEFT JOIN User ON User.UserID = R1.UserID ' +
                'WHERE R1.Login = (SELECT MAX(R2.Login) ' +
                'FROM Register AS R2 ' +
                'WHERE R2.UserID = R1.UserID);';


