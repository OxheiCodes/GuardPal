<?php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function searchJobs($query, $page = 1) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://jsearch.p.rapidapi.com/search?query=" . urlencode($query . " security") . "&page=" . $page . "&num_pages=1&country=us&date_posted=all",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: " . JSEARCH_API_HOST,
            "X-RapidAPI-Key: " . JSEARCH_API_KEY
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return false;
    } else {
        return json_decode($response, true);
    }
}

function getJobDetails($jobId) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://jsearch.p.rapidapi.com/job-details?job_id=" . urlencode($jobId),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: " . JSEARCH_API_HOST,
            "X-RapidAPI-Key: " . JSEARCH_API_KEY
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return false;
    } else {
        return json_decode($response, true);
    }
}
?>