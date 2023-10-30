<?php
// This file is NOT part of Moodle - http://moodle.org/
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
 *
 * @package   factor_bioauth
 * @author    eabyas
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../../config.php');
require_once('locallib.php');
$context = context_system::instance();
$PAGE->set_context($context);
// $PAGE->set_pagelayout('login');
$PAGE->set_pagelayout('secure');
$url = new moodle_url("{$CFG->wwwroot}/admin/tool/mfa/factor/bioauth/b_auth.php");
$PAGE->set_url($url);
$title = get_string('pluginname', 'factor_bioauth');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_cacheable(false);
// Guest can not edit.
if (isguestuser()) {
    print_error('guestnoeditprofile');
}

// The user profile we are editing.
if (!$user = $DB->get_record('user', array('id' => $USER->id))) {
    print_error('invaliduserid');
}

// Guest can not be edited.
if (isguestuser($user)) {
    print_error('guestnoeditprofile');
}


$enrolment_api = "http://103.127.143.69:8080/ksvvoiceservice/rest/service/enrollment/{$USER->id}/RAILTEL";
$authentication_api = "http://103.127.143.69:8080/ksvvoiceservice/rest/service/verification/{$USER->id}/RAILTEL";

// Check if user's is already enrolled to KVS
$is_already_enrolled = is_user_already_enrolled($USER->id);
if ($is_already_enrolled) {
    $str = get_string('authenticate_voice', 'factor_bioauth');
    $kvsurl = $authentication_api;
    $apicall = 'authenticate_audio';
}else{
    $str = get_string('enroll_voice', 'factor_bioauth');
    $kvsurl = $enrolment_api;
    $apicall = 'enroll_audio';
}

$op = html_writer::start_tag('div', ['class' => 'card']);
$op .= html_writer::start_tag('div', ['class' => 'card-body']);
$op .= html_writer::div($str, 'alert alert-success alert-block fade in', ['role' => 'alert']);
$op .= html_writer::start_tag('div', ['class' => 'form-group text-center']);
// $op .= html_writer::tag('label', '5', ['id' => 'countdown', 'class' => "col-2  p-2 ", 'style' => 'border-radius: 5px;']);
$op .= html_writer::tag('div', '<input type="submit" name="submit" id="submit" class="btn btn-primary pull-left"> ');
$op .= html_writer::tag('span', '<i class="icon fa fa-circle-o-notch fa-spin fa-fw " title="Loading" aria-label="Loading"></i>', ['class' => "loading-icon icon-no-margin loader", 'style' => "display: none;"]);
$op .= html_writer::end_tag('div'); // end of form-group text-center
$op .= html_writer::end_tag('div'); // end of card-body
$op .= html_writer::end_tag('div'); // end of card

echo $OUTPUT->header();
echo $op;
$PAGE->requires->js_call_amd('factor_bioauth/main', $apicall, [$kvsurl]);
echo $OUTPUT->footer();
