<?php

class NotifyPlugin extends MantisPlugin {

    function register() {
        $this->name = 'Notify';
        $this->description = lang_get('plugin_notify_desc');
        $this->version = '1.00';
        $this->requires = array('MantisCore' => '1.2.0',);
        $this->author = 'Dennis Geus';
        $this->contact = 'Dennis@hands-off.it';
        $this->url = '';
    }

    function hooks() {
        return array(
            'EVENT_MENU_MANAGE' => 'mainmenu',
            'EVENT_REPORT_BUG' => 'checkandnotify'
        );
    }

    function mainmenu() {
        return array('<a href="' . plugin_page('config.php') . '">' . lang_get('plugin_notify_configlink') . '</a>');
    }

    function checkandnotify($p_event, $p_bugdata) {
        require_once( config_get('plugin_path') . 'notify' . DIRECTORY_SEPARATOR . 'notify_api.php' );
        $bug_table = db_get_table('mantis_bug_table');
        $cf_table = db_get_table('mantis_custom_field_string_table');
        $plug_table = plugin_table('notify');

        # check the table for entries, check if someone has to be notified and email the notification
        $bug_id = $p_bugdata->id;
        $proj_id = $p_bugdata->project_id;
        # get customfields for bug
        $customfields = array();
        $sql = "SELECT * FROM $cf_table WHERE bug_id=$bug_id";
        $sel = db_query_bound($sql);
        while ($row = db_fetch_array($sel)) {
            #bug found
            #get custom field and customfield value
            $customfields[$row['field_id']] = $row['value'];
        }
        # check if we need to notify someone
        $notifyusers = array();
        $n_sql = "SELECT * FROM $plug_table WHERE projectid=$proj_id";
        $n_sel = db_query_bound($n_sql);
        while ($n_row = db_fetch_array($n_sel)) {
            if ($n_row['customfield'] <> 0) {
                if (array_key_exists($n_row['customfield'], $customfields)) {
                    foreach ($customfields as $key => $val) {
                        if ($key == $n_row['customfield']) {
                            if ($val == $n_row['fieldvalue']) {
                                #yes
                                #add userid to notifyers array
                                $notifyusers[] = $n_row['userid'];
                            }
                        }
                    }
                }
            } else {
                # no customfield assigned so user must be notified when projectid is a match
                $notifyusers[] = $n_row['userid'];
            }
        }
        # send the email to the user(s)
        # if there are any users in the array
        if (count($notifyusers) > 0) {
            foreach ($notifyusers as $u) {
                email_notify($u, $bug_id);
            }
        }
    }

    function uninstall() {
        global $g_db;
        # remove the table created at installation
        $request = 'DROP TABLE ' . plugin_table('notify');
        $g_db->Execute($request);

        # IMPORTANT : erase information about the plugin stored in Mantis
        # Without this request, you cannot create the table again (if you re-install)
        $request = "DELETE FROM " . db_get_table('mantis_config_table') . " WHERE config_id = 'plugin_notify_schema'";
        $g_db->Execute($request);
    }

    function schema() {
        return array(
            # v1.00
            array('CreateTableSQL', array(plugin_table('notify'), "
				id  I   NOTNULL UNSIGNED ZEROFILL AUTOINCREMENT PRIMARY,
				projectid   I   default NULL,
				customfield I   default NULL,
                fieldvalue  C(250)  default NULL,
                userid  I   default NULL
				")),
        );
    }

}
