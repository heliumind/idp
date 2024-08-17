<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize the input
    $fname = escapeshellcmd($_POST['fname']);
    $lname = escapeshellcmd($_POST['lname']);
    $email = escapeshellcmd($_POST['email']);
    $password = escapeshellcmd($_POST['password']);
    $lrzuser = escapeshellcmd($_POST['lrzuser']);
    $uid = escapeshellcmd($_POST['uid']);

    // Validate input
    if (empty($fname) || empty($lname) || empty($password)) {
        echo "First Name, Last Name and Password cannot be empty!";
        exit;
    }

    // Validate names (basic check for allowed characters)
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

    // Check if the user already exists
    if (!empty($lrzuser)) {
        $output = shell_exec("id -u " . escapeshellarg($lrzuser));
        if (!empty($output)) {
            echo "User already exists";
            exit;
        }
    }   

    // Prepare command arguments to create the user
    if (empty($lrzuser) && empty($uid)) {
        $args = "-f " . escapeshellarg($fname) .
                " -l " . escapeshellarg($lname) . 
                " -p " . escapeshellarg($password);
    } else {
        $args = "-f " . escapeshellarg($fname) .
                " -l " . escapeshellarg($lname) . 
                " -p " . escapeshellarg($password) .
                " -u " . escapeshellarg($lrzuser) .
                " -U " . escapeshellarg($uid);
    }
  
    // Build the command
    $command = "sudo ./bootstrap_user.sh $args";

    // Execute the command
    $output = shell_exec($command);

    // Check if the user was created
    echo "$output";
    // if (empty($output)) {
    //     echo "User $username created successfully!";
    // } else {
    //     echo "Error creating user!";
    // }
}
?>
