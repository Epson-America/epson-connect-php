
# Epson Connect PHP SDK
The Epson Connect PHP SDK provides a comprehensive interface to the Epson Connect API. With this SDK, PHP developers can effortlessly control Epson printers and scanners through the Epson cloud service.

## Getting Started
### Installation
To include the SDK in your project using Composer, run:

```
composer require arafatkatze/epson-connect-php
```

## Prerequisites
Ensure you have the following credentials:

- Printer Email
- Client ID
- Client Secret

These can be obtained from the Epson Connect API registration portal.

## Usage

You can initialize the client using direct parameters:

```php

require 'vendor/autoload.php';

use  Epsonconnectphp\Epson\Client;
$client = new Client("pdx3882hvp0q97@print.epsonconnect.com", "a243e42e187e469f8e9c6e2383b7e2e6", "PDLDVwcHI7eX4oL2jHGEdIgl0EK9iMdjNkXumi2tZIgaeyG5AKtGqgHQCEyNZGsR");
$scanme = $client->getScanner();
$printer = $client->getPrinter();
echo "<pre>";
print_r($scanme->list());
echo "</pre>";

echo $printer->getDeviceId();
```

## Printing

```php
$printFile = $client->getPrinter();
$printFile->print("file_path.pdf");
```

## Scanning

```php
$scanme = $client->getScanner();
echo $scanme->list();
```

## Testing the library

```bash
git clone git@github.com:arafatkatze/epson-connect-php.git
composer install
./vendor/bin/phpunit
```

## Local Build

```bash
git clone git@github.com:arafatkatze/epson-connect-php.git
composer install
```

To publish to Packagist:  
Ensure you have the necessary configurations in your `composer.json` and then create a new release on your GitHub repository. Packagist will automatically detect and list your library.
