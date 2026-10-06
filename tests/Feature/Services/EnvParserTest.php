<?php

use App\Services\EnvParser;

function parseEnv(string $content)
{
    return (new EnvParser)->parse($content);
}

it('parses plain, double quoted and single quoted values', function () {
    $parsed = parseEnv(<<<'ENV'
    APP_NAME=Test
    APP_TITLE="Test"
    APP_SLUG='Test'
    ENV);

    expect($parsed->variables)->toBe(['APP_NAME' => 'Test', 'APP_TITLE' => 'Test', 'APP_SLUG' => 'Test']);
});

it('ignores comments and blank lines', function () {
    $parsed = parseEnv("# comment\n\nDB_HOST=localhost\n   \n  # indented comment\n");

    expect($parsed->variables)->toBe(['DB_HOST' => 'localhost'])
        ->and($parsed->invalidLines)->toBe([]);
});

it('supports the export prefix', function () {
    expect(parseEnv('export APP_ENV=local')->variables)->toBe(['APP_ENV' => 'local']);
});

it('trims whitespace around keys and values', function () {
    expect(parseEnv("  APP_ENV =  local  \r\n")->variables)->toBe(['APP_ENV' => 'local']);
});

it('keeps spaces and hashes inside quotes', function () {
    $parsed = parseEnv("A=\"hello # world\"\nB='  spaced  '");

    expect($parsed->variables)->toBe(['A' => 'hello # world', 'B' => '  spaced  ']);
});

it('strips inline comments', function () {
    $parsed = parseEnv("A=value # note\nB=\"quoted\" # note\nC=http://x.test/#anchor");

    expect($parsed->variables)->toBe(['A' => 'value', 'B' => 'quoted', 'C' => 'http://x.test/#anchor']);
});

it('handles escapes in double quotes only', function () {
    $parsed = parseEnv('A="line1\nline2 \"q\""'."\n".'B=\'raw\n\'');

    expect($parsed->variables['A'])->toBe("line1\nline2 \"q\"")
        ->and($parsed->variables['B'])->toBe('raw\n');
});

it('parses empty values', function () {
    expect(parseEnv("A=\nB=\"\"\nC=''")->variables)->toBe(['A' => '', 'B' => '', 'C' => '']);
});

it('keeps the last value of duplicated keys and reports them once', function () {
    $parsed = parseEnv("A=1\nA=2\nA=3\nB=1");

    expect($parsed->variables)->toBe(['A' => '3', 'B' => '1'])
        ->and($parsed->duplicates)->toBe(['A']);
});

it('reports invalid lines instead of failing', function () {
    $parsed = parseEnv("OK=1\nthis is not valid\n1BAD=x\nOPEN=\"unterminated\nFINE=2");

    expect($parsed->variables)->toBe(['OK' => '1', 'FINE' => '2'])
        ->and($parsed->invalidLines)->toBe([2, 3, 4]);
});

it('only keeps the first equals sign as separator', function () {
    expect(parseEnv('APP_KEY=base64:abc==')->variables)->toBe(['APP_KEY' => 'base64:abc==']);
});
