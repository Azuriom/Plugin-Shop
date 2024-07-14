<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->where('name', 'shop.commands')->delete();

        $packages = DB::table('shop_packages')->whereNotNull('commands')->get();

        foreach ($packages as $package) {
            try {
                $commands = json_decode($package->commands, true, flags: JSON_THROW_ON_ERROR);

                if (! is_array($commands) || empty($commands = array_filter($commands))) {
                    DB::table('shop_packages')->where('id', $package->id)->update([
                        'commands' => '[]',
                    ]);

                    continue;
                }

                if (array_key_exists('commands', $commands[0])) {
                    return;
                }

                foreach ($commands as &$command) {
                    $command['commands'] = [$command['command']];
                    unset($command['command']);
                }

                DB::table('shop_packages')->where('id', $package->id)->update([
                    'commands' => $commands,
                ]);
            } catch (JsonException) {
                // ignore invalid JSON
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ignore
    }
};
