<?php

if (!function_exists('email_notify')) {

    function email_notify($recipient, $bug_id) {

        $email = user_get_email($recipient);
        $date = date(config_get('normal_date_format'));

        $subject = "This issue requires your attention ";
        $subject .= email_build_subject($bug_id);

        $contents = "\n an issue is created which needs your attention : \n\n";
        $contents .= "Date: $date \n";
        $contents .= string_get_bug_view_url_with_fqdn($bug_id, $recipient) . " \n\n";

        if (!is_blank($email)) {
            email_store($email, $subject, $contents);
            if (OFF == config_get('email_send_using_cronjob')) {
                email_send_all();
            }
        }
    }

}
if (!function_exists('project_get_name')) {

    function project_get_name($prj = 0) {
        if ($prj > 0) {
            $prj_table = db_get_table('mantis_project_table');
            $sql = "select name from $prj_table where id=$prj";
            $res = db_query_bound($sql);
            $row = db_fetch_array($res);
            return $row['name'];
        } else {
            return 'N/A';
        }
    }

}
if (!function_exists('custfield_get_name')) {

    function custfield_get_name($cf = 0) {
        if ($cf > 0) {
            $cf_table = db_get_table('mantis_custom_field_table');
            $sql = "select name from $cf_table where id=$cf";
            $res = db_query_bound($sql);
            $row = db_fetch_array($res);
            return $row['name'];
        } else {
            return 'N/A';
        }
    }

}
if (!function_exists('user_get_realname')) {

    function user_get_realname($u = 0) {
        if ($u > 0) {
            $u_table = db_get_table('mantis_user_table');
            $sql = "select name from $u_table where id=$u";
            $res = db_query_bound($sql);
            $row = db_fetch_array($res);
            return $row['realname'];
        } else {
            return 'N/A';
        }
    }

}