![Composer workflow](https://github.com/carlansell94/url_checker/actions/workflows/composer.yml/badge.svg?event=push)
![PHPStan workflow](https://github.com/carlansell94/url_checker/actions/workflows/phpstan.yml/badge.svg?event=push)

A PHP based URL checker, using cURL multi.

On a Raspberry Pi 3B+, this app was able to check 50,000 URLs in just over 30 seconds.

## Features:
* Ability to check either a single URL, or multiple URLs
* Can check URLs uploaded to a CSV file, preserving any other data linked to the URL
* Customisable maximum number of requests (150 by default)
* Multiple options to format the result

## Requirements:
* PHP 8.0 or above
* Ability to upload files (if using the provided example)

# Installation
This project is designed to be installed using composer.

To do this, clone the repository and run
```
composer update
composer install -o
```
inside the cloned folder.

# Running the Checker
Three checking functions are provided. These are:

| Function | Inputs | Info |
| -------- | ------ | ---- |
| ```Check::single()``` | String URL | Takes a single URL, formatted as a string, and returns a multi-dimensional array containing the single result |
| ```Check::multiple()``` | Array URLs | Takes an array, containing URLs, and returns a multi-dimensional array of results. |
| ```Check::fileUpload()``` | - | Reads an uploaded CSV file, creates an array from the contents, and passes it to ```Check::multiple()``` |

Each option returns an array containing the response data. This result array can be parsed manually, or passed to one of the ```UrlChecker\Formatter``` options described below.

All three options return a multi-dimensional array (including ```Check::single()```), for consistency.

## Check Single
Takes a single URL as an input, and returns the array response. Not much to explain here.

### Basic Usage
```
$result = UrlChecker::check($url);
```

## Check Multiple
```Check::multiple()``` uses ```curl_multi``` to fire multiple requests at once.

The maximum number of open request handles can be set using ```Check::setMaxRequests()```. By default, this value is 150. Call this function before calling ```Check::multiple()``` for the new value to take effect.

### Basic Usage

```
UrlChecker\Check::setMaxRequests(500);
$result = UrlChecker\Check::multiple($urls);
```

## CSV File Upload
The function uses the temporary file loaded into the ```$_FILES``` variable. This can be populated by loading a file through an HTML form.

The first row of the CSV will be parsed as headers. Each header should be unique to prevent data loss.

The first column should contain the URLs to check. Additional columns of data can be provided, and will be retained.

When outputting data to a CSV using ```Formatter::toCsv()```, the structure of the input file is retained, with additional columns added containing the response data.

For other response types, including the raw data array returned by any of the public ```UrlChecker\Check``` functions, the input data is available under the ```data``` array key.

If an error occurs during the loading of a file, ```Check::fileUpload()``` will return a string containing the error response message.

### Basic Usage
```
$result = Check::fileUpload();
```

# Formatting The Result
This app also provides functionality to filter and format the response, as well as saving the response to a file.

The available options are:

| Function                    | Inputs                        | Info |
| --------------------------- | ----------------------------- | ---  |
| ```Formatter::toJson()```     | Result array                  | Returns the result converted to pretty-printed JSON. |
| ```Formatter::toJsonFile()``` | Result array, string filepath | Runs ```Formatter::toJson()```, then saves the result to the file path specificed. |
| ```Formatter::toCsv()```      | Result array, string filepath | Saves the result to a CSV file with the file path specified. Any array values included in the result set are saved as a JSON string. |
| ```Formatter::toHtml()```     | Result array                  | Returns an HTML table using the result. |

Each of these functions expects an array containing data returned by one of the public functions available in ```UrlChecker\Check``` outlined above.

## Filtering Output Fields
By default, the result will retain all values returned by cURL.

To ensure only the required fields are returned, pass the ```UrlChecker\Check``` result array to ```Formatter::filter()```, along with the fields you wish to retain.

For example, to obtain a basic result featuring only the HTTP code and redirect URL, you can use the following.

```
$filtered = UrlChecker\Formatter::filter(
    $result,
    'http_code',
    'redirect_url'
)
```

The available fields returned by cURL are as follows:

| Name                    |
| ----------------------- |
| url                     |
| content_type            | 
| http_code               |
| header_size             |
| request_size            |
| filetime                |
| ssl_verify_result       |
| redirect_count          |
| total_time              |
| namelookup_time         |
| connect_time            |
| pretransfer_time        |
| size_upload             |
| size_download           |
| speed_download          |
| speed_upload            |
| download_content_length |
| upload_content_length   |
| starttransfer_time      |
| redirect_time           |
| redirect_url            |
| primary_ip              |
| certinfo                |
| primary_port            |
| local_ip                |
| local_port              |
| http_version            |
| protocol                |
| ssl_verifyresult        |
| scheme                  |
| appconnect_time_us      |
| connect_time_us         |
| namelookup_time_us      |
| pretransfer_time_us     |
| redirect_time_us        |
| starttransfer_time_us   |
| total_time_us           |

You can pass as many of these field names to ```Formatter::filter()``` as required.

# Example
In the ```example``` folder, you'll find a basic example demonstrating the checker using a file upload.

Navigate to ```/example``` in your browser, and upload a correctly-formatted CSV file. The result will be displayed in both JSON form in the browser, and saved to ```example/output.csv``` using the CSV formatter.

The main functionality can be found in ```example/run.php```. Have a play with this file, to get a feel for how the application works.