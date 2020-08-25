<?php
namespace Thiagodionizio\Workflow\Yadm;

use Thiagodionizio\Workflow\Process;
use Thiagodionizio\Workflow\Token;
use Thiagodionizio\Workflow\DAL;
use Thiagodionizio\Workflow\TokenException;
use Thiagodionizio\Workflow\Uuid;
use function Formapro\Values\get_value;
use function Formapro\Values\set_value;
use function Formapro\Yadm\get_object_id;
use Formapro\Yadm\Storage;

class StandaloneTokenDAL implements DAL
{
    /**
     * @var Storage
     */
    private $processStorage;

    /**
     * @var Storage
     */
    private $tokenStorage;

    public function __construct(Storage $processStorage, Storage $tokenStorage)
    {
        $this->processStorage = $processStorage;
        $this->tokenStorage = $tokenStorage;
    }

    public function createProcessToken(Process $process, string $id = null): Token
    {
        $token = Token::create();
        $token->setId($id ?: Uuid::generate());
        $token->setProcess($process);

        set_value($token, 'processId', $process->getId());

        return $token;
    }

    public function forkProcessToken(Token $token, string $id = null): Token
    {
        return $this->createProcessToken($token->getProcess(), $id);
    }

    public function getProcessTokens(Process $process): \Traversable
    {
        foreach ($this->tokenStorage->find(['processId' => $process->getId()]) as $token) {
            /** @var Token $token */

            $token->setProcess($process);

            yield $token;
        }
    }

    public function getProcessToken(Process $process, string $id): Token
    {
        /** @var Token $token */
        if (false == $token = $this->tokenStorage->findOne(['id' => $id])) {
            throw TokenException::notFound($id);
        }

        if ($process->getId() !== get_value($token, 'processId')) {
            throw new TokenException('Another process token requested.');
        }

        $token->setProcess($process);

        return $token;
    }

    public function getToken(string $id): Token
    {
        /** @var Token $token */
        if (false == $token = $this->tokenStorage->findOne(['id' => $id])) {
            throw TokenException::notFound($id);
        }

        $processId = get_value($token, 'processId');

        /** @var Process $process */
        if (false == $process = $this->processStorage->findOne(['id' => $processId])) {
            throw new TokenException(sprintf('The process "%s" could not be found', $processId));
        }

        $token->setProcess($process);

        return $token;
    }

    public function persistToken(Token $token)
    {
        $this->persistProcess($token->getProcess());

        get_object_id($token, true) ? $this->tokenStorage->update($token) : $this->tokenStorage->insert($token);
    }

    public function persistProcess(Process $process)
    {
        get_object_id($process, true) ? $this->processStorage->update($process) : $this->processStorage->insert($process);
    }
}
