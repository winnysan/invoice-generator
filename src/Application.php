<?php

declare(strict_types=1);

namespace Invoice;

/**
 * Hlavna aplikacia
 */
final class Application
{
    private string $version = '0.1.0';

    /**
     * Spustenie CLI aplikacie
     * 
     * @param array<int, string> $argv
     * @return int
     */
    public function run(array $argv): int
    {
        $script = $argv[0] ?? 'invoice';

        if (in_array('--version', $argv, true) || in_array('-v', $argv, true)) {
            $this->println("{$script} v{$this->version}");
            return 0;
        }

        $this->println('Generator faktur pripraveny');
        $this->println('Pouzitie: composer invoice <input.json> <output.pdf> [--options]');
        $this->println('Pouzitie: php bin/invoice.php <input.json> <output.pdf> [--options]');
        $this->println('Tip: composer invoice -- --version');
        return 0;
    }

    /**
     * Vytlaci riadok na STDOUT
     * 
     * @param string $message
     * @return void
     */
    private function println(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}
