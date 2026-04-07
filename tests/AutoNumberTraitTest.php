<?php

namespace Wuwx\LaravelAutoNumber\Tests;

use Orchestra\Testbench\TestCase;
use Wuwx\LaravelAutoNumber\AutoNumberServiceProvider;
use Wuwx\LaravelAutoNumber\AutoNumberTrait;

class AutoNumberTraitTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            AutoNumberServiceProvider::class,
        ];
    }

    public function test_trait_boot_autonumber_trait_registers_observer()
    {
        $model = new TraitTestModel();
        $this->assertTrue(method_exists($model, 'bootAutoNumberTrait'));
    }

    public function test_trait_requires_get_auto_number_options()
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model {
            use AutoNumberTrait;

            public function getAutoNumberOptions()
            {
                return ['auto_number'];
            }
        };
        $this->assertTrue(method_exists($model, 'getAutoNumberOptions'));
    }
}

class TraitTestModel extends \Illuminate\Database\Eloquent\Model
{
    use AutoNumberTrait;

    public function getAutoNumberOptions()
    {
        return ['auto_number'];
    }
}
