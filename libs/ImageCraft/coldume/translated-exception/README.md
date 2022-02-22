# TranslatedException

[![Build Status](https://travis-ci.org/coldume/translated-exception.svg?branch=develop)](https://travis-ci.org/coldume/translated-exception)

## Depict

Previously:

    +-------------+      'hello' received
    | Client side | <-----------------------+
    +-------------+                         |
           |                                |
           v                                |
    +-------------------+                   |
    | Do somthing wrong |                   |
    +-------------------+                   |
           |                                |
           v                                |
    +-------------------------------+       |
    | throw new \Exception('hello') |       |
    +-------------------------------+       |
           |                                |
           v                                |
    +---------------------------------+     |
    | Catch and echo $e->getMessage() | ----+
    +---------------------------------+

Now:

    +-------------+       'bonjour' received
    | Client side | <-------------------------------------+
    +-------------+                                       |
           |                                              |
           v                                              |
    +-------------------+                                 |
    | Do somthing wrong |                                 |
    +-------------------+                                 |
           |                                              |
           v                                              |
    +----------------------------------------+            |
    | throw new TranslatedException('hello') |            |
    +----------------------------------------+            |
           |                                              |
           v                    +---------------------+   |
    +---------------------+     | Translator          |   |
    | Inside              | --> | locale: "fr"        |   |
    | TranslatedException | <-- | dictionary: "fr-en" |   |
    +---------------------+     +---------------------+   |
           |                                              |
           v                                              |
    +---------------------------------+                   |
    | Catch and echo $e->getMessage() | ------------------+
    +---------------------------------+

## Installation

Simply add a dependency on coldume/translated-exception to your project's
composer.json file:

````json
{
    "require": {
        "coldume/translated-exception": "~1.0"
    }
}
````

## Usage

````php
use TranslatedException\TranslatedException;

$options = [
    'locale'    => 'fr',
    'cache_dir' => __DIR__.'/foo',
    'debug'     => true,
];
TranslatedException::init($options);
TranslatedException::addResourceDir(__DIR__.'/bar');
try {
    throw new TranslatedException('foo', 'hello.%name%', ['%name%' => 'foo']);
} catch (TranslatedException $e) {
    echo $e->getMessage();
}
````

## Resources

*   Symfony translation component.

    http://symfony.com/doc/current/components/translation/index.html
