<?php

/**
 * Helper function to read variables from the root .env file
 */
if (!function_exists('getEnvValue')) {
    function getEnvValue($key) {
        // Path logic to find .env relative to the 'includes' folder
        $path = __DIR__ . '/../.env';
        
        if (!file_exists($path)) {
            return null;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) continue;

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);
                
                if ($name === $key) {
                    return trim($value, '"\' ');
                }
            }
        }
        return null;
    }
}

/**
 * GoldByte CAMS Central SMS Hub
 * Handles all outgoing notifications via Arkesel API
 * * @param string $recipient The phone number
 * @param string $message The text content
 * @param string $senderId Custom Sender ID (Default: GB-CLINIC)
 */
function sendGoldByteSMS($recipient, $message, $senderId = "GB-CLINIC") {
    // 1. FETCH API KEY FROM .ENV FILE
    $apiKey = getEnvValue('ARKESEL_API_KEY');
    
    // Arkesel Sender IDs must be exactly 11 characters or less
    $senderId = substr(trim($senderId), 0, 11);

    if (!$apiKey) {
        error_log("SMS Error: ARKESEL_API_KEY is missing in .env");
        return false;
    }

    // 2. FORMAT THE NUMBER (Ghana Standard 233)
    // Strips spaces, dashes, and plus signs
    $recipient = str_replace([' ', '-', '+'], '', $recipient);
    
    // Converts local 0 format to international 233
    if (substr($recipient, 0, 1) == '0') {
        $recipient = '233' . substr($recipient, 1);
    }

    // 3. PREPARE THE API URL
    $url = "https://sms.arkesel.com/sms/api?action=send-sms"
         . "&api_key=" . urlencode($apiKey)
         . "&to=" . urlencode($recipient)
         . "&from=" . urlencode($senderId)
         . "&sms=" . urlencode($message);

    // 4. TRIGGER THE REQUEST WITH 5-SECOND TIMEOUT
    $options = [
        "http" => [
            "method" => "GET",
            "timeout" => 5, // Prevents system hanging during bulk blasts
            "ignore_errors" => true
        ]
    ];
    $context = stream_context_create($options);
    
    // Send the request safely
    $response = @file_get_contents($url, false, $context);
    
    return $response; 
}
?>