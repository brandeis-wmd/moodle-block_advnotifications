<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * All the configurable strings used throughout the plugin.
 *
 * @package    block_advnotifications
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Zander Potgieter <zander.potgieter@learningworks.co.nz>
 */

$string['pluginname'] = 'Advanced Notifications';

// Capabilities.
$string['advnotifications:addinstance'] = 'Add a new Advanced Notifications block';
$string['advnotifications:myaddinstance'] = 'Add a new Advanced Notifications block to the My Moodle page';
$string['advnotifications:managenotifications'] = 'Manage notifications and the relative settings';

// Block Configuration.
$string['advnotifications'] = 'Advanced Notifications';
$string['advnotifications_class'] = 'Block Class:';

// Notifications Table Column Names & Table Related Lang Strings.
$string['advnotifications_field_id'] = 'ID';
$string['advnotifications_field_title'] = 'Title';
$string['advnotifications_field_type'] = 'Type';
$string['advnotifications_field_enabled'] = 'Enabled';
$string['advnotifications_field_global'] = 'Global';
$string['advnotifications_field_aicon'] = 'Icon';
$string['advnotifications_field_dismissible'] = 'Dismissible';
$string['advnotifications_field_times'] = 'View Times';
$string['advnotifications_field_date_from'] = 'From';
$string['advnotifications_field_date_to'] = 'To';
$string['advnotifications_field_actions'] = 'Actions';
$string['advnotifications_edit_label'] = 'Edit';
$string['advnotifications_delete_label'] = 'Delete';
$string['advnotifications_restore_label'] = 'Restore';
$string['advnotifications_table_empty'] = 'No notifications to show!';
$string['advnotifications_cell_yes'] = 'Yes';
$string['advnotifications_cell_no'] = 'No';

$string['advnotifications_restore_table_warning'] = '<strong>Warning!</strong> Deleting notifications from this table will permanently delete it from the database. We recommend using the auto-delete features of the plugin...';

// Manage Advanced Notification Lang Strings.
$string['advnotifications_table_title'] = 'Manage Notifications';
$string['advnotifications_table_heading'] = 'Advanced Notifications';

$string['advnotifications_restore_table_title'] = 'Restore Notifications';
$string['advnotifications_restore_table_heading'] = 'Advanced Notifications Restore';

// New Notification Lang Strings.
$string['advnotifications_enabled'] = 'Enable/Disable?';
$string['advnotifications_global'] = 'Global Notification?';
$string['add_notification_global_notice'] = 'This notification will be displayed globally/site-wide!';
$string['advnotifications_title'] = 'Title';
$string['advnotifications_message'] = 'Message';
$string['advnotifications_type'] = 'Type';
$string['advnotifications_times'] = '# of times';
$string['advnotifications_times_label'] = 'Number of times to display the notification to the user (0 = forever)';
$string['advnotifications_aicon'] = 'Display Icon?';
$string['advnotifications_dismissible'] = 'Dismissible?';
$string['advnotifications_date_from'] = 'From:';
$string['advnotifications_date_to'] = 'To:';
$string['advnotifications_date_info'] = 'Same date = forever';
$string['advnotifications_save'] = 'Save';
$string['advnotifications_cancel'] = 'Cancel';

// Renderer.
$string['advnotifications_add_heading'] = 'New Notification';
$string['advnotifications_add_option_info'] = 'Information';
$string['advnotifications_add_option_success'] = 'Success';
$string['advnotifications_add_option_warning'] = 'Warning';
$string['advnotifications_add_option_danger'] = 'Danger';
$string['advnotifications_add_option_announcement'] = 'Announcement';
$string['advnotifications_add_saving'] = 'Saving...';
$string['advnotifications_add_done'] = 'Done!';

// Admin Settings.
$string['setting/navigation'] = 'Navigation:';
$string['setting/navigation_desc'] = '<div id="advnotifications_manage">{$a->manage}&nbsp;&nbsp;&nbsp;{$a->restore}</div>';

$string['setting/settings'] = 'Settings:';

$string['setting/enable'] = 'Enable:';
$string['setting/enable_desc'] = 'Toggles whether all notifications are enabled/disabled';
$string['setting/enable_default'] = '';

$string['setting/auto_perma_delete'] = 'Auto Permanent Delete:';
$string['setting/auto_perma_delete_desc'] = 'Toggles whether notifications that have been deleted for more than 30 days are automatically permanently deleted from the database.<br>(Helps with housekeeping/management)';
$string['setting/auto_perma_delete_default'] = '';

$string['setting/auto_delete'] = 'Auto Delete:';
$string['setting/auto_delete_desc'] = 'Toggles whether a notification that go past the set end-date is automatically deleted - but can be restored again.<br>(Helps with housekeeping/management)';
$string['setting/auto_delete_default'] = '';

$string['setting/auto_delete_user_data'] = 'Auto Delete User Data:';
$string['setting/auto_delete_user_data_desc'] = 'Toggles whether user data (such as whether the user has seen/dismissed notifications that don\'t exist anymore, etc) related to advanced notifications is automatically deleted.<br>(Helps with housekeeping/management)';
$string['setting/auto_delete_user_data_default'] = '';

// Navigation Links.
$string['advnotifications_nav_heading'] = 'Notifications:';
$string['advnotifications_nav_manage'] = 'Manage';
$string['advnotifications_nav_restore'] = 'Restore';
$string['advnotifications_nav_settings'] = 'Settings';

// Error Messages.
$string['advnotifications_err_forbidden'] = 'Forbidden, please login again...';
$string['advnotifications_err_nojsedit'] = 'Editing only supported with JavaScript enabled. Re-create the desired notification or enable JavaScript and try again.';

// Cron Messages.
$string['advnotifications_cron_heading'] = 'Cleaning Advanced Notifications';
$string['advnotifications_cron_auto_perma_delete'] = 'Permanently delete notifications that\'s had the deleted flag for more than 30 days...';
$string['advnotifications_cron_auto_delete'] = 'Add deleted flag to notifications that\'s passed their end-date...';
$string['advnotifications_cron_auto_delete_udata'] = 'Remove user records that relates to notifications that don\'t exist anymore...';