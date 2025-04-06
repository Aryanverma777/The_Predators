<?php
// This file would handle the AI chat functionality in a real application
// For now, we'll simulate responses

// Start session to track conversation
session_start();

// Initialize conversation history if not exists
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process incoming message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $user_message = sanitize_input($_POST['message']);
    
    // Add user message to history
    $_SESSION['chat_history'][] = [
        'role' => 'user',
        'content' => $user_message
    ];
    
    // Generate AI response based on keywords
    $response = generate_ai_response($user_message);
    
    // Add AI response to history
    $_SESSION['chat_history'][] = [
        'role' => 'assistant',
        'content' => $response
    ];
    
    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => $response
    ]);
    exit();
}

// Function to generate AI response based on keywords
function generate_ai_response($message) {
    $message = strtolower($message);
    
    // Sample responses for different farming queries
    $responses = [
        'pest' => 'For pest control, I recommend using neem-based organic pesticides. They\'re effective against a wide range of pests while being environmentally friendly. For severe infestations, you might need to consider targeted chemical pesticides, but always follow the recommended dosage.',
        
        'water' => 'For water management, drip irrigation can reduce water usage by up to 60% compared to traditional methods. Consider installing a rainwater harvesting system as well. Mulching around plants can also help retain soil moisture.',
        
        'soil' => 'To improve soil health, consider crop rotation and adding organic matter like compost. A soil test can help determine specific nutrient deficiencies. Cover crops during off-seasons can prevent erosion and add nutrients.',
        
        'crop' => 'For optimal crop selection, consider your local climate, soil type, and market demand. Diversifying crops can reduce risk and improve soil health. Consult with your local agricultural extension office for region-specific recommendations.',
        
        'fertilizer' => 'When applying fertilizers, it\'s important to follow the recommended dosage. Over-fertilization can damage plants and contaminate water sources. Consider organic alternatives like compost, manure, and green manures.',
        
        'weather' => 'Weather patterns significantly impact farming. Consider installing a small weather station on your farm for local data. Many agricultural apps now provide localized weather forecasts specifically for farmers.',
        
        'market' => 'To get better prices for your produce, consider direct marketing through farmers\' markets or community-supported agriculture (CSA). Value-added products can also increase your profit margins.',
        
        'loan' => 'Several government schemes provide low-interest loans to farmers. The Kisan Credit Card scheme is particularly useful for seasonal credit needs. Visit your local bank or agricultural office for more information.',
        
        'subsidy' => 'Various subsidies are available for seeds, fertilizers, equipment, and irrigation systems. Check the Government Schemes page on our website for detailed information on eligibility and application procedures.'
    ];
    
    // Default response if no keywords match
    $default_response = 'I understand you\'re facing a farming challenge. Could you provide more details about the specific crop, growing conditions, and the issues you\'re experiencing? This will help me provide more targeted advice.';
    
    // Check for keywords in the message
    foreach ($responses as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response;
        }
    }
    
    // Return default response if no keywords match
    return $default_response;
}

// If it's a GET request, return the chat history
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'history' => $_SESSION['chat_history']
    ]);
    exit();
}
?>