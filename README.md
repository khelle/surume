# Surume Framework

[![Build Status](https://travis-ci.org/khelle/surume.svg)](https://travis-ci.org/khelle/surume)
[![Total Downloads](https://poser.pugx.org/khelle/surume/downloads)](https://packagist.org/packages/khelle/surume) 
[![Latest Stable Version](https://poser.pugx.org/khelle/surume/v/stable)](https://packagist.org/packages/khelle/surume) 
[![Latest Unstable Version](https://poser.pugx.org/khelle/surume/v/unstable)](https://packagist.org/packages/khelle/surume) 
[![License](https://poser.pugx.org/khelle/surume/license)](https://packagist.org/packages/khelle/surume)

## Description

Surume is a framework created for writing distributed webapplication.

## Installation

To install the framework, first clone the repository using command

```
git clone git@github.com:khelle/surume.git .
```

Then download all dependencies using composer

```
composer install --no-interaction
```

After successful installation, copy data directory and shell scripts

```
cp ./vendor/khelle/surume/surume-data ./surume-data
cp ./vendor/khelle/surume/surume ./surume
cp ./vendor/khelle/surume/surume.server ./surume.server
```

You are ready to go

## Test

To run tests, simply run

```
vendor/bin/phpunit --coverage-text
```

## License

Surume framework is open-sourced software licensed under the [MIT license][1].

[1]: http://opensource.org/licenses/MIT
