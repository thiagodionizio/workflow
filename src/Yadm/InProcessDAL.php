<?php
namespace Thiagodionizio\Workflow\Yadm;

use Thiagodionizio\Workflow\InMemoryDAL;
use Thiagodionizio\Workflow\Process;
use Thiagodionizio\Workflow\Token;
use Thiagodionizio\Workflow\TokenException;
use function Formapro\Yadm\get_object_id;
use Formapro\Yadm\Storage;

class InProcessDAL extends InMemoryDAL
{
    /**
     * @var Storage
     */
    private $processStorage;

    public function __construct(Storage $processStorage)
    {
        $this->processStorage = $processStorage;
    }

    public function getToken(string $id): Token
    {
        /** @var Process $process */
        if (false == $process = $this->processStorage->findOne(['tokens.'.$id => ['$exists' => true]])) {
            throw TokenException::notFound(sprintf('The token "%s" could not be found', $id));
        }

        return $this->getProcessToken($process, $id);
    }

    public function persistToken(Token $token)
    {
        $this->persistProcess($token->getProcess());
    }

    public function persistProcess(Process $process)
    {
        get_object_id($process, true) ? $this->processStorage->update($process) : $this->processStorage->insert($process);
    }
}
