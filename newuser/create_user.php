<?php
$logfile = '/var/log/newuser/create_user_php.log';

function log_message($message, $logfile) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logfile, "$timestamp - $message\n", FILE_APPEND);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = escapeshellcmd($_POST['fname']);
    $lname = escapeshellcmd($_POST['lname']);
    $email = escapeshellcmd($_POST['email']);
    $password = escapeshellcmd($_POST['password']);
    $lrzuser = escapeshellcmd($_POST['lrzuser']);
    $uid = escapeshellcmd($_POST['uid']);

    if (empty($fname) || empty($lname) || empty($password)) {
        echo "First Name, Last Name and Password cannot be empty!";
        exit;
    }

    if (!preg_match('/^([a-zA-Z]{3,30}\s*)+$/', $fname)) {
        echo "Invalid First Name.";
        exit;
    }

    if (!preg_match('/^[a-zA-Z]{3,32}$/', $lname)) {
        echo "Invalid Last Name.";
        exit;
    }

    if (!empty($lrzuser) && (!preg_match('/^[a-z]{2}[0-9]{2}[a-z]{3}[0-9]$/', $lrzuser))) {
        echo "Invalid lrzuser. (e.g. ab12xyz)";
        exit;
    }

    if (!empty($lrzuser)) {
        $output = shell_exec("id -u " . escapeshellarg($lrzuser));
        if (!empty($output)) {
            echo "User already exists";
            exit;
        }
    }   

    if (empty($lrzuser) && empty($uid)) {
        $args = "-f " . escapeshellarg($fname) .
                " -l " . escapeshellarg($lname);
    } else {
        $args = "-f " . escapeshellarg($fname) .
                " -l " . escapeshellarg($lname) . 
                " -u " . escapeshellarg($lrzuser) .
                " -U " . escapeshellarg($uid);
    }
  
    $command = "sudo ./bootstrap_user.sh $args";
    log_message("Executing command: $command", $logfile);
    
    $command = $command . " -p " . escapeshellarg($password);
    $output = shell_exec($command . ' 2>&1'); 
    log_message("Command output: $output", $logfile);

    echo "$output";
}
?>
