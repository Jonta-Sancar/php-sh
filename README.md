# GET STARTED

## install
You need to have installed PHP 8.1 or higher. Having this, you can install this package as a zip file and add it to your projects folder.
After that, you can add the uncompressed folder to your environment variables.

## usage
start server with default values
- ex:
```cmd
 php-sh
```

start server with specific directory
- ex:
```cmd
 php-sh <directory>
```

### flags
### `--break` or `-b`
if you use just the flag, this flag will break all servers, otherwise, if you set specifics ports, it will break only specific server
```cmd
 php-sh --break
```
or:
```cmd
 php-sh -b
```
can you set ports to break:
```cmd
 php-sh --break <ports>
```
for example:
```cmd
 php-sh --break 3000 5050 8080
```

### `--host` or `-ht`
if you want to start server in specific ip address, for example:
```cmd
 php-sh --host 192.168.1.1
```
or:
```cmd
 php-sh -h 192.168.1.1
```

### `--port` or `-p`
if you want to start server in specific port, for example:
```cmd
 php-sh --port 300
```
or:
```cmd
 php-sh -p 300
```

### `--list` or `-l`
if you want to list all servers that are running:
```cmd
 phps --list
```

### `--version` or `-v`
show version of this package and what is new:
```cmd
 phps --version
```
or:
```cmd
 phps -v
```

### `--help` or `-h`
this flags helps you to understand what this script can do:
```cmd
 phps --help
```
or:
```cmd
 phps -h
```