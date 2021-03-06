<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\EditCommandHandler;

class EditCommandHandlerMock implements EditCommandHandler
{
    private $persistedObjects = [];

    private $persistedObjectsChanges = [];

    /**
     * @param EditCommand $command
     */
    public function handle(EditCommand $command)
    {
        $this->persistedObjects[] = $command->getObject();
        $this->persistedObjectsChanges[] = $command->getChanges();
    }

    public function objectWasPersisted($object)
    {
        foreach ($this->persistedObjects as $persistedObject) {
            if ($persistedObject === $object) {
                return true;
            }
        }

        return false;
    }

    public function getPersistedObjectChanges($object)
    {
        foreach ($this->persistedObjects as $index => $persistedObject) {
            if ($persistedObject === $object) {
                return $this->persistedObjectsChanges[$index];
            }
        }

        throw new \RuntimeException("Object was not handled");
    }
}
