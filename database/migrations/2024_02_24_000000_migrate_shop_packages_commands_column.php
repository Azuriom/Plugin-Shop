<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->where('name', 'shop.commands')->delete();
        Cache::forget('settings');

        $packages = DB::table('shop_packages')->whereNotNull('commands')->get();

        foreach ($packages as $package) {
            try {
                $commands = json_decode($package->commands, flags: JSON_THROW_ON_ERROR);

                if (empty($commands) || ! is_string($commands[0])) {
                    continue;
                }

                $servers = DB::table('shop_package_server')->where('package_id', $package->id)->get();

                $commands = $servers->pluck('server_id')
                    ->flatMap(function (int $serverId) use ($commands, $package) {
                        return array_map(fn (string $command) => [
                            'commands' => [$command],
                            'trigger' => 'purchase',
                            'require_online' => $package->need_online,
                            'server' => $serverId,
                        ], array_filter($commands));
                    });

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
