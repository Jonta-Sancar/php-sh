<?php
function adjustParammeters(){
  global $argv, $parammeters;

  adjustArgs();
  
  $parammeters_flags = array_keys($parammeters);
  foreach ($argv as $key => $value) {
    $key = str_replace('--', '', $key);
    if(in_array($key, $parammeters_flags)){
      $parammeters[$key] = $value;
    }
  }
}

function adjustArgs(){
    global $argv, $argc, $FLAGS, $SIMPLE_FLAGS;
    unset($argv[0]);
    $argv = array_values($argv);
    $argc = count($argv);

    foreach ($argv as $i => $arg) {
        $FLAG_i = array_search($arg, $SIMPLE_FLAGS);
        if($FLAG_i !== false){
            $arg = array_keys($FLAGS)[$FLAG_i];
        }

        if(in_array($arg, array_keys($FLAGS))){
            if($FLAGS[$arg]['content'] === false){
                $argv[$arg] = false;
            } else {
                $i2 = $i + 1;
                do{
                    if(isset($argv[$arg]) && array_keys($FLAGS)[0] === $arg){
                        $actual_value = $argv[$arg];
                        if(is_array($actual_value)){
                            $actual_value[] = $argv[$i2];
                        } else {
                            $actual_value = [$actual_value, $argv[$i2]];
                        }

                        $argv[$arg] = $actual_value;
                    } else {
                        $argv[$arg] = isset($argv[$i2]) ? $argv[$i2] : true;
                    }

                    unset($argv[$i2]);

                    if(isset($argv[++$i2])){
                        $FLAG_i = array_search($argv[$i2], $SIMPLE_FLAGS);
                        if($FLAG_i === false){
                            $FLAG_i = array_search($argv[$i2], array_keys($FLAGS));
                        }
                    }
                }while(isset($argv[$i2]) && $FLAG_i === false);

                if(is_array($argv[$arg])){
                    $argv[$arg] = preparePorts(implode(' ', $argv[$arg]));
                }
            }
        } else {
            if($i == 0){
                $argv['dir'] = $arg;
            }
        }
        unset($argv[$i]);
    }
}