<?php
// Monta o caminho físico da requisição
$DOCUMENT_ROOT = str_replace("\\", '/', $_SERVER['DOCUMENT_ROOT']);
$REQUEST_URI   = explode('?', $_SERVER['REQUEST_URI'])[0];
$request = $DOCUMENT_ROOT . $REQUEST_URI;

// Se for um arquivo existente ou se for um diretório com 'index.php', deixa o servidor embutido do PHP servir.
if (is_file($request) || is_file(rtrim($request, '/') . '/index.html') || is_file(rtrim($request, '/') . '/index.php')) {
    // Retorna 'false' para o servidor embutido processar normalmente
    return false;
}

// Se chegou aqui, não encontramos o arquivo/diretório requisitado.
echo "Não foi possível completar a requisição " . $_SERVER['REQUEST_METHOD'] . " " . $REQUEST_URI . "<br>";
echo "Arquivo não encontrado.<br><br>";

// 1. Corrige a $request para encontrar o último diretório existente
$request_dir = rtrim($request, '/');
while (!is_dir($request_dir) && strlen($request_dir) > 1) {
    $request_dir = dirname($request_dir);
}

// 2. Lista o conteúdo do diretório, se ele existir
if (is_dir($request_dir)) {
    echo "<strong>Conteúdo do diretório: {$request_dir}</strong><br>";
    $dir_contents = scandir($request_dir);

    echo "<ul>";
    foreach ($dir_contents as $content_name) {
        // Ignora arquivos/diretórios ocultos (que começam com '.')
        if (substr($content_name, 0, 1) === '.') {
            continue;
        }

        // Monta o caminho completo para o item
        $fullPath = $request_dir . '/' . $content_name;

        // Remove o DOCUMENT_ROOT para criar um link relativo
        $link = str_replace($DOCUMENT_ROOT, '', $fullPath);

        echo "<li><a href=\"{$link}\">{$content_name}</a></li>";
    }
    echo "</ul>";
} else {
    // Caso nenhum diretório seja encontrado
    echo "Não foi possível encontrar um diretório válido para listar o conteúdo.";
}
