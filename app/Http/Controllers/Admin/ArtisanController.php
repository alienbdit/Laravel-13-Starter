<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class ArtisanController extends Controller
{
    private const COMMANDS = [
        'Cache' => [
            'cache:clear'    => 'Flush the application cache',
            'config:clear'   => 'Remove the configuration cache file',
            'config:cache'   => 'Create a cache file for faster configuration loading',
            'route:clear'    => 'Remove the route cache file',
            'route:cache'    => 'Create a route cache file for faster routing',
            'view:clear'     => 'Clear all compiled view files',
            'view:cache'     => 'Compile all of the application\'s Blade templates',
            'optimize'       => 'Cache the framework bootstrap, configuration, and metadata',
            'optimize:clear' => 'Remove all cached bootstrap files',
        ],
        'Database' => [
            'migrate'          => 'Run any pending database migrations',
            'migrate:rollback' => 'Roll back the last batch of migrations',
            'migrate:status'   => 'Show the status of each migration file',
            'db:seed'          => 'Seed the database (RolePermissionSeeder)',
        ],
        'System' => [
            'storage:link'  => 'Create a symbolic link from public/storage to storage/app/public',
            'queue:restart' => 'Signal queue workers to restart after their current job finishes',
        ],
    ];

    // Fixed arguments enforced server-side; not user-controllable
    private const COMMAND_ARGS = [
        'migrate'          => ['--force' => true],
        'migrate:rollback' => ['--force' => true],
        'db:seed'          => ['--class' => 'RolePermissionSeeder', '--force' => true],
    ];

    public function index(): View
    {
        return view('admin.artisan.index', ['commandGroups' => self::COMMANDS]);
    }

    public function run(Request $request): JsonResponse
    {
        $command = $request->input('command');

        $allowed = collect(self::COMMANDS)->flatMap(fn ($cmds) => array_keys($cmds))->all();

        if (! in_array($command, $allowed, true)) {
            return response()->json(['error' => 'Command not allowed.'], 422);
        }

        $args = self::COMMAND_ARGS[$command] ?? [];

        try {
            $exitCode = Artisan::call($command, $args);
            $output   = Artisan::output();
        } catch (\Throwable $e) {
            return response()->json([
                'success'   => false,
                'exit_code' => 1,
                'output'    => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success'   => $exitCode === 0,
            'exit_code' => $exitCode,
            'output'    => $output ?: '(no output)',
        ]);
    }
}
