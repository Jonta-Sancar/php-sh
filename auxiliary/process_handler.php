<?php

function startProcess($HOST, $PORT, $DIR){
  global $PACKAGE_DIR;
  $command_line = "php -S $HOST:$PORT -t $DIR $PACKAGE_DIR/server.php";

  $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'run_php_hidden.vbs';

  $vbscript = <<<EOT
      Set WshShell = CreateObject("WScript.Shell")
      ' Inicia servidor PHP
      WshShell.Run "$command_line", 0, False
  EOT;

  if (file_put_contents($tempFile, $vbscript) === false) {
    die("Erro ao criar o arquivo VBScript.");
  }

  exec('wscript //nologo "' . $tempFile . '"');

  unlink($tempFile);
}

function stopProcess($task, $is_break, $port_to_use){
  global $FLAGS, $argv, $parammeters;

  $processos  = returnsProcessos($task, $is_break, $port_to_use);

  if (count($processos) >= 1) {
    $ports =  isset($argv[array_keys($FLAGS)[5]]) ? $parammeters['port'] : choseHowToWorskWithActiveProcess($processos);
    if($ports === true) {
      echo "\nEncerrando todos os processos\n";
      shellExecKill('NAME', $task);
      return;
    }
    
    foreach($processos as $processo){
      $is_port_1 = $processo['port'] == $ports;
      $is_port_2 = is_array($ports) && in_array($processo['port'], $ports);

      if(!$is_port_1 && !$is_port_2){
        continue;
      }

      $pid = $processo['pid'];

      shellExecKill('PID', $pid);
    }
    return;
  }
}

function returnsProcessos($task){
  global $FLAGS, $argv, $parammeters;
  $process = shell_exec('tasklist /FI "IMAGENAME eq '.$task.'" /NH /FO CSV');
  $processes = [];
  $lines = explode("\n", trim($process));
  foreach ($lines as $line) {
    $parts = str_getcsv($line);
    if (isset($parts[1])) {
      $processes[trim($parts[1], '"')] = trim($parts[0], '"');
    }
  }

  $netstat = shell_exec('netstat -ano');
  $netstatLines = explode("\n", trim($netstat));
  $phpProcesses = [];
  foreach ($netstatLines as $line) {
    if (strpos($line, 'LISTENING') !== false) {
      $parts = preg_split('/\s+/', trim($line));
      $localAddress = $parts[1];
      $pid = end($parts);

      if (preg_match('/:(\d+)$/', $localAddress, $matches)) {
        $port = $matches[1];

        if(in_array($pid, array_keys($processes))){
          $phpProcesses[] = [
            'pid' => $pid,
            'name' => $processes[$pid],
            'port' => $port
          ];
        }
      }
    }
  }

  foreach($phpProcesses as $index => $phpProcess){
    $processes[$phpProcess['pid']] = $phpProcess['name'];
    $is_break = isset($argv[array_keys($FLAGS)[0]]);
    $is_list  = isset($argv[array_keys($FLAGS)[6]]);
    if($is_list){
      break;
    }
    
    $condition_1 = !$is_break && ($port == @$argv['--port'] || $port == $parammeters['port']);
    $condition_2 = $is_break && $port == @$argv['--port'];
    $condition_3 = $is_break && @$argv['--port'] === null;
    
    $final_condition = $condition_1 || $condition_2 || $condition_3;
    if(!$final_condition){
      unset($processes[$index]);
    }
  }
  
  return $phpProcesses;
}

function showProcess($processos){
  echo "\n\nProcessos PHP em execução:\n";

  if(is_array($processos) && count($processos) > 0){
    foreach($processos as $processo){
      echo $processo['pid'] . ' - ' . $processo['name'] . ':' . $processo['port'] . "\n";
    }
  } else {
    echo "Nenhum processo PHP em execução.\n";
  }
}

function choseHowToWorskWithActiveProcess($processos){
  global $argv, $FLAGS, $parammeters;
  $port = empty($argv['--port']) ? $parammeters['port'] : $argv['--port'];

  $is_break = isset($argv[array_keys($FLAGS)[0]]);
  $is_break_with_port = $is_break && !empty($argv[array_keys($FLAGS)[0]]);
  $is_force = !$is_break && isset($argv[array_keys($FLAGS)[5]]);

  $opt = 0;
  if($is_break && !$is_break_with_port){
    $opt = 1;
  } else if($is_force){
    $opt = 2;
  } else if($is_break_with_port){
    $opt = 3;
  }
  
  $opts = [
    '1' => 'Encerrar todos os processos',
    '2' => 'Encerrar processos com porta igual a atual (:' . $port . ')',
    '3' => 'Encerrar processos por porta(s) específica(s)',
  ];
  while(!in_array($opt, array_keys($opts))){
    showProcess($processos);

    echo "\nEscolha uma opção:\n";
    foreach($opts as $key => $value){
      echo $key . ' - ' . $value . "\n";
    }

    $opt = readline("> ");
    if(!in_array($opt, array_keys($opts))){
      echo "Opção inválida. Tente novamente.\n";
    } else $opt = (int)$opt;
  }

  switch($opt){
    case 1:
      return true;
    case 2:
      return $port;
    case 3:

      if($is_break_with_port){
        if(is_array($argv[array_keys($FLAGS)[0]])){
          $ports = implode(' ', $argv[array_keys($FLAGS)[0]]);
        } else {
          $ports = $argv[array_keys($FLAGS)[0]];
        }
      } else {
        echo "Digite as portas separadas por espaço\n";
        $ports = readline('> ');
      }

      return preparePorts($ports);
  }
}

function preparePorts($ports){
  $opt = false;
  $rejected_ports = [];
  
  do{
    if(!is_array($ports)){
      if(mb_strpos($ports, ' ') !== false){
        $ports = explode(' ', $ports);
      } else {
        $ports = $ports;
        break;
      }
    }
    
    foreach($ports as $key => $port){
      if($port === '') {
        unset($ports[$key]);
        continue;
      }

      if(!is_numeric($port)){
        $rejected_ports[] = $port;
        unset($ports[$key]);
        continue;
      }

      $ports[$key] = (int)$port;
    }

    if(count($rejected_ports) > 0){
      echo "\nPortas inválidas: " . implode(', ', $rejected_ports) . "\n";
      echo "* Digite as portas separadas por espaço (para tentar novamente)\n";
      echo "* Deixe em branco para sair\n";
      $correct_ports = readline("> ");
      if($correct_ports == ''){
        $opt = true;
      } else $ports = implode(' ', $ports) . ' ' . $correct_ports;
    } else {
      $opt = true;
    }
  } while ($opt == false && count($rejected_ports) > 0);

  return $ports;
}

function ajdustDivider($string, $from, $to){
  while(strpos($string, $from)){
    $string = str_replace($from, $to, $string);
  }

  return $string;
}

function shellExecKill($type, $identifier){
  $types = [
      'PID' => '/PID',
      'NAME' => '/IM'
  ];

  $command = 'taskkill /F ' . $types[$type] . ' ' . $identifier;
  echo "\nEncerrando processo, por identificador ($type): " . $identifier . "\n";
  shell_exec($command);
}