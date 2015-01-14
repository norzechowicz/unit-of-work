<?php

namespace spec\Coduo\UnitOfWork;

use Coduo\UnitOfWork\ClassDefinition;
use Coduo\UnitOfWork\Command\NewCommand;
use Coduo\UnitOfWork\Command\NewCommandHandler;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\RuntimeException;
use Coduo\UnitOfWork\ObjectStates;
use Coduo\UnitOfWork\ObjectVerifier;
use Coduo\UnitOfWork\Tests\Double\EditablePersistedEntityStub;
use Coduo\UnitOfWork\Tests\Double\NotPersistedEntityStub;
use Coduo\UnitOfWork\Tests\Double\PersistedEntityStub;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UnitOfWorkSpec extends ObjectBehavior
{

    function let(ObjectVerifier $objectVerifier)
    {
        $objectVerifier->isPersisted(Argument::type("Coduo\\UnitOfWork\\Tests\\Double\\NotPersistedEntityStub"))
            ->willReturn(false);
        $objectVerifier->isPersisted(Argument::type("Coduo\\UnitOfWork\\Tests\\Double\\PersistedEntityStub"))
            ->willReturn(true);
        $objectVerifier->isPersisted(Argument::type("Coduo\\UnitOfWork\\Tests\\Double\\EditablePersistedEntityStub"))
            ->willReturn(true);
        $objectVerifier->isEqual(
                Argument::type("Coduo\\UnitOfWork\\Tests\\Double\\EditablePersistedEntityStub"),
                Argument::type("Coduo\\UnitOfWork\\Tests\\Double\\EditablePersistedEntityStub")
            )->willReturn(false);

        $objectVerifier->isEqual(Argument::any(), Argument::any())->willReturn(true);

        $this->beConstructedWith($objectVerifier);
    }

    function it_throw_exception_during_non_object_registration()
    {
        $this->shouldThrow(new InvalidArgumentException("Only object can be register."))
            ->during("register", ["Coduo"]);
    }

    function it_should_throw_exception_when_checking_unregistered_object_state()
    {
        $this->shouldThrow(new RuntimeException("Object need to be registered first in the Unit of Work."))
            ->during("getObjectState", [new \DateTime]);
    }

    function it_tells_when_object_was_registered()
    {
        $object = new NotPersistedEntityStub();
        $this->register($object);
        $this->isRegistered($object)->shouldReturn(true);
    }

    function it_should_return_new_state_when_registering_object_is_not_persisted()
    {
        $object = new NotPersistedEntityStub();

        $this->register($object);

        $this->getObjectState($object)->shouldReturn(ObjectStates::NEW_OBJECT);
    }

    function it_should_return_persisted_objects_state_when_registering_object_is_persisted()
    {
        $object = new PersistedEntityStub();

        $this->register($object);

        $this->getObjectState($object)->shouldReturn(ObjectStates::PERSISTED_OBJECT);
    }

    function it_should_return_edited_state_when_object_was_modified_after_registration()
    {
        $object = new EditablePersistedEntityStub();

        $this->register($object);

        $object->changeName("new name");

        $this->getObjectState($object)->shouldReturn(ObjectStates::EDITED_OBJECT);
    }

    function it_should_throw_exception_on_removing_not_persisted_object()
    {
        $object = new NotPersistedEntityStub();
        $this->shouldThrow(new RuntimeException("Unit of Work can't remove not persisted objects."))
            ->during('remove', [$object]);
    }

    function it_return_removed_state_for_not_registered_but_persisted_object()
    {
        $object = new PersistedEntityStub();
        $this->remove($object);
        $this->getObjectState($object)->shouldReturn(ObjectStates::REMOVED_OBJECT);
    }

    function it_handle_new_command_when_there_is_object_that_should_be_persisted(
        ObjectVerifier $objectVerifier,
        ClassDefinition $classDefinition,
        NewCommandHandler $commandHandler
    ) {
        $object = new NotPersistedEntityStub();
        $objectVerifier->getDefinition($object)->willReturn($classDefinition);
        $classDefinition->hasNewCommandHandler()->willReturn(true);
        $classDefinition->getNewCommandHandler()->willReturn($commandHandler);
        $commandHandler->handle(new NewCommand($object))->shouldBeCalled();

        $this->register($object);
        $this->commit();
    }
}