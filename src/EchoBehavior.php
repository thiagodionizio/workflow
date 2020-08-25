<?php
namespace Formapro\Pvm;


class EchoBehavior implements Behavior
{
    public function execute(Token $token)
    {
        echo $token->getCurrentTransition()->getTransition()->getTo()->getOption('text') . PHP_EOL;
    }
}
