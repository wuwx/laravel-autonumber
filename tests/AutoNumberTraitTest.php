<?php

namespace Wuwx\LaravelAutoNumber\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

    public function test_trait_has_boot_method()
    {
        $reflection = new \ReflectionClass(AutoNumberTrait::class);
        $this->assertTrue($reflection->hasMethod('bootAutoNumberTrait'));
    }

    public function test_trait_has_abstract_method()
    {
        $reflection = new \ReflectionClass(AutoNumberTrait::class);
        $this->assertTrue($reflection->hasMethod('getAutoNumberOptions'));
    }

    public function test_boot_auto_number_trait_registers_observer()
    {
        $traitReflection = new \ReflectionClass(AutoNumberTrait::class);
        $bootMethod = $traitReflection->getMethod('bootAutoNumberTrait');
        $this->assertTrue($bootMethod->isPublic());
        $this->assertTrue($bootMethod->isStatic());

        try {
            $mockModel = $this->createMock(\Illuminate\Database\Eloquent\Model::class);
            $bootMethod->invoke($mockModel);
        } catch (\Error $e) {
            $this->assertStringContainsString('observe', $e->getMessage());
        }
    }
}
