# ImcStream

[![Build Status](https://travis-ci.org/coldume/imc-stream.svg?branch=develop)](https://travis-ci.org/coldume/imc-stream)

This read-only wrapper is an abstract layer between stream open function and
any other wrapper. It controls data limit and timeout, gives rewind/seek
support to unseekable stream, replaces error reporting with exception handlers,
and more! 

## Depict

Previously:

    +----------------------+        +----------------+
    | Stream open function | -----> | Any wrapper    |
    | e.g fopen()          | <----- | e.g http://... |
    +----------------------+        +----------------+

Now:

    +----------------------+        +-------------+        +----------------+
    | Stream open function | -----> | imc wrapper | -----> | Any wrapper    |
    | e.g fopen()          | <----- | imc://...   | <----- | e.g http://... |
    +----------------------+        +-------------+        +----------------+

## Installation

Simply add a dependency on coldume/imc-stream to your project's composer.json
file:

````json
{
    "require": {
        "coldume/imc-stream": "~1.0"
    }
}
````

## Usage:

### Exception Handling

Any supported filesystem function will thorw exception when an error occurs,
such as file not found, read error, etc.

````php
use ImcStream\ImcStream;
use ImcStream\Exception\TranslatedException;

ImcStream::register();
$arr = ['uri' => __FILE__];
$path = 'imc://'.serialize($arr);
try {
    $fp = fopen($path, 'rb');
    fread($fp, 10);
    fclose($fp);
} catch (TranslatedException $e) {
    echo $e->getMessage();
}
````

In addition to the default English exception message, ImcStream can be switched
to other languages. You can help with existing translations or to add another
language.

````php
ImcStream::register(['locale' => 'zh_TW']);
````

### Seek and Rewind

````php
use ImcStream\ImcStream;
use ImcStream\Exception\TranslatedException;

ImcStream::register();
$arr = [
    'uri'  => 'http://www.example.com',
    'seek' => true,
];
$path = 'imc://'.serialize($arr);
try{
    $fp = fopen($path, 'rb');
    fseek($fp, 300);
    fread($fp, 800);
    rewind($fp);
    fseek($fp, 500);
    fread($fp, 1024);
} catch (TranslatedException $e) {
    echo $e->getMessage();
}
````

### Data Limit and Timeout

These two directives only work on non-local stream. 

The stream read function will throw exception if data read exceeds allowalbe
size limit or timeout expired.

````php
use ImcStream\ImcStream;
use ImcStream\Exception\TranslatedException;

ImcStream::register();
$arr = [
    'uri'        => 'http://www.example.com',
    'data_limit' => 500,                      // Unit in KB.
    'timeout'    => 5,                        // Unit in second.
];
$path = 'imc://'.serialize($arr);
try {
    $fp = fopen($path, 'rb');
    while (!feof($fp)) {
        fread($fp, 1024);
    }
    fclose($fp);
} catch (TranslatedException $e) {
    echo $e->getMessage();
}
````

### Global URI

This directive only works on non-local stream. 

Sometimes, you have to open an URI multiple times:

````php
$fp = fopen('http://www.example.com', 'rb');
fread($fp, 1024);
fclose($fp);
file_get_contents('http://www.example.com');
file_get_contents('http://www.example.com');
````

The above operations is inefficient, as it will download the same network
resource three times. This problem can be solved by enabling global and seek
directive:

````php
use ImcStream\ImcStream;
use ImcStream\Exception\TranslatedException;

ImcStream::register();
$arr = [
    'uri'    => 'http://www.example.com',
    'seek'   => true,
    'global' => true,
];
$path = 'imc://'.serialize($arr);
try {
    fopen($path, 'rb');
    fread($fp, 1024);
    fclose($path);
    file_get_contents($path);
    file_get_contents($path);
    ImcStream::fclose($path);
} catch (TranslatedException $e) {
    echo $e->getMessage();
}
````

Once global directive is activated, `fclose()` will merely rewind internal
file pointer, let the latter function read from beginning of the file. You have
to call `ImcStream::fclose()` to close the internal file pointer.

## Resources

*   PHP supported protocols and wrappers.

    http://php.net/manual/en/wrappers.php

*   The streamWrapper class.

    http://php.net/manual/en/class.streamwrapper.php
