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

function redirect_for_audio_auth() {
    global $CFG;

    redirect("{$CFG->wwwroot}/admin/tool/mfa/factor/bioauth/b_auth.php");
}
/**
 * Id for the user to check whether the audio is already enrolled on not
 * @param (INT)$userid
 * 
 */
function is_user_already_enrolled($userid) {
    global $DB;
    $is_already_enrolled = $DB->record_exists('factor_bioauth', ['userid' => $userid]);
    return $is_already_enrolled ? true : false;
}
/**
 * Get file properties
 * @param $fullpath is the full path of the file
 * 
 */
function makeCurlFile($fullpath){
    $mime = mime_content_type($fullpath);
    $info = pathinfo($fullpath);
    // $output = ['type' => 'audio/wav', 'fileinfo' => $info];
    $info['type'] = 'audio/wav';
    $info['content'] = file_get_contents($fullpath);
    return $info;
}

/**
 * Generate Randome Strings
 * 
 */
function generateRandomString($length = 25) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function get_storedfile_object(&$curlobj, $fileid, $filetype){
    $fs = get_file_storage();
    $fileinfo = $fs->get_file_by_id($fileid);
    $fileinfo->add_to_curl_request($curlobj, $filetype);
    $source = @unserialize($fileinfo->get_source());
    $filename = '';
    if (is_object($source)) {
        $filename = $source->source;
    } else {
        // If source is not a serialised object, it is a string containing only the filename.
        $filename = $fileinfo->get_source();
    }
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    if (empty($extension)) {
        $extension = mimeinfo_from_type('extension', $fileinfo->get_mimetype());
        $filename .= '.' . $extension;
    }
    $mimetype = mimeinfo('type', $filename);
    list($mediatype, $subtype) = explode('/', $mimetype);
    if ($mediatype != $filetype) {
        throw new \moodle_exception('wrongmimetypedetected', 'block_stream');
    }
    $fileinfo->postname = $filename;
    $fileinfo->mime = $mimetype;
    return $fileinfo;
}