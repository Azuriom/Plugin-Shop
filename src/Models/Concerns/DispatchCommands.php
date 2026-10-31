<?php

namespace Azuriom\Plugin\Shop\Models\Concerns;

use Azuriom\Models\Server;
use Azuriom\Models\User;
use Illuminate\Support\Collection;

trait DispatchCommands
{
    /**
     * Send the given commands to their server, grouped by whether the player must be online.
     *
     * @param  \Illuminate\Support\Collection<int, array{server: int, require_online: bool, commands: string[]}>  $commands
     */
    protected function sendServerCommands(Collection $commands, User $user): void
    {
        $servers = Server::findMany($commands->pluck('server')->unique());

        foreach ($servers as $server) {
            $serverCommands = $commands->where('server', $server->id);

            foreach ([true, false] as $requireOnline) {
                $commandsToSend = $serverCommands
                    ->filter(fn (array $command) => ((bool) $command['require_online']) === $requireOnline)
                    ->pluck('commands')
                    ->flatten()
                    ->all();

                if (! empty($commandsToSend)) {
                    $server->bridge()->sendCommands($commandsToSend, $user, $requireOnline);
                }
            }
        }
    }
}
