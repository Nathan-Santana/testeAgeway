<?php
spl_autoload_register(function ($class) {
    // Caminho onde as classes do Adianti estão localizadas
    $prefix = 'Adianti\\'; // Prefixo das classes do Adianti
    $base_dir = __DIR__ . '/lib/adianti'; // Substitua pelo caminho correto

    // Verifique se a classe pertence ao namespace Adianti
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Remova o prefixo "Adianti\" e substitua as barras invertidas por barras normais
    $relative_class = substr($class, $len);
    $file = $base_dir . '/' . str_replace('\\', '/', $relative_class) . '.class.php';

    // Se o arquivo existir, inclua-o
    if (file_exists($file)) {
        require $file;
    }
});
