
<?php
/**
 * Uploads record
 * TODO: fix privilages; php mkdir and chmod does not work properly
 */
require_once('locallib.php');
// global $USER, $CFG;
$ext = ".wav";
$target_dir = "data/";
// $target_dir = $target_dir . $_POST["name"] . "/";
$name = generateRandomString();
$target_dir = $target_dir . $name . "/";

if (!file_exists($target_dir)) {
    mkdir($target_dir, 777, true);
    exec("chmod 777 " . $target_dir);
}

$target_file = $target_dir . $name. "_" . rand();

if (file_exists($target_file  . $ext)) {
    $i = 1;
    while (file_exists($target_file . "_" . $i  . $ext))
        $i++;
    $target_file = $target_file . "_" . $i;
}

$target_file = $target_file . $ext;

if (move_uploaded_file($_FILES["audio"]["tmp_name"], $target_file)) {
    exec("chmod 777 " . $target_file);
    echo json_encode(['filepath' => $target_file]);
}
else
    echo "error";

?>
