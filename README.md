# Symfony Environment Variable Processors

[![Latest Stable Version](http://poser.pugx.org/nbgrp/env-bundle/v)](https://packagist.org/packages/nbgrp/env-bundle)
[![Latest Unstable Version](http://poser.pugx.org/nbgrp/env-bundle/v/unstable)](https://packagist.org/packages/nbgrp/env-bundle)
[![Total Downloads](https://poser.pugx.org/nbgrp/env-bundle/downloads)](https://packagist.org/packages/nbgrp/env-bundle)
[![License](https://poser.pugx.org/nbgrp/env-bundle/license)](https://packagist.org/packages/nbgrp/env-bundle)

[![PHP Version Require](http://poser.pugx.org/nbgrp/env-bundle/require/php)](https://packagist.org/packages/nbgrp/env-bundle)
[![Codecov](https://codecov.io/gh/nbgrp/env-bundle/branch/1.x/graph/badge.svg?token=3D6RG66XXN)](https://codecov.io/gh/nbgrp/env-bundle)
[![Audit](https://github.com/nbgrp/env-bundle/actions/workflows/audit.yml/badge.svg)](https://github.com/nbgrp/env-bundle/actions/workflows/audit.yml)

[![SymfonyInsight](https://insight.symfony.com/projects/eaacf2fc-2729-4e18-9b1c-f8fbd7827a7a/small.svg)](https://insight.symfony.com/projects/eaacf2fc-2729-4e18-9b1c-f8fbd7827a7a)

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/S6S073WSW)

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
        dot: '.'      #  csv_dot will parse env value into array with "." as a separator
        colon: ':'    #  csv_colon will parse env value into array with ":" as a separator
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
  bools:   '%env(bool_array:csv:CSV_BOOL_ENV)%'        #  will contains [true, false, false, true]
  ints:    '%env(int_array:csv:CSV_INT_ENV)%'          #  will contains [1, 2, 3]
  floats:  '%env(float_array:csv:CSV_FLOAT_ENV)%'      #  will contains ['key1' => 1.1, 'key2' => 2.2]
  strings: '%env(string_array:json:JSON_STRING_ENV)%'  #  will contains ['foo', 'foo "bar"', '']
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
  person: '%env(csv_semi:CSV_SEMICOLON_ENV)%'  #  will contains ['Alice', 'alice@mail.me']
```
