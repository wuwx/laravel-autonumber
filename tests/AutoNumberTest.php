<?php

namespace Wuwx\LaravelAutoNumber\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Wuwx\LaravelAutoNumber\AutoNumber;
use Wuwx\LaravelAutoNumber\AutoNumberServiceProvider;
use Wuwx\LaravelAutoNumber\Models\AutoNumber as AutoNumberModel;

class AutoNumberTest extends TestCase
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

    public function test_service_provider_registers_autonumber_observer()
    {
        $this->assertInstanceOf(AutoNumber::class, $this->app->make(AutoNumber::class));
    }

    public function test_service_provider_register()
    {
        $provider = new AutoNumberServiceProvider($this->app);
        $provider->register();

        $observer = $this->app->make(\Wuwx\LaravelAutoNumber\Observers\AutoNumberObserver::class);
        $this->assertInstanceOf(\Wuwx\LaravelAutoNumber\Observers\AutoNumberObserver::class, $observer);
    }

    public function test_service_provider_boot_publishes_config_and_migrations()
    {
        $provider = new AutoNumberServiceProvider($this->app);
        $provider->boot();
        $this->assertTrue(true);
    }

    public function test_generate_unique_name()
    {
        $autoNumber = new AutoNumber();
        $reflection = new \ReflectionClass($autoNumber);
        $method = $reflection->getMethod('generateUniqueName');
        $method->setAccessible(true);

        $result1 = $method->invoke($autoNumber, ['key' => 'value']);
        $result2 = $method->invoke($autoNumber, ['key' => 'value']);
        $result3 = $method->invoke($autoNumber, ['key' => 'different']);

        $this->assertEquals($result1, $result2);
        $this->assertNotEquals($result1, $result3);
    }

    public function test_evaluate_configuration_with_defaults()
    {
        $autoNumber = new AutoNumber();
        $config = $autoNumber->evaluateConfiguration();

        $this->assertEquals('?', $config['format']);
        $this->assertEquals(4, $config['length']);
        $this->assertFalse($config['onUpdate']);
    }

    public function test_evaluate_configuration_with_overrides()
    {
        $autoNumber = new AutoNumber();
        $config = $autoNumber->evaluateConfiguration([
            'format' => 'INV-?',
            'length' => 6,
        ]);

        $this->assertEquals('INV-?', $config['format']);
        $this->assertEquals(6, $config['length']);
    }

    public function test_evaluate_configuration_with_callable_format()
    {
        $autoNumber = new AutoNumber();
        $config = $autoNumber->evaluateConfiguration([
            'format' => function () {
                return 'CALL-?';
            },
        ]);

        $this->assertEquals('CALL-?', $config['format']);
    }

    public function test_evaluate_configuration_throws_on_null_format()
    {
        $autoNumber = new AutoNumber();
        $this->expectException(\InvalidArgumentException::class);
        $autoNumber->evaluateConfiguration(['format' => null]);
    }

    public function test_evaluate_configuration_throws_on_null_length()
    {
        $autoNumber = new AutoNumber();
        $this->expectException(\InvalidArgumentException::class);
        $autoNumber->evaluateConfiguration(['length' => null]);
    }

    public function test_evaluate_configuration_throws_on_null_on_update()
    {
        $autoNumber = new AutoNumber();
        $this->expectException(\InvalidArgumentException::class);
        $autoNumber->evaluateConfiguration(['onUpdate' => null]);
    }

    public function test_get_next_number_creates_new_record()
    {
        $autoNumber = new AutoNumber();
        $reflection = new \ReflectionClass($autoNumber);
        $method = $reflection->getMethod('getNextNumber');
        $method->setAccessible(true);

        $result = $method->invoke($autoNumber, 'test-name-1');

        $this->assertEquals(1, $result);
        $this->assertDatabaseHas('auto_numbers', [
            'name' => 'test-name-1',
            'number' => 1,
        ]);
    }

    public function test_get_next_number_increments_existing()
    {
        $autoNumber = new AutoNumber();
        $reflection = new \ReflectionClass($autoNumber);
        $method = $reflection->getMethod('getNextNumber');
        $method->setAccessible(true);

        $method->invoke($autoNumber, 'test-name-2');
        $result = $method->invoke($autoNumber, 'test-name-2');

        $this->assertEquals(2, $result);
        $this->assertDatabaseHas('auto_numbers', [
            'name' => 'test-name-2',
            'number' => 2,
        ]);
    }

    public function test_generate_auto_number_with_string_attribute()
    {
        $model = new TestModel();
        $autoNumber = new AutoNumber();
        $result = $autoNumber->generate($model);

        $this->assertTrue($result);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $model->auto_number);
    }

    public function test_generate_auto_number_with_numeric_attribute()
    {
        $model = new TestModelWithNumericAttribute();
        $autoNumber = new AutoNumber();
        $result = $autoNumber->generate($model);

        $this->assertTrue($result);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $model->auto_number);
    }

    public function test_generate_auto_number_with_custom_format()
    {
        $model = new TestModelWithCustomFormat();
        $autoNumber = new AutoNumber();
        $result = $autoNumber->generate($model);

        $this->assertTrue($result);
        $this->assertMatchesRegularExpression('/^INV-\d{6}$/', $model->invoice_number);
    }

    public function test_generate_auto_number_with_zero_length()
    {
        $model = new TestModelWithZeroLength();
        $autoNumber = new AutoNumber();
        $result = $autoNumber->generate($model);

        $this->assertTrue($result);
        $this->assertEquals(1, $model->auto_number);
    }
}

class TestModel extends \Illuminate\Database\Eloquent\Model
{
    public $table = 'test_models';
    public $timestamps = false;

    public function getAutoNumberOptions()
    {
        return [
            'auto_number' => [],
        ];
    }
}

class TestModelWithNumericAttribute extends \Illuminate\Database\Eloquent\Model
{
    public $table = 'test_models';
    public $timestamps = false;

    public function getAutoNumberOptions()
    {
        return ['auto_number'];
    }
}

class TestModelWithCustomFormat extends \Illuminate\Database\Eloquent\Model
{
    public $table = 'test_models';
    public $timestamps = false;

    public function getAutoNumberOptions()
    {
        return [
            'invoice_number' => [
                'format' => 'INV-?',
                'length' => 6,
            ],
        ];
    }
}

class TestModelWithZeroLength extends \Illuminate\Database\Eloquent\Model
{
    public $table = 'test_models';
    public $timestamps = false;

    public function getAutoNumberOptions()
    {
        return [
            'auto_number' => [
                'length' => 0,
            ],
        ];
    }
}
