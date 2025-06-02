<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/plain");

if (isset($_POST['api_url'])) {
    $apiUrl = trim($_POST['api_url']);
    if (filter_var($apiUrl, FILTER_VALIDATE_URL)) {
        // Try to fetch the XML
        $xmlContent = @file_get_contents($apiUrl);
        if ($xmlContent !== false) {
            // Try to parse XML to validate it
            $xmlData = @simplexml_load_string($xmlContent);
            if ($xmlData === false) {
                echo "Failed to parse XML.";
            } else {
                // Return the raw XML content
                echo $xmlContent;
            }
        } else {
            echo "Failed to fetch XML from the provided URL.";
        }
    } else {
        echo "Invalid URL.";
    }
} else {
    echo "No URL provided.";
}
?> 