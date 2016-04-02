<?php

namespace Surume\Runtime\Command\Arch;

use Surume\Channel\Extra\Request;
use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Throwable\Exception\Runtime\Execution\RejectionException;
use Surume\Promise\Promise;
use Surume\Runtime\RuntimeCommand;

class ArchStartCommand extends Command implements CommandInterface
{
    /**
     * ChannelBaseInterface
     */
    protected $channel;

    /**
     *
     */
    protected function construct()
    {
        $this->channel = $this->runtime->core()->make('Surume\Runtime\Channel\ChannelInterface');
    }

    /**
     *
     */
    protected function destruct()
    {
        unset($this->channel);
    }

    /**
     * @param mixed[] $params
     * @return mixed
     */
    protected function command($params = [])
    {
        $runtime = $this->runtime;
        $channel = $this->channel;
        $promise = $this->runtime->start();

        return $promise
            ->then(
                function() use($runtime) {
                    return $runtime->manager()->getRuntimes();
                }
            )
            ->then(
                function($children) use($channel) {
                    $promises = [];

                    foreach ($children as $childAlias)
                    {
                        $req = new Request(
                            $channel,
                            $childAlias,
                            new RuntimeCommand('arch:start')
                        );

                        $promises[] = $req->call();
                    }

                    return Promise::all($promises);
                }
            )
            ->then(
                function() {
                    return 'Part of architecture has been started.';
                },
                function() {
                    throw new RejectionException('Part of architecture could not be started.');
                }
            )
        ;
    }
}
