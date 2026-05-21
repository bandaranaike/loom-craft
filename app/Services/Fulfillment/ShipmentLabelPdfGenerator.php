<?php

namespace App\Services\Fulfillment;

use Spatie\Browsershot\Browsershot;
use Symfony\Component\Process\ExecutableFinder;

class ShipmentLabelPdfGenerator
{
    /**
     * @param  array<string, mixed>  $label
     */
    public function generate(array $label): string
    {
        $html = view('fulfillment.shipment-label', [
            'label' => $label,
            'printMode' => 'pdf',
        ])->render();

        $this->ensureRuntimeDirectories();

        $browsershot = Browsershot::html($html)
            ->showBackground()
            ->margins(0, 0, 0, 0)
            ->paperSize(620, 756, 'px')
            ->windowSize(620, 756)
            ->timeout((int) config('services.browsershot.timeout', 60))
            ->setOption('preferCSSPageSize', true)
            ->setNodeEnv($this->nodeEnvironment())
            ->userDataDir($this->chromeUserDataDir())
            ->addChromiumArguments($this->chromiumArguments());

        $this->configureExecutablePaths($browsershot);

        if ((bool) config('services.browsershot.no_sandbox', true)) {
            $browsershot->noSandbox();
        }

        return $browsershot->pdf();
    }

    private function configureExecutablePaths(Browsershot $browsershot): void
    {
        $chromePath = $this->chromePath();
        $nodeBinary = $this->nodeBinary();
        $nodeModulePath = $this->nodeModulePath();

        if (is_string($chromePath) && $chromePath !== '') {
            $browsershot->setChromePath($chromePath);
        }

        if (is_string($nodeBinary) && $nodeBinary !== '') {
            $browsershot->setNodeBinary($nodeBinary);
        }

        if (is_string($nodeModulePath) && $nodeModulePath !== '') {
            $browsershot->setNodeModulePath($nodeModulePath);
        }
    }

    /**
     * Browsershot prefixes each Chromium argument with "--".
     *
     * @return list<string>
     */
    private function chromiumArguments(): array
    {
        return [
            'disable-gpu',
            'disable-dev-shm-usage',
            'disable-crash-reporter',
            'disable-breakpad',
            'no-zygote',
            'disable-setuid-sandbox',
            'disable-software-rasterizer',
            'disable-extensions',
            'font-render-hinting=none',
            'disable-features=VizDisplayCompositor',
            'remote-debugging-port=0',
        ];
    }

    private function ensureRuntimeDirectories(): void
    {
        foreach ([
            $this->runtimePath(),
            $this->chromeUserDataDir(),
            $this->runtimePath().'/cache',
            $this->runtimePath().'/config',
        ] as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
        }
    }

    private function nodeEnvironment(): array
    {
        return [
            'HOME' => $this->runtimePath(),
            'XDG_CACHE_HOME' => $this->runtimePath().'/cache',
            'XDG_CONFIG_HOME' => $this->runtimePath().'/config',
        ];
    }

    private function runtimePath(): string
    {
        $configuredPath = config('services.browsershot.runtime_path');

        if (is_string($configuredPath) && $configuredPath !== '') {
            return $configuredPath;
        }

        return storage_path('app/browsershot/runtime');
    }

    private function nodeBinary(): string
    {
        $configuredPath = config('services.browsershot.node_binary');

        if (is_string($configuredPath) && $configuredPath !== '') {
            return $configuredPath;
        }

        $finder = new ExecutableFinder;

        foreach (['node', 'nodejs', '/usr/bin/node', '/usr/local/bin/node', '/opt/homebrew/bin/node'] as $binary) {
            if ($path = $finder->find($binary)) {
                return $path;
            }

            if (is_string($binary) && str_starts_with($binary, '/')) {
                if (is_file($binary) && is_executable($binary)) {
                    return $binary;
                }
            }
        }

        throw new \RuntimeException('Unable to locate a Node.js binary for Browsershot. Set BROWSERSHOT_NODE_BINARY to an absolute path.');
    }

    private function nodeModulePath(): string
    {
        $configuredPath = config('services.browsershot.node_module_path');

        if (is_string($configuredPath) && $configuredPath !== '') {
            return $configuredPath;
        }

        return base_path('node_modules');
    }

    private function chromeUserDataDir(): string
    {
        return $this->runtimePath().'/chrome-profile';
    }

    private function chromePath(): ?string
    {
        $configuredPath = config('services.browsershot.chrome_path');

        if (is_string($configuredPath) && $configuredPath !== '' && is_executable($configuredPath)) {
            return $configuredPath;
        }

        $storageMatches = array_values(array_filter(
            glob(storage_path('app/browsershot/chrome/*/chrome-linux64/chrome')) ?: [],
            static fn (string $path): bool => is_executable($path),
        ));

        rsort($storageMatches);

        if (isset($storageMatches[0])) {
            return $storageMatches[0];
        }

        $homePath = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? null;

        if (! is_string($homePath) || $homePath === '') {
            return null;
        }

        $matches = array_values(array_filter(
            glob($homePath.'/.cache/puppeteer/chrome/*/chrome-linux64/chrome') ?: [],
            static fn (string $path): bool => is_executable($path),
        ));

        rsort($matches);

        return $matches[0] ?? null;
    }
}
