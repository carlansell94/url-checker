<?php

namespace carlansell94\UrlChecker;

class FileHandler
{
    /**
     * @return array<mixed>
     */
    public static function load(): array|string|false
    {
        if ($error = self::fileIsInvalid()) {
            return $error;
        }

        return self::toArray($_FILES['file']['tmp_name']);
    }

    private static function fileIsInvalid(): string|false
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $file_type = $finfo->file($_FILES['file']['tmp_name']);

        if (!in_array($file_type, array('text/csv', 'text/plain'))) {
            return "Uploaded file type is an incorrect type ($file_type)";
        }

        if ($_FILES['file']['error'] > 0) {
            if ($_FILES['file']['error'] == 4) {
                return "No file has been selected";
            }

            return "An error has occurred";
        }

        return false;
    }

    /**
     * @return array<mixed>
     */
    private static function toArray(string $file): array|false
    {
        $result = array();

        if (!$handle = fopen($file, "r")) {
            return false;
        };

        if (!$header = fgetcsv($handle)) {
            return false;
        }

        while ($row = fgetcsv($handle)) {
            $result[] = array_combine($header, $row);
        }

        return $result;
    }
}
