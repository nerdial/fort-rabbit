### There is not much to do to setup the project only run composer install 

```bash
composer install
```


### Run following command to get list of available commands

```bash
php rabbit
```


### This command will generate table output to the console

```bash
php rabbit craft:analyze 
```

### Limit the result

```bash
php rabbit craft:analyze --limit 20
```

### Order by functionality

```bash
php rabbit craft:analyze --orderBy dependents
```

### Order type functionality

```bash
php rabbit craft:analyze --order asc
```

### Output to json file, there is a output file example in the source code as well

```bash
php rabbit craft:analyze --output sample-output.json
```


### Run the unit tests

```bash
./vendor/bin/phpunit  src/Tests
```

