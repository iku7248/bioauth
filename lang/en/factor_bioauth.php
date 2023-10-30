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
 * Language strings.
 *
 * @package     factor_bioauth
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Voice Authentication';
$string['info'] = 'Check the type of authentication used to login as an MFA factor.';

$string['settings:goodauth'] = 'Factor authentication types';
$string['settings:goodauth_help'] = 'Select all authentication types to use as a factor for MFA. Any types not selected will not be treated as a FAIL in MFA.';
$string['summarycondition'] = 'has an authentication type of {$a}';
$string['privacy:metadata'] = 'The Auth Factor plugin does not store any personal data';
$string['enrolmentapi'] = 'Voice Biometrics Enrolment API';
$string['enrolmentapi_desc'] = 'Voice Biometrics Enrolment API';
$string['authenticationapi'] = 'Voice Biometrics Verification Process';
$string['authenticationapi_desc'] = 'API URL for Voice Biometrics Verification Process';
$string['manage_apis'] = 'Manage Authentication APIs';
$string['enroll_voice'] = "<p class='bold'>Please enroll your voice for more secure authentication...</p>";
$string['authenticate_voice'] = "<p class='bold'>Please record your voice for more secure authentication...</p>";
$string['factor_bioauthnoemail'] = 'Tried to send you an email but failed!';
$string['factor_bioauthrecaptcha'] = 'Adds a visual/audio confirmation form element to the sign-up page for email self-registering users. This protects your site against spammers and contributes to a worthwhile cause. See https://www.google.com/recaptcha for more details.';
$string['factor_bioauthrecaptcha_key'] = 'Enable reCAPTCHA element';
$string['factor_bioauthsettings'] = 'Settings';
$string['privacy:metadata'] = 'The Email-based self-registration authentication plugin does not store any personal data.';
$string['loginsubmit'] = 'Submit';
$string['loginskip'] = 'Cancel';
$string['already_enrolled'] = 'The user already enrolled in voice biometric';
$string['permissionerror'] = 'The target directory <i> {$a} </i> needs write permission to save the audio.';
$string['event:voice_enrolment_success'] = 'Voice Enrollment Passed';
$string['event:voice_authentication_success'] = 'Voice Authentication Passed';
$string['event:voice_authentication_failed'] = 'Voice Authentication Failed';