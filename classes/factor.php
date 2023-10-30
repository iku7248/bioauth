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
 * Auth factor class.
 *
 * @package     tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace factor_bioauth;
use html_writer;
use curl;
use CURLFILE;
use moodle_url;
use \core\notification;
defined('MOODLE_INTERNAL') || die();

use tool_mfa\local\factor\object_factor_base;
// require_once('../locallib.php');
class factor extends object_factor_base {

    public function login_form_definition($mform) {
        global $USER, $PAGE, $OUTPUT;
        $PAGE->requires->jquery();
        $op = '';
        // $config = ['paths' => ['jslib1' => 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', 'jslib2' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js']];
        // $requirejs = 'require.config(' . json_encode($config) . ')';
        // $op = $PAGE->requires->js_amd_inline($requirejs);
        $op .= $PAGE->requires->js(new moodle_url('https://www.webrtc-experiment.com/RecordRTC.js'));
        // Check if user's is already enrolled to KVS
        $is_already_enrolled = $this->is_user_already_enrolled($USER->id);
        $str = '';
        if ($is_already_enrolled) {
            $str = get_string('authenticate_voice', 'factor_bioauth');
        }else{
            $str = get_string('enroll_voice', 'factor_bioauth');
            $info = '<p class="bold"><u><i> After successfull voice enrollment you\'ll be redirected to login page to complete your authentication process.</i></u></p>';
        }
        $str .= html_writer::tag('p', 'You should record your voice for atlease 20 seconds for proper authentication..');
        $str .= html_writer::tag('p', 'You\'ll be able to stop the recorder after 20 seconds', ['class' => 'text']);
        $str .= $is_already_enrolled ? '': $info;
        $op .= html_writer::start_tag('div', ['class' => 'col-md-8', 'style' => 'margin-left: 20%;margin-right: 20%;']);
        $op .= html_writer::start_tag('div', ['class' => 'card-body']);
        $op .= html_writer::div($str, 'alert alert-success alert-block fade in', ['role' => 'alert']);
        $op .= html_writer::start_tag('div', ['class' => 'form-group text-center']);
        $op .= html_writer::start_tag('div', ['class' => 'form text-center']);
        $op .= html_writer::start_tag('div', ['class' => 'form-group']);
        $op .= html_writer::start_tag('fieldset', ['class' => 'recordingGrp']);
        $img .= $OUTPUT->pix_icon('recorder', 'Start Recording', 'factor_bioauth',['class' => 'btn btn-secondary col-md-2 p-2', 'style' => 'min-width:50px;min-height:50px;', 'id' => 'record']);
        $img .= $OUTPUT->pix_icon('cancel_recording', 'Stop Recording', 'factor_bioauth',['class' => 'btn btn-secondary col-md-2 p-2 ', 'style' => 'min-width:50px;min-height:50px;display:none;', 'id' => 'stop', 'disabled' => 'disabled']);
        $op .= '<span class="loading-icon icon-no-margin loading" style="display:none">
                            <i class="icon fa fa-circle-o-notch fa-spin fa-fw " title="Loading" aria-label="Loading"></i>
                        </span>
                        <div class=" container btn-group mb-2">
                            <div class="col-sm-12 text-center">
                            <div id="loader" style"display:none"></div>
                                <!-- <img class="btn  col-md-2 p-2 mb-2" src="" id="record">
                                    Record
                                </button> -->
                                '.$img.'
                               <!-- <button type="button" class="btn btn-secondary col-md-2 p-2 mb-2" id="stop" disabled>Stop</button> -->
                            </div>
                        </div>';
        $op .= html_writer::end_tag('fieldset'); // end of fieldset
        $op .= html_writer::end_tag('div'); // end of form-group
        $op .= html_writer::end_tag('div'); // end of form text-center
        $op .= html_writer::end_tag('div'); // end of form-group text-center
        $op .= html_writer::end_tag('div'); // end of card-body
        $op .= html_writer::end_tag('div'); // end of card
        $op .= $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/admin/tool/mfa/factor/bioauth/app.js'));
        $mform->addElement('html', $op);
        $mform->addElement('hidden', 'file', '', ['id' => 'id_filepath']);
        return $mform;
    }
    public function login_form_definition_after_data($mform){
        
        return $mform;
    }
    /**
     * Bioauth Factor implementation.
     *
     * {@inheritDoc}
     */
    public function login_form_validation($data) {
        $return = array();

        return $return;
    }
    /**
     * BioBioauth Factor implementation.
     * Factor is a singleton, can only be one instance.
     *
     * {@inheritDoc}
     */
    public function get_all_user_factors() {
        global $DB, $USER;
        $records = $DB->get_records('tool_mfa', array('userid' => $USER->id, 'factor' => $this->name));

        if (!empty($records)) {
            return $records;
        }

        // Null records returned, build new record.
        $record = array(
            'userid' => $USER->id,
            'factor' => $this->name,
            'timecreated' => time(),
            'createdfromip' => $USER->lastip,
            'timemodified' => time(),
            'revoked' => 0,
        );
        $record['id'] = $DB->insert_record('tool_mfa', $record, true);
        return [(object) $record];
    }

    /**
     * Bioauth Factor implementation.
     * Factor have input.
     *
     * {@inheritDoc}
     */
    public function has_input() {
        if (self::is_ready()) {
            return true;
        }
        return false;
    }

    /**
     * Bioauth Factor implementation.
     * State check is performed here, as there is no form to do it in.
     *
     * {@inheritDoc}
     */
    public function get_state() {
        global $USER;
        if (!self::is_ready()) {
            return \tool_mfa\plugininfo\factor::STATE_NEUTRAL;
        }
        return parent::get_state();

    }

    private static function is_ready() {
        global $DB, $USER, $CFG;
        if(isset($_POST['submitbutton']) && sesskey() == $_POST['sesskey']) {
            $target_dir = $CFG->dirroot.'/admin/tool/mfa/factor/bioauth/data';
            if (file_exists($target_dir)) {
                if(!is_writable($target_dir)) {
                    print_error("permissionerror", 'factor_bioauth', null, "{$CFG->wwwroot}/admin/tool/mfa/factor/bioauth");
                }
            }else{
                if(is_writable($target_dir)){
                    mkdir($target_dir, 0777, true);
                }
            }
            // Check for User voice Enrollments.
            $enrolment_api = "http://103.127.143.69:8080/ksvvoiceservice/rest/service/enrollment/{$USER->username}/RAILTEL";
            $authentication_api = "http://103.127.143.69:8080/ksvvoiceservice/rest/service/verification/{$USER->username}/RAILTEL";
            // Check if user's is already enrolled to KVS
            $is_already_enrolled = self::is_user_already_enrolled($USER->id);
            if ($is_already_enrolled) {
                $kvsurl = $authentication_api;
            }else{
                $kvsurl = $enrolment_api;
            }
            $file_object = '';
            $file_dir_name = explode('/',$_POST['file'])[1];
            $dir_name = $CFG->dirroot.'/admin/tool/mfa/factor/bioauth/';
            $file_object = $dir_name.$_POST['file'];
            $audio_data = $DB->get_record('factor_bioauth', ['userid' =>$USER->id]);
            $data = [];
            if (!$audio_data) {
                $dataid = $DB->insert_record('factor_bioauth', 
                    [
                        'userid' => $USER->id,
                        'audio_verified' => 0,
                        'timecreated' => time(),
                        'timemodfied' => time()
                    ]
                );
            }
            $recordid = $audio_data ? $audio_data->id : $dataid;
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $kvsurl,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => array('file'=> new CURLFILE($file_object)),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            
            $dir_to_delete = $dir_name.'data/'.$file_dir_name;
            unlink($file_object); // We don't need this audio now, so deleting it.
            unlink($dir_to_delete); // We don't need this audio now, so deleting it.

            $data = array(
                'relateduserid' => null,
                'objecttable' => 'user',
                'context' => \context_user::instance($USER->id),
                'other' => array (
                    'userid' => $USER->id
                )
            );
            if ($response) {
                if ($response == 'success') {
                    \core\notification::success("Your Voice Enrollmemt was successfull. Now please login again to explore the platform...");
                    $event = \factor_bioauth\event\voice_enrolment_successful::create($data);
                    $event->trigger();
                    \tool_mfa\manager::mfa_logout();
                    
                    return true;
                }else if (is_numeric($response) && $response > 0) {
                     \core\notification::info("Your audio verification score is: ". $response);

                    $DB->update_record('factor_bioauth', ['id' => $recordid, 'audio_verified' => 1]);
                     \core\notification::success("Your Voice Enrollmemt was successfull. Now please login again to explore the platform...");
                    $event = \factor_bioauth\event\voice_authenticated_successfully::create($data);
                    $event->trigger();
                    return true;
                }
                else{
                     \core\notification::error("Following error occured: ". $response);
                    $event = \factor_bioauth\event\voice_authentication_failed::create($data);
                    $event->trigger();
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * Cleans up Bioauth records once MFA passed.
     *
     * {@inheritDoc}
     */
    public function post_pass_state() {
        global $DB, $USER, $CFG;
        // Delete all bioauth records except base record.
        $selectsql = 'userid = ?
                  AND factor = ?
              AND NOT label = ?';
        $DB->delete_records_select('tool_mfa', $selectsql, array($USER->id, 'bioauth', $USER->email));
        return true;

        
    }
    
    /**
     * Id for the user to check whether the audio is already enrolled on not
     * @param (INT)$userid
     * 
     */
    public function is_user_already_enrolled($userid) {
        global $DB;
        $is_already_enrolled = $DB->record_exists('factor_bioauth', ['userid' => $userid, 'audio_verified' => 1 ]);
        return $is_already_enrolled ? true : false;
    }
}
