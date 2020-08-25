<?php
namespace Thiagodionizio\Workflow\Enqueue;

use Enqueue\Consumption\Result;
use Thiagodionizio\Workflow\Token;

class HandleAsyncTransitionResult extends Result
{
    private $waitTokens = [];

    public function setWaitTokens(array $tokens)
    {
        $this->waitTokens = $tokens;
    }

    /**
     * @return Token[]
     */
    public function getWaitTokens(): array
    {
        return $this->waitTokens;
    }
}
