<?php

namespace Wuwx\LaravelAutoNumber\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Wuwx\LaravelAutoNumber\AutoNumber;
use Wuwx\LaravelAutoNumber\AutoNumberServiceProvider;
use Wuwx\LaravelAutoNumber\Observers\AutoNumberObserver;

class AutoNumberObserverTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            AutoNumberServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('autonumber', [
            'format' => '?',
            'length' => 4,
            'onUpdate' => false,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('auto_numbers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32);
            $table->integer('number');
            $table->timestamps();
        });
    }

    public function test_observer_construction()
    {
        $autoNumber = new AutoNumber();
        $observer = new AutoNumberObserver($autoNumber);
        $this->assertInstanceOf(AutoNumberObserver::class, $observer);
    }

    public function test_observer_saving_on_new_model()
    {
        $autoNumber = new AutoNumber();
        $observer = new AutoNumberObserver($autoNumber);
        $model = new ObserverTestModel();
        $model->exists = false;

        $result = $observer->saving($model);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $model->auto_number);
    }

    public function test_observer_saving_on_existing_model_without_on_update()
    {
        $autoNumber = new AutoNumber();
        $observer = new AutoNumberObserver($autoNumber);
        $model = new ObserverTestModel();
        $model->exists = true;
        $model->auto_number = '0001';

        $result = $observer->saving($model);
        $this->assertNull($result);
        $this->assertEquals('0001', $model->auto_number);
    }

    public function test_observer_saving_on_existing_model_with_on_update_enabled()
    {
        $this->app['config']->set('autonumber.onUpdate', true);
        $autoNumber = new AutoNumber();
        $observer = new AutoNumberObserver($autoNumber);
        $model = new ObserverTestModel();
        $model->exists = true;
        $model->auto_number = '0001';

        $result = $observer->saving($model);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $model->auto_number);
    }
}

class ObserverTestModel extends \Illuminate\Database\Eloquent\Model
{
    public $table = 'test_models';
    public $timestamps = false;

    public function getAutoNumberOptions()
    {
        return ['auto_number'];
    }
}
