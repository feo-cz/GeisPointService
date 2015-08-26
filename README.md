# GeisPointService

[![Build Status](https://travis-ci.org/ondrejd/GeisPointService.svg)](https://travis-ci.org/ondrejd/GeisPointService)

PHP implementace klienta pro webovou službu [GeisPoint](http://www.geispoint.cz/).

> __For non-Czech visitors__: Since __GeisPoint__ web service is located in Czech Republic and serves mainly Czech users all documentation is written directly in Czech (but code self is commented in English and examples are self-explanatory).


## Instalace

__TBD__

## Použití

Nejprve rychlý příklad. Následující skript:

```php
<?php
require 'vendor/autoload.php';

$gpsrv = new \GeisPointService\Service();

// Získáme všechny regiony
$regions = $gpsrv->getRegions();
echo 'Počet regionů: '.count($regions).PHP_EOL;

// Vybereme náhodný region
$region = $regions[rand(0, count($regions) - 1)];
echo 'Města regionu '.$region->name.PHP_EOL;

// Vybereme města zvoleného regionu
$cities = $gpsrv->getCities(null, $region->id_region);
echo 'Počet měst: '.count($cities).PHP_EOL;

// Vybereme náhodné město
$city = $cities[rand(0, count($cities) - 1)];
echo 'Výdejní místa pro město '.$city->city.PHP_EOL;

// Najdeme výdejní místa v daném městě
$points = $gpsrv->searchPoints(null, $city->city, null);
echo 'Počet výdejních míst: '.count($points).PHP_EOL;
```

by měl vypsat následující výstup (konkrétní hodnoty se mohou lišit):

```
Počet regionů: 14
Města regionu Vysočina
Počet měst: 18
Výdejní místa pro město Havlíčkův Brod
Počet výdejních míst: 2
```

### Konfigurace

Konstruktor třídy `\GeisPointService\Service` přijíma jako jediný argument pole s nastavením klienta. Možnosti konfigurace jsou následující:

#### Přehled nastavení

Pole s nastavením může obsahovat následující hodnoty:

| Klíč             | Typ hodnoty | Popis
|------------------|-------------|------------------
| `defaultCountry` | `string`    | Defaultní země.
| `defaultRegion`  | `integer`   | Defaultní region.
| `useCache`       | `boolean`   | Pokud je `TRUE` je využita cache.
| `usedCache`      | `string`    | Jméno třídy, která implementuje cache. Je možno využít vlastní třídu (implementující rozhraní `\GeisPointService\Cache\CacheInterface`) nebo jednu ze dvou předvytvořených (`\GeisPointService\Cache\DbCache` či `\GeisPointService\Cache\FileCache`).
| `cacheOptions`   | `array`     | Viz. níže.

##### Nastavení pro `\GeisPointService\Cache\FileCache`

| Klíč             | Typ hodnoty | Popis
|------------------|-------------|------------------
| `path`           | `string`    | Cesta k souboru, který bude cache využívat.

##### Nastavení pro `\GeisPointService\Cache\DbCache`

Tato třída využíva pro připojení [PDO](http://php.net/manual/en/class.pdo.php). Z toho vyplývá i nastavení:

| Klíč             | Typ hodnoty | Popis
|------------------|-------------|------------------
| `dsn`            | `string`    | Řetězec popisující nastavení spojení s databází.
| `user`           | `string`    | Jméno uživatele pro spojení s databází.
| `password`       | `string`    | Heslo uživatele pro spojení s databází.
| `table`          | `string`    | Název tabulky.

__Pozn.__ Pokud tabulka neexistuje, bude vytvořena.

#### Příklad nastavení

Níže je příklad nastavení s využitím souborové cache:

```php
<?php
require 'vendor/autoload.php';

$options = array(
	'defaultCountry' => 'cz',
	'defaultRegion' => 19,
	'useCache' => true,
	'usedCache' => '\GeisPointService\Cache\FileCache',
	'cacheOptions' => array(
		'path' => '/path/to/your/file'
	)
);

$gpsrv = new \GeisPointService\Service($options);

// ...
```
