<?php

// Adicione aqui o diretorio raiz onde as classes serão mapeadas
$baseDir = dirname(__DIR__) . '/Core';

// Adicione aqui diretorios onde o mapeamento deve ser ignorado
$ignoreDirs = ['vendor', 'node_modules'];

$totalClasses = 0;
$namespacedClasses = 0;
$nonNamespacedClasses = 0;

function shouldIgnore($path, $ignoreDirs) {
    foreach ($ignoreDirs as $dir) {
        if (strpos($path, DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR) !== false) {
            return true;
        }
    }
    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
        continue;
    }

    $filePath = $file->getPathname();
    if (shouldIgnore($filePath, $ignoreDirs)) {
        continue;
    }

    $code = file_get_contents($filePath);
    $tokens = token_get_all($code);

    $hasNamespace = false;
    $hasClass = false;

    foreach ($tokens as $token) {
        if (is_array($token)) {
            if ($token[0] === T_NAMESPACE) {
                $hasNamespace = true;
            }
            if (in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT])) {
                $hasClass = true;
            }
        }
    }

    if ($hasClass) {
        $totalClasses++;
        if ($hasNamespace) {
            $namespacedClasses++;
        } else {
            $nonNamespacedClasses++;
        }
    }
}

// Exibe resultados
echo "Estatísticas de Classes:\n";
echo "---------------------------\n";
echo "Total de classes encontradas:       $totalClasses\n";
echo "Com namespace declarado:            $namespacedClasses\n";
echo "Sem namespace declarado:            $nonNamespacedClasses\n";
