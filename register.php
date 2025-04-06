<?php
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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get common form data
    $profile_type = sanitize_input($_POST["profile_type"]);
    $name = sanitize_input($_POST["name"]);
    $phone = sanitize_input($_POST["phone"]);
    $email = isset($_POST["email"]) ? sanitize_input($_POST["email"]) : "";
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash password
    $village = sanitize_input($_POST["village"]);
    $district = sanitize_input($_POST["district"]);
    $state = sanitize_input($_POST["state"]);
    $pincode = sanitize_input($_POST["pincode"]);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert into users table
        $sql = "INSERT INTO users (name, phone, email, password, village, district, state, pincode, profile_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $name, $phone, $email, $password, $village, $district, $state, $pincode, $profile_type);
        $stmt->execute();
        
        $user_id = $conn->insert_id;
        
        // Process farmer-specific data
        if ($profile_type == "farmer") {
            $land_size = sanitize_input($_POST["land_size"]);
            $land_type = sanitize_input($_POST["land_type"]);
            $labor_requirement = isset($_POST["labor_requirement"]) ? sanitize_input($_POST["labor_requirement"]) : "";
            $farm_description = isset($_POST["farm_description"]) ? sanitize_input($_POST["farm_description"]) : "";
            
            // Insert into farmers table
            $sql = "INSERT INTO farmers (user_id, land_size, land_type, labor_requirement, farm_description) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idsss", $user_id, $land_size, $land_type, $labor_requirement, $farm_description);
            $stmt->execute();
            
            // Process crops
            if (isset($_POST["crops"]) && is_array($_POST["crops"])) {
                foreach ($_POST["crops"] as $crop) {
                    $crop = sanitize_input($crop);
                    
                    $sql = "INSERT INTO farmer_crops (farmer_id, crop_name) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $user_id, $crop);
                    $stmt->execute();
                }
            }
        }
        
        // Process laborer-specific data
        if ($profile_type == "laborer") {
            $experience = isset($_POST["experience"]) ? sanitize_input($_POST["experience"]) : "";
            $availability = isset($_POST["availability"]) ? sanitize_input($_POST["availability"]) : "";
            $wage_expectation = isset($_POST["wage_expectation"]) ? sanitize_input($_POST["wage_expectation"]) : 0;
            $additional_info = isset($_POST["additional_info"]) ? sanitize_input($_POST["additional_info"]) : "";
            
            // Insert into laborers table
            $sql = "INSERT INTO laborers (user_id, experience, availability, wage_expectation, additional_info) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issds", $user_id, $experience, $availability, $wage_expectation, $additional_info);
            $stmt->execute();
            
            // Process skills
            if (isset($_POST["skills"]) && is_array($_POST["skills"])) {
                foreach ($_POST["skills"] as $skill) {
                    $skill = sanitize_input($skill);
                    
                    $sql = "INSERT INTO laborer_skills (laborer_id, skill_name) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $user_id, $skill);
                    $stmt->execute();
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to success page
        header("Location: ../profile-success.html");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

$conn->close();
?>