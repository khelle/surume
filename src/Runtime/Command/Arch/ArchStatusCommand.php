<?php

namespace Surume\Runtime\Command\Arch;

use Surume\Channel\Extra\Request;
use Surume\Runtime\Command\Command;
use Surume\Command\CommandInterface;
use Surume\Promise\Promise;
use Surume\Runtime\RuntimeCommand;

class ArchStatusCommand extends Command implements CommandInterface
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
        $promise = Promise::doResolve();

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
                            new RuntimeCommand('arch:status')
                        );

                        $promises[] = $req->call();
                    }

                    return Promise::all($promises);
                }
            )
            ->then(
                function($childrenData) use($runtime) {
                    return [
                        'parent'   => $runtime->parent(),
                        'alias'    => $runtime->alias(),
                        'name'     => $runtime->name(),
                        'state'    => $runtime->state(),
                        'children' => $childrenData
                    ];
                }
            )
        ;
    }
}
