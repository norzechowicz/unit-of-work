<?php

namespace Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

final class NewCommand
{
    /**
     * @var mixed
     */
    private $object;

    /**
     * @var int
     */
    private $totalNewObjects;

    /**
     * @param mixed $object
     * @param int $totalNewObjects
     * @throws InvalidArgumentException
     */
    public function __construct($object, $totalNewObjects)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf("New command require object \"%s\" type passed.", gettype($object)));
        }

        if (!is_integer($totalNewObjects)) {
            throw new InvalidArgumentException(sprintf("Total new objects count must be valid integer."));
        }

        $this->object = $object;
        $this->totalNewObjects = $totalNewObjects;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return int
     */
    public function getTotalNewObjects()
    {
        return $this->totalNewObjects;
    }
}
