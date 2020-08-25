<?php
namespace Formapro\Pvm;

final class ClassClosure
{
    const CLASS_MAP = [
        Process::SCHEMA => Process::class,
        Node::SCHEMA => Node::class,
        Token::SCHEMA => Token::class,
        Transition::SCHEMA => Transition::class,
        TokenTransition::SCHEMA => TokenTransition::class,
    ];

    /**
     * @var ClassClosure
     */
    private static $instance;

    public function __invoke(array $values)
    {
        if (array_key_exists('schema', $values) && array_key_exists($values['schema'], self::CLASS_MAP)) {
            return self::CLASS_MAP[$values['schema']];
        }
    }

    public static function create(): ClassClosure
    {
        if (false == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}