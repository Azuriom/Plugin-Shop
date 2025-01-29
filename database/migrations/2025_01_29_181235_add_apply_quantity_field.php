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
        DB::table('shop_packages')->get()->each(function ($command) {
            $commands = json_decode($command->commands, true);

            foreach ($commands as $key => &$commandItem) {
                if (! isset($commandItem['apply_quantity'])) {
                    $commandItem['apply_quantity'] = 0;
                }
            }

            DB::table('shop_packages')
                ->where('id', $command->id)
                ->update(['commands' => json_encode($commands)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('shop_packages')->get()->each(function ($command) {
            $commands = json_decode($command->commands, true);

            foreach ($commands as $key => &$commandItem) {
                if (isset($commandItem['apply_quantity'])) {
                    unset($commandItem['apply_quantity']);
                }
            }

            DB::table('shop_packages')
                ->where('id', $command->id)
                ->update(['commands' => json_encode($commands)]);
        });
    }
};
