<?php

namespace Wuwx\LaravelAutoNumber;

use Wuwx\LaravelAutoNumber\Observers\AutoNumberObserver;

trait AutoNumberTrait
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootAutoNumberTrait()
    {
        static::observe(AutoNumberObserver::class);
    }

    /**
     * Return the autonumber configuration array for this model.
     *
     * @return array
     */
    abstract public function getAutoNumberOptions();
}
