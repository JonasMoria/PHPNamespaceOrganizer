<?php

$baseDir = dirname(__DIR__); // raiz do projeto
$baseNamespace = 'Core';

// Adicione aqui diretorios onde o mapeamento deve ser ignorado
$ignoreDirs = ['vendor', 'node_modules'];

// Adicione aqui o caminho relativo das classes para serem mapeadas
$classes = [
    '',
];

// Utils
function normalizePath($path) {
    return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

function shouldIgnore($path, $ignoreDirs) {
    foreach ($ignoreDirs as $dir) {
        if (strpos($path, DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR) !== false) {
            return true;
        }
    }
    return false;
}

function pathToNamespace($relativePath, $baseNamespace) {
    $parts = explode(DIRECTORY_SEPARATOR, dirname($relativePath));
    $parts = array_map('ucfirst', $parts);
    return $baseNamespace . '\\' . implode('\\', $parts);
}

function fixClassNamespace($fullPath, $fqcnNamespace) {
    $code = file_get_contents($fullPath);
    $lines = explode("\n", $code);

    $hasNamespace = false;
    $phpLine = 0;
    foreach ($lines as $i => $line) {
        if (strpos(trim($line), '<?php') === 0) {
            $phpLine = $i;
        }
        if (preg_match('/^namespace\s+.*;/', trim($line))) {
            $lines[$i] = "namespace {$fqcnNamespace};";
            $hasNamespace = true;
            break;
        }
    }

    if (!$hasNamespace) {
        array_splice($lines, $phpLine + 1, 0, "namespace {$fqcnNamespace};");
    }

    file_put_contents($fullPath, implode("\n", $lines));
    echo "Namespace corrigido: {$fullPath}\n";
}

function isClassActuallyUsed($tokens, $className) {
    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i];
        if (!is_array($token)) {
            continue;
        }

        if (in_array($token[0], [T_NEW, T_INSTANCEOF, T_EXTENDS, T_IMPLEMENTS, T_USE])) {
            $j = $i + 1;
            while (isset($tokens[$j]) && is_array($tokens[$j])) {
                if ($tokens[$j][0] === T_STRING && $tokens[$j][1] === $className) {
                    return true;
                }
                if (!in_array($tokens[$j][0], [T_STRING, T_NS_SEPARATOR, T_WHITESPACE])) {
                    break;
                }
                $j++;
            }
        }

        if ($token[0] === T_STRING && $token[1] === $className) {
            if (isset($tokens[$i + 1]) && is_array($tokens[$i + 1]) && $tokens[$i + 1][0] === T_DOUBLE_COLON) {
                return true;
            }
        }
    }
    return false;
}

function addUseToFile($filePath, $fqcn, $className) {
    $code = file_get_contents($filePath);
    $tokens = token_get_all($code);
    $lines = explode("\n", $code);

    if (!isClassActuallyUsed($tokens, $className)) {
        return;
    }

    // Ignora se já houver use
    foreach ($lines as $line) {
        if (preg_match('/use\s+.*\\\\' . preg_quote($className, '/') . '\s*;/', $line)) {
            return;
        }
    }

    // Remove uses duplicados curtos (ex: use Login;)
    $lines = array_filter($lines, function ($line) use ($className) {
        return !preg_match('/^use\s+' . preg_quote($className, '/') . '\s*;/', $line);
    });

    $insertLine = 0;
    foreach ($lines as $i => $line) {
        if (preg_match('/^namespace\s+.*;/', trim($line))) {
            $insertLine = $i + 1;
            break;
        }
        if (strpos(trim($line), '<?php') === 0) {
            $insertLine = $i + 1;
        }
    }

    array_splice($lines, $insertLine, 0, 'use ' . $fqcn . ';');
    file_put_contents($filePath, implode("\n", $lines));
    echo "Use adicionado em: {$filePath}\n";
}

function updateUseStatementsForClass($baseDir, $fqcn, $className, $ignoreDirs, $targetFile) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
    foreach ($iterator as $file) {
        if (!$file->isFile() || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        if (realpath($path) === realpath($targetFile)) {
            continue;
        }
        if (shouldIgnore($path, $ignoreDirs)) {
            continue;
        }

        addUseToFile($path, $fqcn, $className);
    }
}

// ---------- EXECUÇÃO ----------
foreach ($classes as $classPath) {
    $relativePath = normalizePath($classPath . '.php');
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . $relativePath;

    if (!file_exists($fullPath)) {
        echo "⚠️  Classe não encontrada: $fullPath\n";
        continue;
    }

    $className = basename($classPath);
    $namespace = pathToNamespace($relativePath, $baseNamespace);
    $fqcn = $namespace . '\\' . $className;

    fixClassNamespace($fullPath, $namespace);
    updateUseStatementsForClass($baseDir, $fqcn, $className, $ignoreDirs, $fullPath);
}

echo "\nFinalizado com múltiplas classes.\n";
