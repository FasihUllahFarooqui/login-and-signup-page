<?php
include("conn.php");

if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password']) && isset($_FILES['profile_picture'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_picture = $_FILES['profile_picture'];

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    // Check if the email already exists
    $sql_check_email = "SELECT * FROM login WHERE email = '$email'";
    $result = mysqli_query($conn, $sql_check_email);
    if (mysqli_num_rows($result) > 0) {
        echo "Email already exists. Please use a different email.";
    } else {
        // Handle file upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        if (getimagesize($profile_picture["tmp_name"]) === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (5MB max for example)
        if ($profile_picture["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats (JPEG, PNG, GIF)
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            // Move the uploaded file to the server
            if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                // Insert into database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
                $sql_insert = "INSERT INTO login (name, email, password, profile_picture) VALUES (?, ?, ?, ?)";
                
                // Prepare statement to prevent SQL injection
                if ($stmt = mysqli_prepare($conn, $sql_insert)) {
                    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $target_file);
                    if (mysqli_stmt_execute($stmt)) {
                        echo "New record created successfully";
                        header('Location: sign-in.php');
                        exit();
                    } else {
                        echo "Error: " . mysqli_error($conn);
                    }
                } else {
                    echo "Error in preparing the statement.";
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
}
?>
    