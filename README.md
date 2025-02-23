# GET STARTEd

## base code
start server with default values
- ex:
```cmd
 phps --server-helper
```
- ex:
```cmd
 phps -SH
```

start server with specific directory
- ex:
```cmd
 phps --server-helper <directory>
```
or:
```cmd
 phps -SH <directory>
```

## flags
### `--break` or `-b`
stop all php processes
```cmd
 phps -SH --break
```
or:
```cmd
 phps -SH -b
```

### `--host` or `-h`
if you want to start server in specific ip address, for example:
```cmd
 phps -SH --host 192.168.1.1
```
or:
```cmd
 phps -SH -h 192.168.1.1
```

### `--port` or `-p`
if you want to start server in specific port, for example:
```cmd
 phps -SH --port 300
```
or:
```cmd
 phps -SH -p 300
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