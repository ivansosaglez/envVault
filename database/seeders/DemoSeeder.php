<?php

namespace Database\Seeders;

use App\Actions\CreateEnvironment;
use App\Actions\CreateProject;
use App\Actions\RecordActivity;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo account with a project whose environments differ on purpose, so the
 * comparison, validator and health indicators have something to show.
 *
 * Every value below is a fake placeholder.
 */
class DemoSeeder extends Seeder
{
    public const EMAIL = 'demo@example.com';

    public const PASSWORD = 'password';

    public function run(
        CreateProject $createProject,
        CreateEnvironment $createEnvironment,
        RecordActivity $record,
    ): void {
        $user = User::updateOrCreate(
            ['email' => self::EMAIL],
            ['name' => 'Demo User', 'password' => self::PASSWORD],
        );

        // Idempotent: rebuild the demo project from scratch on every run.
        $user->projects()->where('name', 'Laravel SaaS')->delete();

        $project = $createProject($user, ['name' => 'Laravel SaaS', 'description' => 'Multi-tenant SaaS built with Laravel']);

        $environments = [
            'Local' => $createEnvironment($project, ['name' => 'Local', 'color' => 'sky']),
            'Staging' => $createEnvironment($project, ['name' => 'Staging', 'color' => 'amber']),
            'Production' => $createEnvironment($project, ['name' => 'Production', 'color' => 'emerald']),
        ];

        // key => [local, staging, production, secret?]. null means "not defined in that environment".
        $variables = [
            'APP_NAME' => ['Laravel SaaS', 'Laravel SaaS', 'Laravel SaaS', false],
            'APP_ENV' => ['local', 'staging', 'production', false],
            'APP_KEY' => ['base64:LOCALdemoKEYdemoKEYdemoKEYdemoKEY00000=', 'base64:STAGINGdemoKEYdemoKEYdemoKEYdemoKE000=', 'base64:PRODdemoKEYdemoKEYdemoKEYdemoKEYdemo00=', true],
            'APP_DEBUG' => ['true', 'false', 'false', false],
            'APP_URL' => ['http://laravel-saas.test', 'https://staging.laravel-saas.example', 'https://app.laravel-saas.example', false],
            'LOG_LEVEL' => ['debug', null, 'warning', false],
            'DB_CONNECTION' => ['pgsql', 'pgsql', 'pgsql', false],
            'DB_HOST' => ['127.0.0.1', 'db.staging.internal', 'db.production.internal', false],
            'DB_PORT' => ['5432', '5432', '5432', false],
            'DB_DATABASE' => ['saas_local', 'saas_staging', 'saas', false],
            'DB_USERNAME' => ['postgres', 'saas_app', 'saas_app', false],
            'DB_PASSWORD' => ['secret', '', 'fake-production-password', true],
            'REDIS_HOST' => ['127.0.0.1', 'redis.staging.internal', null, false],
            'CACHE_STORE' => ['redis', 'redis', 'database', false],
            'QUEUE_CONNECTION' => ['sync', 'redis', 'redis', false],
            'MAIL_MAILER' => ['log', 'smtp', 'ses', false],
            'STRIPE_KEY' => ['pk_test_fake_local', 'pk_test_fake_staging', 'pk_live_fake_production', false],
            'STRIPE_SECRET' => ['sk_test_fake_local', 'sk_test_fake_staging', 'sk_live_fake_production', true],
            'STRIPE_WEBHOOK_SECRET' => ['whsec_fake_local', null, 'whsec_fake_production', true],
            'SENTRY_LARAVEL_DSN' => [null, null, 'https://fake@sentry.example/1', true],
        ];

        foreach ($variables as $key => [$local, $staging, $production, $secret]) {
            foreach (['Local' => $local, 'Staging' => $staging, 'Production' => $production] as $name => $value) {
                if ($value !== null) {
                    $environments[$name]->variables()->create(['key' => $key, 'value' => $value, 'is_secret' => $secret]);
                }
            }
        }

        // A believable recent history (names only, never values), oldest first.
        $history = [
            [ActivityAction::VariableAdded, 'STRIPE_WEBHOOK_SECRET', 'Production', 190],
            [ActivityAction::VariableUpdated, 'APP_DEBUG', 'Staging', 95],
            [ActivityAction::VariableDeleted, 'OLD_API_KEY', 'Production', 40],
            [ActivityAction::VariableAdded, 'SENTRY_LARAVEL_DSN', 'Production', 12],
            [ActivityAction::VariableUpdated, 'DB_PASSWORD', 'Production', 3],
        ];

        foreach ($history as [$action, $subject, , $minutesAgo]) {
            $record($project, $action, $subject)->forceFill(['created_at' => now()->subMinutes($minutesAgo)])->save();
        }
    }
}
