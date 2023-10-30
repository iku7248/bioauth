$(document).ready(function () {
	$('#id_submitbutton').attr('disabled',true);
	var numSentences = 1;
	function getRandomInt(max) {
		min = 1;
		max++;
		return Math.floor(Math.random() * (max - min)) + min; //The maximum is exclusive and the minimum is inclusive
	}
	function randomString() {
		return Math.random().toString(36).substring(5);
	}
	if (navigator.mediaDevices) {
		var constraints = { audio: true };
		var chunks = [];
		var blob = null;
		var clipName = "";
		navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
				
				// const onSuccess = (stream) => {
				    const options = {
				        type: 'audio',
				        numberOfAudioChannels: 1,
					    // mimeType: "audio/wav",
				        recorderType: StereoAudioRecorder,
				        desiredSampRate: 16000
				    }
				// }
				var mediaRecorder = new MediaRecorder(stream, options);
				
				console.log(mediaRecorder);
				$("#record").click(function () {
					chunks = [];
					mediaRecorder.start();
					$("#record").css('cursor', 'not-allowed');
					$("#stop").css("cursor", 'not-allowed');
					$("#record").attr("disabled", true);
					$("#stop").attr("disabled", true);
					$("#record").hide();
					const recording = '<div class="spinner-grow spinner-grow-sm " role="status"><span class="sr-only">Loading...</span></div>Listening...';
					$('#loader').show();
					$('#loader').html(recording);
					setTimeout(function() {			
						$("#stop").show();
						$("#stop").attr("disabled", false);
						$("#stop").css("cursor", 'pointer');
					}, 20000);
				});

				$("#stop").click(function () {
					var count = $('#counter').html();
					$('#loader').hide();
					$('#stop').hide();
					$("#record").attr("disabled", false);
					$("#record").css('cursor','pointer');
					$("#record").show();
					mediaRecorder.stop();
					recordingstopped();
				});
				function recordingstopped(){
					
					setTimeout(function() {
						var xhr = new XMLHttpRequest();
						xhr.onload = function (e) {
							if (this.readyState === 4) {
								console.log("Server returned: ", e.target.responseText);
							}
						};
						var fd = new FormData();
						fd.append("name", randomString() + randomString());
						fd.append("audio", blob, clipName);
						xhr.open("POST", "factor/bioauth/upload.php", false);
						xhr.send(fd);
						var response = JSON.parse(xhr.responseText);
						console.log(response);
						if (response.filepath != " ") {
							$('#id_filepath').val(response.filepath);
							$('#id_submitbutton').attr('disabled',false);
							$('#loader').hide();
							$("#record").attr("disabled", false);
							$("#record").show();
							$("#stop").attr("disabled", true);
							$("#stop").hide();

						}
						else {
							alert('error during upload');
						}
					}, 2000);
				}
				mediaRecorder.onstop = function (e) {
					clipName = $("#dataset").val() + "_" + $("#sentenceNumber").val();

					blob = new Blob(chunks, { 'type': 'audio; codecs=opus', 'mimeType':'audio/wav' });
					chunks = [];
					var audioURL = URL.createObjectURL(blob);
					$("#preview").attr("src", audioURL);

					if ($("#autoplay").is(":checked"))
						$("#preview").trigger("play");
				}

				mediaRecorder.ondataavailable = function (e) {
					chunks.push(e.data);
				}
			})
			.catch(function (err) {
				alert('The following error occurred: ' + err);
			})
	}

});