<?php

declare(strict_types=1);

namespace Invoice;

/**
 * Hlavná aplikácia
 */
final class Application
{
    private string $version = '0.1.0';

    /**
     * Spustenie CLI aplikácie
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

        // --- jednoduchý router na príkazy ---
        // Očakávame: generate <input.json> [--template=path] [--html-out=path]
        $command = $argv[1] ?? null;
        if ($command === 'generate') {
            return $this->handleGenerate(array_slice($argv, 2));
        }

        // Pomocník (help)
        if ($command === 'help' || in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
            $this->printHelp($script);
            return 0;
        }

        // Default info obrazovka
        $this->println('Generator faktur pripraveny');
        $this->println('Pouzitie: composer invoice <input.json> <output.pdf> [--options]');
        $this->println('Pouzitie: php bin/invoice.php <input.json> <output.pdf> [--options]');
        $this->println('');
        $this->println('Prikazy:');
        $this->println('  generate <input.json> [--template=templates/invoice-test.php] [--html-out=out.html]');
        $this->println('  --version | -v');
        $this->println('  --help | -h | help');
        $this->println('');
        $this->println('Tip: php bin/invoice.php generate examples/invoice-sample.json --html-out=out.html');
        return 0;
    }

    /**
     * Príkaz: generate – načíta JSON a vyrenderuje HTML zo šablóny (bez PDF).
     * 
     * @param array<int, string> $args
     * @return int
     */
    private function handleGenerate(array $args): int
    {
        // Parsovanie argumentov: prvý povinný je input.json
        $inputPath = $args[0] ?? null;
        if (!$inputPath) {
            $this->eprintln('Chyba: chyba cesta k input.json');
            $this->eprintln('Pouzitie: php bin/invoice.php generate <input.json> [--template=templates/invoice-test.php] [--html-out=out.html]');
            return 1;
        }

        // Volitelné prepínače
        $templatePath = $this->getOptionValue($args, '--template') ?? 'templates/invoice-test.php';
        $htmlOutPath  = $this->getOptionValue($args, '--html-out'); // ak nie je, ideme na STDOUT

        // 1) Načítaj JSON
        $data = $this->loadJson($inputPath);
        if ($data === null) {
            return 2;
        }

        // 2) Render šablóny na string
        $html = $this->renderTemplate($templatePath, ['data' => $data]);
        if ($html === null) {
            return 3;
        }

        // 3) Výstup buď do súboru, alebo na STDOUT
        if ($htmlOutPath) {
            $ok = @file_put_contents($htmlOutPath, $html);
            if ($ok === false) {
                $this->eprintln("Chyba: nepodarilo sa zapisat HTML do suboru: {$htmlOutPath}");
                return 4;
            }
            $this->println("OK: HTML zapisane do {$htmlOutPath}");
        } else {
            // Priamo na STDOUT
            $this->println($html);
        }

        return 0;
    }

    /**
     * Pomocný vytlačený help text
     * 
     * @param string $script
     * @return void
     */
    private function printHelp(string $script): void
    {
        $this->println("Pouzitie:");
        $this->println("  php bin/invoice.php generate <input.json> [--template=templates/invoice-test.php] [--html-out=out.html]");
        $this->println("  composer invoice -- generate <input.json> [--template=...] [--html-out=...]");
        $this->println("");
        $this->println("Priklady:");
        $this->println("  php bin/invoice.php generate examples/invoice-sample.json");
        $this->println("  php bin/invoice.php generate examples/invoice-sample.json --html-out=out.html");
        $this->println("  composer invoice -- generate examples/invoice-sample.json --template=templates/invoice-test.php --html-out=out.html");
        $this->println("");
        $this->println("Info:");
        $this->println("  --version, -v   Zobrazi verziu");
        $this->println("  --help, -h      Zobrazi tuto napovedu");
    }

    /**
     * Vytlací riadok na STDOUT
     * 
     * @param string $message
     * @return void
     */
    private function println(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    /**
     * Vytlací riadok na STDERR
     * 
     * @param string $message
     * @return void
     */
    private function eprintln(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);
    }

    /**
     * Načíta a dekóduje JSON súbor na asociatívne pole.
     * 
     * @param string $path
     * @return array<string, mixed>|null
     */
    private function loadJson(string $path): ?array
    {
        if (!is_file($path)) {
            $this->eprintln("Chyba: subor neexistuje: {$path}");
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            $this->eprintln("Chyba: nepodarilo sa nacitat subor: {$path}");
            return null;
        }
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->eprintln('Chyba: neplatny JSON: ' . json_last_error_msg());
            return null;
        }
        return $data;
    }

    /**
     * Bezpečne vyrenderuje PHP šablonu do stringu.
     * Odovzdá premenné cez extrakciu (len whitelist keys).
     * 
     * @param string $templatePath
     * @param array<string, mixed> $vars
     * @return string|null
     */
    private function renderTemplate(string $templatePath, array $vars): ?string
    {
        if (!is_file($templatePath)) {
            $this->eprintln("Chyba: sablona neexistuje: {$templatePath}");
            return null;
        }

        // whitelist: povolíme len kľúče, ktoré očakávame
        $allowed = ['data'];
        $safe = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $vars)) {
                $safe[$k] = $vars[$k];
            }
        }

        // zachytenie výstupu šablony
        ob_start();
        try {
            /** @var array $data */
            $data = $safe['data'] ?? [];
            require $templatePath;
        } catch (\Throwable $e) {
            ob_end_clean();
            $this->eprintln('Chyba pri renderovani sablony: ' . $e->getMessage());
            return null;
        }
        return (string)ob_get_clean();
    }

    /**
     * Získa hodnotu z voliteľného prepínača v tvare --key=value
     * 
     * @param array<int, string> $args
     * @param string $key
     * @return string|null
     */
    private function getOptionValue(array $args, string $key): ?string
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, $key . '=')) {
                return substr($arg, strlen($key) + 1);
            }
        }
        return null;
    }
}
