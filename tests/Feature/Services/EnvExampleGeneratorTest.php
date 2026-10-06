<?php

use App\Services\EnvExampleGenerator;

it('generates keys without values, grouped by prefix', function () {
    $output = (new EnvExampleGenerator)->generate(['STRIPE_SECRET', 'APP_NAME', 'DB_HOST', 'APP_ENV', 'REDIS_HOST', 'STRIPE_KEY']);

    expect($output)->toBe(<<<'ENV'
    APP_ENV=
    APP_NAME=

    DB_HOST=

    REDIS_HOST=

    STRIPE_KEY=
    STRIPE_SECRET=

    ENV);
});

it('is deterministic regardless of input order and removes duplicates', function () {
    $generator = new EnvExampleGenerator;

    expect($generator->generate(['B_X', 'A_X', 'B_X']))->toBe($generator->generate(['A_X', 'B_X']));
});

it('returns an empty string when there are no keys', function () {
    expect((new EnvExampleGenerator)->generate([]))->toBe('');
});
