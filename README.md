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
- Output file is stored in output/out.php. This folder must be writeable for the checker to work.
- Any URL with a returned HTTP status that is not 200 is returned. Valid URLs are ignored.
- Includes two sections to add an image.

On a Raspberry Pi 3B+, with the curl_multi_select call enabled, this app was able to check 50,000 URLs in just over 30 seconds.
