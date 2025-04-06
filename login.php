<?php
// Start session
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farmer_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = sanitize_input($_POST["phone"]);
    $password = $_POST["password"];
    
    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT id, name, password, profile_type FROM users WHERE phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user["password"])) {
            // Password is correct, start a new session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["profile_type"] = $user["profile_type"];
            
            // Redirect to dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Password is incorrect
            $error_message = "Invalid phone number or password";
        }
    } else {
        // User not found
        $error_message = "Invalid phone number or password";
    }
    
    // If there was an error, redirect back to login page with error message
    if (isset($error_message)) {
        $_SESSION["login_error"] = $error_message;
        header("Location: ../profiles.html#login");
        exit();
    }
}

$conn->close();
?>