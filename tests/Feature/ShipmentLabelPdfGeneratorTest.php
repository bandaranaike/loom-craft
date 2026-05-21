<?php

use App\Services\Fulfillment\ShipmentLabelPdfGenerator;
use Illuminate\Filesystem\Filesystem;

it('builds a writable chrome runtime configuration', function () {
    $runtimePath = sys_get_temp_dir().'/loom-craft-browsershot-'.uniqid();
    $filesystem = new Filesystem;

    app('config')->set('services.browsershot.runtime_path', $runtimePath);
    app('config')->set('services.browsershot.node_module_path', '');
    app('config')->set('services.browsershot.node_binary', '/usr/bin/node');

    $generator = new ShipmentLabelPdfGenerator;

    $runtimePathFromService = invokePrivateMethod($generator, 'runtimePath');
    $chromeUserDataDir = invokePrivateMethod($generator, 'chromeUserDataDir');
    $nodeBinary = invokePrivateMethod($generator, 'nodeBinary');
    $nodeModulePath = invokePrivateMethod($generator, 'nodeModulePath');
    $nodeEnvironment = invokePrivateMethod($generator, 'nodeEnvironment');

    expect($runtimePathFromService)->toBe($runtimePath);
    expect($chromeUserDataDir)->toBe($runtimePath.'/chrome-profile');
    expect($nodeBinary)->toBe('/usr/bin/node');
    expect($nodeModulePath)->toBe(base_path('node_modules'));
    expect($nodeEnvironment)->toBe([
        'HOME' => $runtimePath,
        'XDG_CACHE_HOME' => $runtimePath.'/cache',
        'XDG_CONFIG_HOME' => $runtimePath.'/config',
    ]);

    invokePrivateMethod($generator, 'ensureRuntimeDirectories');

    expect($filesystem->isDirectory($runtimePath))->toBeTrue();
    expect($filesystem->isDirectory($runtimePath.'/chrome-profile'))->toBeTrue();
    expect($filesystem->isDirectory($runtimePath.'/cache'))->toBeTrue();
    expect($filesystem->isDirectory($runtimePath.'/config'))->toBeTrue();

    $filesystem->deleteDirectory($runtimePath);
});

function invokePrivateMethod(object $object, string $method, array $arguments = []): mixed
{
    $reflection = new ReflectionClass($object);
    $reflectionMethod = $reflection->getMethod($method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invokeArgs($object, $arguments);
}
