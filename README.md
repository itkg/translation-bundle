translation-bundle
==================

[![Build Status](https://travis-ci.org/itkg/translation-bundle.svg?branch=master)](https://travis-ci.org/itkg/translation-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itkg/translation-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itkg/translation-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/itkg/translation-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/itkg/translation-bundle/?branch=master)

# About

Import / Export translation feature for Symfony 2 projects

# Installation

## Installation by Composer

* If you use composer, add ItkgDelayEventBundle bundle as a dependency to the composer.json of your application

```json

    "require": {
        "itkg/translation-bundle": "dev-master"
    },

```

* Add ItkgTranslationBundle to your application kernel.

```php

// app/AppKernel.php
<?php
    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Itkg\TranslationBundle\ItkgTranslationBundle(),
        );
    }

```

# Usage 

* Export yml files to a CSV 

```
app/console itkg:translation:convert --path ./ --input=yml --output=csv --output-path=/var/www/mhps/translations
```

* Import csv files to a yml 

```
 app/console itkg:translation:convert --path ./translations/ --input=csv --output=yml
```
