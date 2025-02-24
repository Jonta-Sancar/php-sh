<?php
require_once __DIR__ . '/auxiliary/aux_functions.php';
require_once __DIR__ . '/auxiliary/process_handler.php';

$FLAGS = [
    '--break' => [
        'content' => true,
        'description' => "breaks all or specific process"
    ],
    '--host' => [
        'content' => true,
        'description' => "sets the server host"
    ],
    '--port' => [
        'content' => true,
        'description' => "sets the server port"
    ],
    '--version' => [
        'content' => false,
        'description' => "shows package version, name, description and news"
    ],
    '--help' => [
        'content' => false,
        'description' => "show all flags and flags description"
    ],
    '--force' => [
        'content' => false,
        'description' => "starts the server, case the server port  is already in use it will be killed and restarted with new parameters"
    ],
    '--list' => [
        'content' => false,
        'description' => "shows all active servers"
    ],
    '--dir' => [
        'content' => true,
        'description' => "sets the current directory"
    ]
];

$SIMPLE_FLAGS = [
    '-b',
    '-ht',
    '-p',
    '-v',
    '-h',
    '-f',
    '-l',
    '-d'
];

$parammeters = json_decode(file_get_contents(__DIR__ . '/php-sh.json'), true);

$command_line = "php " . implode(' ', $argv);


adjustParammeters();

$PACKAGE_DIR = __DIR__;
$PACKAGE_INFO  = json_decode(file_get_contents(__DIR__ . '/data.json'), true);

function run(){
    global $argv, $FLAGS;
    
    if(isset($argv[array_keys($FLAGS)[0]])){
        stopServer();
    } else if(isset($argv[array_keys($FLAGS)[6]])){
        listServers();
    } else if (isset($argv[array_keys($FLAGS)[3]])){
        showVersion();
    } else if (isset($argv[array_keys($FLAGS)[4]])){
        showHelp();
    } else {
        startServer();
    }
}run();

/**
    * Encerra o servidor PHP em segundo plano.
    *
    * Se o parâmetro opcional $start for true, o servidor PHP   iniciado em
    * segundo plano antes de ser encerrado.
    *
    * @param bool $start Se true, o servidor PHP   iniciado em segundo plano
    *                    antes de ser encerrado.
    *
    * @return void
*/
function stopServer(){
    global $parammeters, $argv, $FLAGS;
    echo "Verificando se há processos para encerrar.\n";

    stopProcess('php.exe', isset($argv[array_keys($FLAGS)[0]]), $parammeters['port']);
}

/**
   * Inicia o servidor PHP em segundo plano (apenas Windows) com o seguinte comando:
   *
   *   php -S <host>:<port> -t <dir> <server.php>
   *
   * Onde:
   *
   *   <host> é o host do servidor (default=localhost);
   *   <port> é a porta do servidor (default=80);
   *   <dir> é o diretório raiz do servidor (default=dir definido no arquivo php-sh.json);
   *   <server.php> é o arquivo server.php localizado na pasta do php-sh.
   *
   * Se o servidor j  estiver em execu o, o mesmo   encerrado antes de iniciar o novo.
   *
   * @return void
 */
function startServer(){
    global $parammeters, $PACKAGE_INFO;
    $PARAMS_DIR  = $parammeters['dir'];
    $PARAMS_HOST = $parammeters['host'];
    $PARAMS_PORT = $parammeters['port'];

    echo $PACKAGE_INFO['name'] . "\n";

    stopServer();
    
    startProcess($PARAMS_HOST, $PARAMS_PORT, $PARAMS_DIR);

    echo "\n\nServidor PHP iniciado em segundo plano.\n";
    echo "(http://$PARAMS_HOST:$PARAMS_PORT) - SERVER ROOT: $PARAMS_DIR\n";
}

function listServers(){
    $processos = returnsProcessos('php.exe');
    showProcess($processos);
    echo "\n";
}

function showVersion(){
    global $PACKAGE_INFO;
    echo $PACKAGE_INFO['version'] . "\n";
    echo strtoupper($PACKAGE_INFO['name']) . "\n";
    echo "description:\n" . $PACKAGE_INFO['description'] . "\n";
    echo "\nnews:\n" . $PACKAGE_INFO['news'] . "\n\n";
}

function showHelp(){
    global $FLAGS, $SIMPLE_FLAGS;
    $max_flag_length = 10; // for adjust the help format
    $max_simple_flag_length = 6; // for adjust the help format

    $FLAGS = ['Flag' => ['description' => "Description\n"], ...$FLAGS];
    $SIMPLE_FLAGS = [
        'Simple',
        ...$SIMPLE_FLAGS
    ];

    $flags = array_keys($FLAGS);
    foreach($FLAGS as $flag => $value){
        $simple_flag = $SIMPLE_FLAGS[array_search($flag, $flags)];

        $flag = str_pad($flag, $max_flag_length);
        $simple_flag = str_pad($simple_flag, $max_simple_flag_length);
        echo "$flag or $simple_flag - $value[description]\n";
    }
}