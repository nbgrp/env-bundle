# Symfony Environment Variable Processors

![Audit](https://github.com/nbgrp/env-bundle/actions/workflows/audit.yml/badge.svg) [![Total Downloads](https://poser.pugx.org/nbgrp/env-bundle/downloads)](//packagist.org/packages/nbgrp/env-bundle)  [![License](https://poser.pugx.org/nbgrp/env-bundle/license)](//packagist.org/packages/nbgrp/env-bundle)

## Overview

The bundle provides a few useful custom Symfony Environment Variable processors:
* [Array Cast](#arraycastenvvarprocessor) processor for array values type casting.
* [CSV](#csvenvvarprocessor) processor with customizable delimiter.

## Installation / Configuration

```
composer require nbgrp/env-bundle
```

Enable the bundle in `config/bundles.php`:
``` php
return [
    // ...
    NbGroup\Symfony\NbgroupEnvBundle::class => ['all' => true],
];
```

All Environment Variable processors disabled by default. You should enable the required processors explicitly through
the bundle config.

YAML config example:
```yaml
# config/packages/nbgroup_env.yaml
nbgroup_env:
    array_cast: true  #  enable Array Cast processor
    csv:              #  enable CSV processor
        dot: '.'      #  csv-dot will parse env value into array with "." as a separator
        colon: ':'    #  csv-colon will parse env value into array with ":" as a separator
```

PHP config example (for Symfony 5):
```php
// config/packages/nbgroup_env.php
return static function (Symfony\Config\NbgroupEnvConfig $config): void {
    $config->arrayCast()
        ->enabled(true)
    ;

    $config->csv()
        ->enabled(true)
        ->delimiter('dot', '.')
        ->delimiter('colon', ':')
    ;
};
```

## Processors

### `ArrayCastEnvVarProcessor`

Performs type casting of the env value to the one of the supported types:
* bool
* int
* float
* string

> nb: If the csv value is not an array it will be casted to an array.

**Example:**

```yaml
# config/services.yaml
parameters:
  env(CSV_BOOL_ENV): '1,0,no,"true"'
  env(CSV_INT_ENV): '1,"2","3"'
  env(JSON_FLOAT_ENV): '{"key1": 1.1,"key2": "2.2"}'
  env(JSON_STRING_ENV): '["foo", "foo \"bar\"", ""]'
...
  bools:   '%env(bool-array:csv:CSV_BOOL_ENV)%'        #  will contains [true, false, false, true]
  ints:    '%env(int-array:csv:CSV_INT_ENV)%'          #  will contains [1, 2, 3]
  floats:  '%env(float-array:csv:CSV_FLOAT_ENV)%'      #  will contains ['key1' => 1.1, 'key2' => 2.2]
  strings: '%env(string-array:json:JSON_STRING_ENV)%'  #  will contains ['foo', 'foo "bar"', '']
```

### `CsvEnvVarProcessor`

Parses the env value into array. Unlike build-in `csv` processor, this one supports customization of the delimiter.

To use the CSV processor it should be configured: see [config example](#installation--configuration) how to specify
available delimiters (and so env prefixes).

> nb: Do not use backslash ` \ ` for escaping double quote `"` enclosure character (on PHP ^7.4 it will not work).
> For escape `"` just write it twice.

**Example:**

```yaml
# config/packages/nbgroup_env.yaml
nbgroup_env:
    csv:
        semi: ';'

# config/services.yaml
parameters:
  env(CSV_SEMICOLON_ENV): 'Alice;alice@mail.me'
...
  person: '%env(csv-semi:CSV_SEMICOLON_ENV)%'  #  will contains ['Alice', 'alice@mail.me']
```
