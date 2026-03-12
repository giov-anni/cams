<?php

/**
 * Helper function to read variables from the root .env file
 */
if (!function_exists('getEnvValue')) {
    function getEnvValue($key) {
        $path = __DIR__ . '/../.env';
        
        if (!file_exists($path)) {
            return null;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
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
 * Sender ID: GB-CLINIC
 */
function sendGoldByteSMS($recipient, $message) {
    // 1. FETCH API KEY FROM .ENV FILE
    $apiKey = getEnvValue('ARKESEL_API_KEY');
    
    // UPDATED SENDER ID
    $senderId = "GB-CLINIC"; 

    if (!$apiKey) {
        error_log("SMS Error: ARKESEL_API_KEY is missing in .env");
        return false;
    }

    // 2. FORMAT THE NUMBER (Ghana Standard 233)
    // Removes any spaces or dashes first
    $recipient = str_replace([' ', '-'], '', $recipient);
    
    if (substr($recipient, 0, 1) == '0') {
        $recipient = '233' . substr($recipient, 1);
    } elseif (substr($recipient, 0, 1) == '+') {
        $recipient = substr($recipient, 1);
    }

    // 3. PREPARE THE API URL
    $url = "https://sms.arkesel.com/sms/api?action=send-sms"
         . "&api_key=" . urlencode($apiKey)
         . "&to=" . urlencode($recipient)
         . "&from=" . urlencode($senderId)
         . "&sms=" . urlencode($message);

    // 4. TRIGGER THE REQUEST
    // Note: ensure 'allow_url_fopen' is ON in your XAMPP/PHP settings
    $response = @file_get_contents($url);
    
    return $response; 
}
?>