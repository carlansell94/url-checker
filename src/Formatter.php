<?php

namespace carlansell94\UrlChecker;

class Formatter
{
    /**
     * @param array<array<mixed>> $input
     * @return array<array<mixed>>
     */
    public static function filter(array $input, string ...$fields): array
    {
        $trimmed = array();
        array_unshift($fields, 'data');
        $count = count($input);

        for ($i = 0; $i < $count; $i++) {
            foreach ($fields as $field) {
                if (isset($input[$i][$field])) {
                    $trimmed[$i][$field] = $input[$i][$field];
                }
            }
        }

        return $trimmed;
    }

    /**
     * @param array<array<mixed>> $input
     */
    public static function toJson(array $input): string|false
    {
        return json_encode($input, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
    }

    /**
     * @param array<array<mixed>> $input
     */
    public static function toJsonFile(array $input, string $filepath): bool
    {
        if (file_put_contents($filepath, self::toJson($input))) {
            return true;
        }

        return false;
    }

    /**
     * @param array<array<mixed>> $input
     */
    public static function toCsv(array $input, string $file): void
    {
        $parse_data = function ($row) {
            if (!isset($row['data'])) {
                return $row;
            }

            $data = $row['data'];
            unset($row['data']);
            return $data + $row;
        };

        if (!$df = fopen($file, 'w')) {
            return;
        }

        ob_start();
        fputcsv($df, array_keys($parse_data($input[0])));

        foreach ($input as $value) {
            $value = $parse_data($value);

            foreach ($value as $key => &$field) {
                if (is_array($field)) {
                    $field = json_encode($field, JSON_FORCE_OBJECT);
                }
            }

            fputcsv($df, array_values($value));
        }

        fclose($df);
        ob_get_clean();
    }
}
