<?php

namespace App\Console\Commands;

use App\Models\GemaraCase;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Fixtures for the browser suite.
 *
 * The E2E tests run against the real development database rather than an
 * isolated one, so everything they create is tagged and removable. Creating a
 * dedicated database needs MySQL admin rights the application user does not
 * have; tagging is the next best thing and keeps the suite from leaving debris
 * in, or deleting, real development data.
 */
class E2eFixtures extends Command
{
    protected $signature = 'e2e:fixtures {action : seed|clean}';

    protected $description = 'Create or remove the tagged fixtures used by the Playwright suite';

    public const EMAIL = 'e2e-fixture@example.test';
    public const PASSWORD = 'e2e-fixture-password';
    public const TAG = 'E2E-FIXTURE';

    public function handle(): int
    {
        return $this->argument('action') === 'seed' ? $this->seed() : $this->clean();
    }

    private function seed(): int
    {
        $this->clean();

        $user = User::create([
            'name' => 'E2E Fixture User',
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now(),
        ]);

        // A fully answered case, so the diagram renders every oval and all
        // eight arrows when it is opened.
        $case = new GemaraCase([
            'masechet' => 'Berakhot',
            'daf' => '7a',
            'gemara_text' => self::TAG . ' gemara text',
            'title' => self::TAG . ' complete case',
            'din_type' => 'מותר',
            'act' => self::TAG . ' act',
            'public' => true,
        ]);

        foreach (GemaraCase::inputConditions as $inputCondition) {
            $case->{$inputCondition} = self::TAG . ' ' . $inputCondition;
            $case->{$inputCondition . '_nr'} = false;
        }

        $case->user_id = $user->id;
        $case->save();

        // A second, disposable case: the delete test destroys whatever it
        // targets, and it must not be the one other tests read from.
        $deletable = $case->replicate();
        $deletable->title = self::TAG . ' deletable case';
        $deletable->user_id = $user->id;
        $deletable->save();

        $this->line(json_encode([
            'userId' => $user->id,
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
            'completeCaseId' => $case->id,
            'deletableCaseId' => $deletable->id,
        ]));

        return self::SUCCESS;
    }

    private function clean(): int
    {
        $user = User::where('email', self::EMAIL)->first();

        if ($user) {
            GemaraCase::where('user_id', $user->id)->delete();
            $user->delete();
        }

        GemaraCase::where('title', 'like', '%' . self::TAG . '%')->delete();

        return self::SUCCESS;
    }
}
