<?php
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
html_page_top1(lang_get('plugin_notify_title'));
html_page_top2();
print_manage_menu();

require_once( config_get('plugin_path') . 'notify' . DIRECTORY_SEPARATOR . 'notify_api.php' );

# tables
$plug_table = plugin_table('notify');
$prj_table = db_get_table('mantis_project_table');
$cf_table = db_get_table('mantis_custom_field_table');
$usr_table = db_get_table('mantis_user_table');
$usrpr_table = db_get_table('mantis_project_user_list_table');

if (isset($_POST['projectid'])) {
    $projectid = $_POST['projectid'];
    $message = '';
} else {
    $projectid = 0;
    $message = '<font color="red">' . lang_get('plugin_notify_message') . '</font>';
}

if (isset($_POST['save'])) {
    # now insert it
    $customfield = $_POST['customfield'];
    $fieldvalue = $_POST['fieldvalue'];
    $userid = $_POST['userid'];
    $ins_sql = "INSERT INTO $plug_table 
                    SET projectid = $projectid, 
                    customfield = $customfield,
                    fieldvalue = '$fieldvalue',
                    userid = $userid
                    ";
    $insert = db_query_bound($ins_sql);
    if ($insert) {
        # okay?
    } else {
        $message = lang_get('plugin_notify_entryerror');
    }
}

if (isset($_POST['del'])) {
    # now delete it
    $id = $_POST['lineid'];
    $del_sql = "DELETE FROM $plug_table WHERE id=$id";
    $delete = db_query_bound($del_sql);
    if ($delete) {
        # okay?
    } else {
        $message = lang_get('plugin_notify_delerror');
    }
}
?>
<br />
<table class=width100 border="0">
    <tr><!-- Row 1-->
        <td><!-- cell 1 -->
            <table class="width100" cellspacing="0">
                <tr <?php echo helper_alternate_class() ?>>
                    <td class="form-title" colspan="5"><?php echo lang_get('plugin_notify_desc'); ?></td>
                </tr>
                <tr <?php echo helper_alternate_class() ?>>
                    <td class="form-title" ><?php echo lang_get('project'); ?>:</td>
                    <td class="form-title" ><?php echo lang_get('plugin_notify_customfield'); ?>:</td>
                    <td class="form-title" ><?php echo lang_get('plugin_notify_fieldvalue'); ?>:</td>
                    <td class="form-title" ><?php echo lang_get('plugin_notify_user'); ?>:</td>
                    <td class="form-title" ></td>
                </tr>
<?php
$sql = "SELECT * FROM $plug_table WHERE 1";
$result = db_query_bound($sql);
while ($row = db_fetch_array($result)) {
    ?>
                    <tr <?php echo helper_alternate_class() ?>>
                        <td><?php echo project_get_name($row['projectid']); ?></td>
                        <td><?php echo custfield_get_name($row['customfield']); ?></td>
                        <td><?php echo $row['fieldvalue']; ?></td>
                        <td><?php echo user_get_realname($row['userid']); ?></td>
                        <td> <form name="removeline" id="removeline" method="post" action="<?php echo plugin_page('config') ?>">
                                <input type="hidden" name="lineid" value="<?php echo $row['id']; ?>" />
                                <button name="del" id="del"><img src="<?php echo config_get('path'); ?>plugins/notify/img/glyphicons_016_bin.png" height="16" border="0" /></button>
                            </form>
                        </td>
                    </tr>
    <?php
}
?>
            </table>
        </td><!-- /cell 1 -->
    </tr><!-- /row 1 -->
    <tr><!-- row 2 -->
        <td><!-- cell 2-->
            <form name="addline" id="addline" method="post" action="<?php echo plugin_page('config') ?>">
                <table class="width100">
                    <tr class="center">
                        <td colspan="5"><?php echo $message; ?></td>
                    </tr>
                    <tr>
                        <td>
                            <label for="projectid"><?php echo lang_get('project'); ?></label>
                            <select name="projectid" onchange="this.form.submit()">
                                <option value=""><?php echo lang_get('plugin_notify_selproject'); ?></option>
<?php
$pr_sql = "SELECT id FROM $prj_table WHERE 1 ORDER BY name ASC";
$pr_result = db_query_bound($pr_sql);
while ($pr = db_fetch_array($pr_result)) {
    if ($pr['id'] == $projectid) {
        $selected = 'selected';
    } else {
        $selected = '';
    }
    echo '<option ' . $selected . ' value="' . $pr['id'] . '">' . htmlspecialchars(project_get_name($pr['id'])) . '</option>';
}
?>
                            </select></td>
                        <td>
                            <label for="customfield"><?php echo lang_get('plugin_notify_customfield'); ?></label>
                            <select name="customfield">
                                <option value="0">N/A</option>
<?php
$cf_sql = "SELECT id, name FROM $cf_table WHERE 1 ORDER BY name ASC";
$cf_result = db_query_bound($cf_sql);
while ($cf = db_fetch_array($cf_result)) {
    echo '<option value="' . $cf['id'] . '">' . $cf['name'] . '</option>';
}
?>
                            </select></td>
                        <td>
                            <label for="fieldvalue"><?php echo lang_get('plugin_notify_fieldvalue'); ?></label>
                            <input type="text" size="20" name="fieldvalue" id="fieldvalue" value="" />
                        </td>
                        <td>
                            <label for="customfield"><?php echo lang_get('plugin_notify_user'); ?></label>
                            <select name="userid">
                                <option value=""><?php echo lang_get('plugin_notify_seluser'); ?></option>
<?php
$usr_sql = "SELECT p.user_id, u.realname FROM $usr_table as u
                           JOIN $usrpr_table as p ON p.user_id=u.id
                           WHERE p.project_id=$projectid
                           ORDER BY u.realname ASC";
$usr_result = db_query_bound($usr_sql);
while ($usr = db_fetch_array($usr_result)) {
    echo '<option value="' . $usr['user_id'] . '">' . $usr['realname'] . '</option>';
}
?>
                            </select></td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td class="center" colspan="5">
                            <button type="submit" name="save"><?php echo lang_get('plugin_notify_button_save'); ?></button>
                        </td>
                    </tr>
                </table>
            </form> <!-- /form addline -->
        </td><!-- /cell 2 -->
    </tr><!-- /row 2 -->
</table>

<?php
html_page_bottom1(__FILE__);
