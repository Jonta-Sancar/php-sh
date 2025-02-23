<?php
echo "1. Iniciar Servidor\n";
echo "2. Encerrar Servidor\n";

echo "\nO que deseja fazer?\n";

$set_dir_command = "-dir";
$set_force_command = "-force";
$set_break_command = "-break";

$action = !in_array($set_dir_command, $argv) ? 
    !in_array($set_break_command, $argv) ? readline('') : '2'
: '1';

$default_dir = "C:/xampp/htdocs";

$command_line = "php C:/xampp/htdocs/run_server.php " . implode(' ', $argv);

switch ($action) {
    case '1':
        echo "\nQual o diretório root do servidor (default=C:/xampp/htdocs)?\n";

        $arg_dir = '';
        if(in_array($set_dir_command, $argv)){
            $index_command = array_search($set_dir_command, $argv);
            $arg_dir = isset($argv[$index_command+1]) ? $argv[$index_command+1] : $default_dir;
        }

        $dir = empty($arg_dir) ? readline('') : $arg_dir;

        // Se o usuário não digitar nada, define um valor padrão
        $dir = empty($dir) ? $default_dir : $dir;
        startServer($dir);
        break;

    case '2':
        stopServer();
        break;

    default:
        echo "Opção inválida.\n";
        break;
}

/**
 * Inicia o servidor PHP e o MySQL em segundo plano, sem janelas do CMD.
 */
function startServer($dir){
    global $argv, $set_force_command;
    if(!in_array($set_force_command, $argv)){
        stopServer(true);
    }
    // Cria um arquivo VBScript temporário
    $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'run_php_hidden.vbs';

    // VBScript que inicia MySQL e servidor PHP em segundo plano
    // Ajuste o caminho de mysqld.exe se necessário
    $vbscript = <<<EOT
        Set WshShell = CreateObject("WScript.Shell")
        ' Inicia MySQL
        WshShell.Run "C:/xampp/mysql/bin/mysqld.exe", 0, False

        ' Inicia servidor PHP
        WshShell.Run "php -S localhost:80 -t $dir C:/xampp/htdocs/server.php", 0, False
    EOT;

    // Grava o VBScript
    if (file_put_contents($tempFile, $vbscript) === false) {
        die("Erro ao criar o arquivo VBScript.");
    }

    // Executa o VBScript sem exibir janela
    exec('wscript //nologo "' . $tempFile . '"');

    // Remove o arquivo temporário
    unlink($tempFile);

    echo "Servidor PHP e MySQL iniciados em segundo plano.\n";
    echo "(http://localhost:80) - SERVER ROOT: $dir\n";
}

/**
 * Encerra o servidor PHP e o MySQL usando 'taskkill' (apenas Windows).
 */
function stopServer($start = false){
    taskKill('mysqld.exe');

    taskKill('php.exe');

    echo "Servidor PHP e MySQL foram encerrados.\n";
}

function taskKill($task){
    global $command_line, $set_force_command, $set_break_command, $argv;

    $processo  = shell_exec('tasklist /FI "IMAGENAME eq '.$task.'"');
    $processos_iniciais = returnsProcessos($processo);

    if (strpos($processo, $task) !== false) {
        if($task == "php.exe" && !in_array($set_break_command, $argv)){
            shell_exec($command_line . ' ' . $set_force_command);

            $processo  = shell_exec('tasklist /FI "IMAGENAME eq '.$task.'"');
            $processo_a_manter = getNewProcess($processos_iniciais, returnsProcessos($processo));
            
            foreach($processos_iniciais as $processo){
                $pid = explode('.id:', $processo)[1];

                if(!in_array($processo, $processo_a_manter)){
                    shell_exec('taskkill /F /PID ' . $pid);
                }
            }
        } else {
            shell_exec('taskkill /F /IM ' . $task);
        }
        echo "\nProcesso '$task' encerrado\n";
        return;
    }

    echo "\nProcesso '$task' já não está ativo\n";
}

function returnsProcessos($processo){
    $processos = explode("\n", $processo);
    unset($processos[count($processos)-1], $processos[0], $processos[1], $processos[2]);

    $processos = array_map(function($v){
        while(strpos($v, '  ')){
            $v = str_replace('  ', ' ', $v);
        }
        $v = explode(' ', $v);

        return $v[0] . '.id:' . $v[1];
    }, $processos);

    return array_values($processos);
}


function getNewProcess($processos_iniciais, $processos_totais){
    foreach($processos_totais as $k => $processo){
        $pid = explode('.id:', $processo)[1];
        if(in_array($processo, $processos_iniciais) && $pid != getmypid()){
            unset($processos_totais[$k]);
        }
    }

    return $processos_totais;
}