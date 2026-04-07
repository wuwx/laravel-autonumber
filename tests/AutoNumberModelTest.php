<?php

namespace Wuwx\LaravelAutoNumber\Tests;

use Orchestra\Testbench\TestCase;
use Wuwx\LaravelAutoNumber\AutoNumberServiceProvider;
use Wuwx\LaravelAutoNumber\Models\AutoNumber;

class AutoNumberModelTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            AutoNumberServiceProvider::class,
        ];
    }

    public function test_model_fillable_attributes()
    {
        $model = new AutoNumber();
        $expectedFillable = ['name', 'number'];
        $this->assertEquals($expectedFillable, $model->getFillable());
    }

    public function test_model_can_be_instantiated()
    {
        $model = new AutoNumber();
        $this->assertInstanceOf(AutoNumber::class, $model);
    }

    public function test_model_can_set_and_get_attributes()
    {
        $model = new AutoNumber();
        $model->name = 'test-name';
        $model->number = 123;
        $this->assertEquals('test-name', $model->name);
        $this->assertEquals(123, $model->number);
    }
}
