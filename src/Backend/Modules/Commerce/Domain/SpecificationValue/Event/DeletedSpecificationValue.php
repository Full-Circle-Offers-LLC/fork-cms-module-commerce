<?php

namespace Backend\Modules\Commerce\Domain\SpecificationValue\Event;

final class DeletedSpecificationValue extends Event
{
    /**
     * @var string The name the listener needs to listen to to catch this event.
     */
    const EVENT_NAME = 'commerce.event.specification_value.deleted';
}