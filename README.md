URL Checker
A PHP based URL checker, using cURL multi.  

Features:
- Checks URLs uploaded in a CSV file.
- Preserves any other data linked to the URL.
- Sends up to 150 requests at a time.
- Can process thousands of URLs in seconds.
- Automatically downloads resulting CSV file.

Technical Details:
- Parses input file with array_map and str_getcsv to ensure columns are parsed correctly.
- Uses curl_multi to batch together up to 150 requests.
- Once a request has returned, the next request replaces it in the processing queue.
- Speed can be increased by commenting out the curl_multi_select call in run.php.
- Output file is stored in output/out.csv. This folder must be writeable for the checker to work.
- Any URL with a returned HTTP status that is not 200 is returned. Valid URLs are ignored.
- Includes two sections to add an image.

Requirements:
- PHP (Tested on 7.2 & 7.3, other versions not guaranteed to work).
- cURL PHP extension (e.g. php7.2-curl)

How To Use:
- Extract the folder onto your server.
- Ensure the 'output' folder is writable.
- Comment out curl_multi_select if desired.
- Navigate to the folder root in your browser, and upload the CSV file. The first line of the CSV is used as a file header, so ensure the uploaded file includes a header on the first line. The URL should be saved to the first column. Any other data you wish to be included on the downloaded file can be added to the subsequent columns.

FAQ:
Q: I'm uploading a valid CSV, but the app tells me the uploaded file is invalid?
A: Check the size of the file is <= the values set for upload_max_filesize and post_max_size in .htaccess, or in the relevant php.ini file if you are not using .htaccess.

Q: I receive a 500 error when I click 'run'?
A: The easiest way to diagnose this is to enable error reporting. To do this, add the following lines to run.php:
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);

   These should be added just above the line:
   // Check uploaded file is valid

   The most common reason for this error is that the cURL PHP extension is not installed/enabled.

Q: I'm using NGINX/Litespeed/Some other server software, can I run this app?
A: Yes, however you will need to check the configuration for your server to ensure your files can be uploaded.

On a Raspberry Pi 3B+, with the curl_multi_select call enabled, this app was able to check 50,000 URLs in just over 30 seconds.
