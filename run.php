<?php

ini_set('max_execution_time', 600);

class URL
{
    private $address;
    private $data;
    private $http_status;
    private $redirect_url;

    public function __construct($row)
    {
        $this->data = $row;
        $this->address = $row[0];
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setHttpStatus($status)
    {
        $this->http_status = $status;
    }

    public function getHttpStatus()
    {
        return $this->http_status;
    }

    public function setRedirectUrl($url)
    {
        $this->redirect_url = $url;
    }

    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }
}


function parseFile($file) : array
{
    // Split input file into array
    $parse = array_map("str_getcsv", file($file));

    // Add extra field names to first row
    $parse[0][] = "http_status";
    $parse[0][] = "http_status_type";
    $parse[0][] = "redirect_url";

    // Create a new object for each row
    foreach ($parse as $row) {
        $result[] = new URL($row);
    }

    return $result;
}

function getUrlHeaders($urls) : array
{
    $request_id = 0;
    $responses = array();

    $urls = array_reverse($urls);
    $data_size = count($urls);

    // Store field names at the start of the array
    $responses[-1] = array_pop($urls);

    // Set cURL options
    $curl_opts = array(
      CURLOPT_FOLLOWLOCATION => FALSE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_NOBODY => TRUE,
    );
   
    // Set max number of simultaneous requests
    $data_size = count($urls);    
    $max_requests = 150 < $data_size ? 150 : $data_size;

    // Create master curl_multi handle
    $mh = curl_multi_init();

    $add_handle = function($id) use (&$urls, &$mh, &$curl_opts, &$responses) {
        $responses[$id] = array_pop($urls);

        if ($ch = curl_init($responses[$id]->getAddress())) {
            curl_setopt_array($ch, $curl_opts);

            if ($id !== null) {
                curl_setopt($ch, CURLOPT_PRIVATE, $id);
            }

            curl_multi_add_handle($mh, $ch);
        }
    };
    
    // Create initial requests
    for ($request_id; $request_id < $max_requests; $request_id++) {
        $add_handle($request_id);
    }

    do {
        // Execute master handle
        curl_multi_exec($mh, $running);

        /* Blocks here until a handle returns data - comment this out for a slight speed increase
           at the expense of extra cpu load */
        curl_multi_select($mh);

        // Return queue might contain multiple handles, loop until queue is empty
        while ($returned = curl_multi_info_read($mh)) {
            // Get id of returned cURL handle
            $id = curl_getinfo($returned['handle'], CURLINFO_PRIVATE);
            $info = curl_getinfo($returned['handle']);

            // Store returned data and remove handle from processing queue
            $responses[$id]->setHttpStatus($info['http_code']);
            $responses[$id]->setRedirectUrl($info['redirect_url']);
            curl_multi_remove_handle($mh, $returned['handle']);

            // Create a new handle for the next URL
            if ($urls) {
                $add_handle($request_id);
                $request_id++;
            }       
        }
    } while ($running > 0);

    // Close master handle
    return $responses;
}


function fileWriter($res)
{
    ob_start();
    $df = fopen("output/out.csv", 'w');

    // Output field headers
    $headers = array_shift($res);
    fputcsv($df, $headers->getData());

    foreach ($res as $result) {
        // Ignore valid URLs
        if ($result->getHttpStatus() != 200) {
            $out = array();

            // Output original row data
            foreach ($result->getData() as $uploaded_file) {
                $out[] = $uploaded_file;
            }

            // Output HTTP Status
            $out[] = $result->getHttpStatus();

            // Output relevant HTTP status info
            if ($result->getHttpStatus() >= 300 && $result->getHttpStatus() < 400) {
                $out[] = 'Redirected';
            } else {
                $out[] = 'Not Found';
            }

            $out[] = $result->getRedirectUrl();

            // Write result to 'output/out.csv'
            fputcsv($df, $out);
        }
    }

    fclose($df);
    ob_get_clean();
}


function errorMessage(string $error)
{
    echo '<link rel="stylesheet" href="style.css">
          <div id="container">
              <h1>URL checker</h1>
              <p>Because sometimes, your shit gets moved</p>
              <div id="inner-container">' . $error . '. Please stop trying to beak my thing &#9785;</div>
              <input id="return-button" type="submit" value="Ok" name="submit2" onclick="location.href=\'./\';" />
          </div>';
}


// Check uploaded file is valid
$finfo = new finfo(FILEINFO_MIME_TYPE);
$file_type = $finfo->file($_FILES['file']['tmp_name']);

if ($_FILES['file']['error'] == 4) {
    $error = "No file has been selected";
    errorMessage($error);
} else if ($file_type != 'text/csv' && $file_type != 'text/plain') {
    $error = "Uploaded file type is an incorrect type ($file_type)";
    errorMessage($error); 
} else if ($_FILES['file']['error'] > 0) {
    $error = "An error has occurred";
    errorMessage($error);
} else {
    $data = parseFile($_FILES['file']['tmp_name']);
    $res = getUrlHeaders($data);
    fileWriter($res);

    // Go to success page on completion
    header('Location: done.html');
}

