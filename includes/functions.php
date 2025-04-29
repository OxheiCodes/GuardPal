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
    // Always append security-related keywords to the search
    $securityKeywords = ['security', 'guard', 'officer', 'protection', 'surveillance', 'SIA'];
    
    // If the user hasn't included any security terms, add them
    $hasSecurityTerm = false;
    foreach ($securityKeywords as $keyword) {
        if (stripos($query, $keyword) !== false) {
            $hasSecurityTerm = true;
            break;
        }
    }
    
    if (!$hasSecurityTerm) {
        // Add "security" to the query if no security terms are found
        $query .= " security";
    }
    
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://jsearch.p.rapidapi.com/search?query=" . urlencode($query) . "&page=" . $page . "&num_pages=1&country=us&date_posted=all",
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
        $jobsData = json_decode($response, true);
        
        // Further filter results to ensure they're security-related
        if (isset($jobsData['data']) && is_array($jobsData['data'])) {
            $filteredJobs = array_filter($jobsData['data'], function($job) use ($securityKeywords) {
                $jobTitle = strtolower($job['job_title'] ?? '');
                $jobDescription = strtolower($job['job_description'] ?? '');
                
                // Check if job title or description contains security-related keywords
                foreach ($securityKeywords as $keyword) {
                    $keyword = strtolower($keyword);
                    if (strpos($jobTitle, $keyword) !== false || strpos($jobDescription, $keyword) !== false) {
                        return true;
                    }
                }
                return false;
            });
            
            $jobsData['data'] = array_values($filteredJobs); // Reset array keys
        }
        
        return $jobsData;
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