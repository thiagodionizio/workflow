<?php
namespace Thiagodionizio\Workflow\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Thiagodionizio\Workflow\DAL;
use Thiagodionizio\Workflow\Process;
use Thiagodionizio\Workflow\Token;
use Thiagodionizio\Workflow\Uuid;
use function Formapro\Values\get_values;

class DoctrineDAL implements DAL
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    
    /**
     * @var string
     */
    private $processClass;
    
    /**
     * @var string
     */
    private $tokenClass;

    public function __construct(ObjectManager $objectManager, string $processClass, string $tokenClass)
    {
        $this->objectManager = $objectManager;
        $this->processClass = $processClass;
        $this->tokenClass = $tokenClass;
    }

    public function createProcessToken(Process $process, string $id = null): Token
    {
        $token = Token::create();
        $token->setId($id ?: Uuid::generate());
        $token->setProcess($process);

        return $token;
    }

    public function forkProcessToken(Token $token, string $id = null): Token
    {
        return $this->createProcessToken($token->getProcess(), $id);
    }

    public function getProcessTokens(Process $process): \Traversable
    {
        /** @var \Thiagodionizio\Workflow\Doctrine\Token[] $ormTokens */
        $ormTokens = $this->objectManager->getRepository($this->tokenClass)->findBy([
            'processId' => $process->getId(),
        ]);

        foreach ($ormTokens as $ormToken) {
            /** @var \Thiagodionizio\Workflow\Doctrine\Token $ormToken */

            $token = Token::create($ormToken->getState());
            $token->setId($ormToken->getId());
            $token->setProcess($process);

            yield $token;
        }
    }

    public function getProcessToken(Process $process, string $id): Token
    {
        /** @var \Thiagodionizio\Workflow\Doctrine\Token $ormToken */
        $ormToken = $this->objectManager->getRepository($this->tokenClass)->findOneBy([
            'processId' => $process->getId(),
            'id' => $id,
        ]);

        if (false == $ormToken) {
            throw new \LogicException(sprintf('The token with id "%s" could not be found', $id));
        }
        $token = Token::create($ormToken->getState());
        $token->setId($ormToken->getId());
        $token->setProcess($process);

        return $token;
    }

    public function persistToken(Token $token)
    {
        /** @var \Thiagodionizio\Workflow\Doctrine\Token $ormToken */
        $ormToken = $this->objectManager->getRepository($this->tokenClass)->findOneBy([
            'processId' => $token->getProcess()->getId(),
            'id' => $token->getId(),
        ]);

        if (false == $ormToken) {
            $ormToken = new $this->tokenClass;
            $ormToken->setId($token->getId());
        }

        $ormToken->setProcessId($token->getProcess()->getId());
        $ormToken->setState(get_values($token));

        $this->objectManager->persist($ormToken);
        $this->objectManager->flush();

        $this->persistProcess($token->getProcess());
    }

    public function persistProcess(Process $process)
    {
        /** @var \Thiagodionizio\Workflow\Doctrine\Process $ormProcess */
        $ormProcess = $this->objectManager->getRepository($this->processClass)->findOneBy([
            'id' => $process->getId()
        ]);

        if (false == $ormProcess) {
            $ormProcess = new $this->processClass;
            $ormProcess->setId($process->getId());
        }

        $ormProcess->setState(get_values($process));

        $this->objectManager->persist($ormProcess);
        $this->objectManager->flush();
    }

    public function getToken(string $id): Token
    {
        /** @var \Thiagodionizio\Workflow\Doctrine\Token $ormToken */
        $ormToken = $this->objectManager->getRepository($this->tokenClass)->findOneBy([
            'id' => $id,
        ]);

        if (false == $ormToken) {
            throw new \LogicException(sprintf('The token with id "%s" could not be found', $id));
        }

        /** @var \Thiagodionizio\Workflow\Doctrine\Process $ormProcess */
        $ormProcess = $this->objectManager->getRepository($this->processClass)->findOneBy([
            'id' => $ormToken->getProcessId(),
        ]);

        if (false == $ormToken) {
            throw new \LogicException(sprintf('The process with id "%s" could not be found', $ormToken->getProcessId()));
        }

        $process = Process::create($ormProcess->getState());

        $token = Token::create($ormToken->getState());
        $token->setId($ormToken->getId());
        $token->setProcess($process);

        return $token;
    }
}
