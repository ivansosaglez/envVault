<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ParsedEnv;

/**
 * Parses the common subset of the .env syntax:
 *
 *  - KEY=value, with optional spaces around "=" and an optional `export ` prefix
 *  - "double quoted" values (escapes: \n \r \t \" \\) and 'single quoted' values (literal)
 *  - full-line comments (#) and inline comments after unquoted values (" # ...")
 *  - blank lines are ignored; a repeated key keeps its last value
 *
 * Not supported: multi-line values and ${VARIABLE} expansion.
 */
final class EnvParser
{
    public function parse(string $content): ParsedEnv
    {
        $variables = [];
        $duplicates = [];
        $invalidLines = [];

        foreach (preg_split('/\R/', $content) as $index => $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $line = preg_replace('/^export\s+/', '', $line);

            if (! preg_match('/^([A-Za-z_][A-Za-z0-9_.]*)\s*=\s*(.*)$/', $line, $matches)) {
                $invalidLines[] = $index + 1;

                continue;
            }

            [, $key, $rawValue] = $matches;
            $value = $this->parseValue($rawValue);

            if ($value === null) {
                $invalidLines[] = $index + 1;

                continue;
            }

            if (array_key_exists($key, $variables) && ! in_array($key, $duplicates, true)) {
                $duplicates[] = $key;
            }

            $variables[$key] = $value;
        }

        return new ParsedEnv($variables, $duplicates, $invalidLines);
    }

    private function parseValue(string $raw): ?string
    {
        if (str_starts_with($raw, '"')) {
            if (! preg_match('/^"((?:[^"\\\\]|\\\\.)*)"\s*(?:#.*)?$/', $raw, $m)) {
                return null;
            }

            return strtr($m[1], ['\\n' => "\n", '\\r' => "\r", '\\t' => "\t", '\\"' => '"', '\\\\' => '\\']);
        }

        if (str_starts_with($raw, "'")) {
            return preg_match("/^'([^']*)'\s*(?:#.*)?$/", $raw, $m) ? $m[1] : null;
        }

        return trim(preg_replace('/\s+#.*$/', '', $raw));
    }
}
