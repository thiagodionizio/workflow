<?php
namespace Thiagodionizio\Workflow\Enqueue;

use Enqueue\Client\ProducerInterface;
use Thiagodionizio\Workflow\Token;

class AsyncTransition implements \Thiagodionizio\Workflow\AsyncTransition
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @param ProducerInterface $producer
     */
    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    /**
     * {@inheritdoc}
     */
    public function transition(array $tokens)
    {
        foreach ($tokens as $token) {
            /** @var Token $token */

            $this->producer->sendCommand(
                HandleAsyncTransitionProcessor::COMMAND,
                HandleAsyncTransition::forToken($token)
            );
        }
    }
}