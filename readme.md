# PHPdiff

Diff between server file and local file.

Use PHPloy ini file for SSH connection parameters.

Sync local files with server files is possible.

## Usage

```
phpdiff -s server
```

To sync with server files: 

```
phpdiff -s server --sync
```

No phploy.ini file: 
```
phpdiff -u username -h host -p path
```
