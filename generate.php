<?php

/**
 * Generates ~300 PHP classes with deliberately untyped parameters
 * to produce enough PHPStan type-coverage errors for parallel fork processing.
 */

$baseDir = __DIR__ . '/src';

$modules = ['Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta', 'Iota', 'Kappa'];

$classesPerModule = 30;

foreach ($modules as $module) {
    $dir = "$baseDir/$module";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    for ($i = 1; $i <= $classesPerModule; $i++) {
        $className = "Service{$i}";
        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\{$module};\n\nclass {$className}\n{\n";

        // Add several methods with untyped params to generate type-coverage errors
        for ($m = 1; $m <= 8; $m++) {
            $params = [];
            for ($p = 1; $p <= 3; $p++) {
                $params[] = "\$param{$p}";  // deliberately untyped
            }
            $paramStr = implode(', ', $params);
            $content .= "    public function method{$m}({$paramStr})\n    {\n        return \$param1;\n    }\n\n";
        }

        // Add some untyped properties
        for ($prop = 1; $prop <= 4; $prop++) {
            $content .= "    public \$property{$prop};\n\n";
        }

        // Add constants without type (PHP 8.3+ supports typed constants)
        for ($c = 1; $c <= 3; $c++) {
            $content .= "    public const CONST_{$c} = 'value_{$c}';\n\n";
        }

        $content .= "}\n";

        file_put_contents("$dir/{$className}.php", $content);
    }

    echo "Generated $classesPerModule classes in App\\$module\n";
}

$total = count($modules) * $classesPerModule;
echo "\nTotal: $total classes generated in src/\n";
echo "Run: ./vendor/bin/pest --type-coverage --compact --min=50\n";
