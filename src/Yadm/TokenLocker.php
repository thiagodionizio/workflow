<?php
namespace Thiagodionizio\Workflow\Yadm;

use Thiagodionizio\Workflow\PessimisticLockException;
use Thiagodionizio\Workflow\TokenLockerInterface;
use Formapro\Yadm\PessimisticLock;
use Formapro\Yadm\PessimisticLockException as YadmPessimisticLockException;

class TokenLocker implements TokenLockerInterface
{
    /**
     * @var PessimisticLock
     */
    private $lock;

    public function __construct(PessimisticLock $lock)
    {
        $this->lock = $lock;
    }

    public function lock(string $tokenId, bool $blocking = true)
    {
        try {
            $this->lock->lock($tokenId, $blocking);
        } catch (YadmPessimisticLockException $e) {
            throw PessimisticLockException::lockFailed($e);
        }
    }

    public function unlock(string $tokenId)
    {
        $this->lock->unlock($tokenId);
    }

    public function locked(string $tokenId): bool
    {
        return $this->lock->locked($tokenId);
    }
}
