/**
 * JavaScript package
 * @module     factor_bioauth
 * @author     eabyas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
define(['jquery', 'core/config', 'core/str', 'core/notification'], function($, config, str, notification) {

    return {
        enroll_audio:function(kvsurl) {
            $('#submit').click(function(e){
                e.preventDefault();
                $('.loader').show();
                $('#submit').attr('disabled');
                $.ajax({
                    url: config.wwwroot+"/admin/tool/mfa/factor/bioauth/process.php",
                    type: 'POST',
                    data : {kvsurl: kvsurl, tosave: true},
                    success: function(response) {
                        console.log(response);
                        $('.loader').hide();
                        $('#submit').removeAttr('disabled', 'false');
                        if (response == "Success" || response.responseText == "Success") {
                            window.location.href = config.wwwroot+'/my';
                        }else{
                            notification.alert("", response);
                        }
                    },
                    error: function(e) {
                        $('.loader').hide();
                        $('#submit').val('Click to Verify again');
                        $('#submit').removeAttr('disabled');

                        
                        if (e.status==0) {
                            notification.alert('Error', 'Something went wrong...! Please try again after sometime.', 'OK');
                            
                        }else if(e.responseText=='Already Enrolled'){
                            
                            notification.alert('Already Enrolled', 'The user already enrolled in voice biometric', 'OK');
                        }else if(e.responseText=='L'){
                            
                            notification.alert('Insufficient speech length..', 'The required speech length is not sufficient for enrolment', 'OK');
                        }else if(e.status==400){
                            
                            notification.alert('Bad Request', 'Bad Request - An invalid value was specified for one of the query parameters in the request URL', 'OK');
                        }else if(e.status==401){
                            
                            notification.alert('Unauthorized', 'Unauthorized – The request requires a user authentication', 'OK');
                        }else if(e.status==403){
                            
                            notification.alert('Forbidden', 'Forbidden – The server understood the request, but is refusing it or the access is not allowed', 'OK');
                        }else if(e.status==404){
                            
                            notification.alert('Not found', 'Not found – There is no resource behind the URI..', 'OK');
                        }else if(e.status==405){
                            
                            notification.alert('Method Not Allowed', 'Method Not Allowed', 'OK');
                        }else  if(e.status==413){
                            
                            notification.alert('Limit Exceeds', 'Size of uploaded file exceeds size limit', 'OK');
                        }else {
                            
                            notification.alert('', e);
                        }
                    }
                });
            });
        },
        authenticate_audio:function(kvsurl) {
            $('#submit').click(function(e){
                e.preventDefault();
                $('.loader').show();
                $('#submit').attr('disabled');
                // console.log(kvsurl);
                $.ajax({
                    url: config.wwwroot+"/admin/tool/mfa/factor/bioauth/process.php",
                    type: 'POST',
                    data : {kvsurl: kvsurl, tosave: false},
                    success: function(response) {
                        $('.loader').hide();
                        $('#submit').removeAttr('disabled');
                        if (response >= 80) {
                            window.location.href=config.wwwroot+'/my';
                        }else{
                            notification.alert('Error', 'The voice input does not matched with your registered voice..');
                            $('#submit').val('Record Again..');
                        }
                    },
                    error: function(e) {
                        console.log(e);
                        $('.loader').hide();
                        $('#submit').removeAttr('disabled');
                        if (e.status==0) {
                            notification.alert('Error', 'Something went wrong...! Please try again after sometime.', 'OK');
                        }else if(e.responseText=='L'){
                            notification.alert('Insufficient speech length', 'The required speech length is not sufficient for enrolment', 'OK');
                        }else if(e.status==400){
                            notification.alert('Bad Request', 'Bad Request - An invalid value was specified for one of the query parameters in the request URL', 'OK');
                        }else if(e.status==401){
                            notification.alert('Unauthorized','Unauthorized – The request requires a user authentication', 'OK');
                        }else{
                            notification.alert("", e);
                        }
                    }
                });
            });
        }
    };
});