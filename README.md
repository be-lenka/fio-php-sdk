# Fio

Support [Fio API](http://www.fio.sk/docs/cz/API_Bankovnictvi.pdf). Read is provided via json file.

### Installation to project

The best way to install be-lenka/fio-php-sdk is using Composer:
```sh
$ composer require be-lenka/fio-php-sdk
```

### How to use
Here is [example](tests/origin/FioTest.php) and run via cli. This script require account.ini in same directory, whose looks like.

```ini
[my-account]
account = 123456789
token = abcdefghijklmn

[wife-account]
account = 987654321
token = zyxuvtsrfd
```

FioFactory class help you create instances of classes FioPay and FioRead.

```php
use Belenka\Fio;
$fioFactory = new Fio\Utils\FioFactory([
	'my-alias' => [
		'account' => '123456789',
		'token' => 'abcdefg'
	],
	'next-alias' => [
		'account' => '987654321',
		'token' => 'tuvwxyz'
	]
]);

$fioRead = $fioFactory->createFioRead('my-account');
$fioPay = $fioFactory->createFioPay('wife-account');
```

## Reading

#### Read range between date.

```php
use Belenka\Fio;
/* @var $fioRead Fio\FioRead */
/* @var $list Fio\Response\Read\TransactionList */
$list = $fioRead->movements(/* $from, $to */); // default is last week

foreach ($list as $transaction) {
    /* @var $transaction Fio\Response\Read\Transaction */
    var_dump($transaction->moveId);
    foreach ($transaction as $property => $value) {
        var_dump($property, $value);
    }
}

var_dump($list->getInfo());
```

#### You can download transaction by id of year.
```php
use Belenka\Fio;
/* @var $fioRead Fio\FioRead */
/* @var $list Fio\Response\Read\TransactionList */
$list = $fioRead->movementId(2, 2015); // second transaction of year 2015
```

#### Very useful method where download last transactions.
After download it automaticaly set new break point.
```php
use Belenka\Fio;
/* @var $fioRead Fio\FioRead */
/* @var $list Fio\Response\Read\TransactionList */
$list = $fioRead->lastDownload();
// same use like above
var_dump($list->getInfo()->idLastDownload);
```

#### Change your break point.
By date.
```php
$fioRead->setLastDate('1986-12-30');
$list = $fioRead->lastDownload();
var_dump($list->getInfo()->idLastDownload);
```

By movement ID.
```php
$fioRead->setLastId(123456789);
$list = $fioRead->lastDownload();
var_dump($list->getInfo()->idLastDownload); // 123456789
```

#### Custom Transaction class
By default is Belenka\Fio\Response\Read\Transaction if you want other names for properties. Let's define as second parameter to FioFactory.


Define annotation and you don't forget id in brackets.
```php
<?php

use Belenka\Fio\Response\Read\TransactionAbstract

/**
 * @property-read float $amount [1]
 * @property-read string $to_account [2]
 * @property-read string $bank_code [3]
 */
class MyTransaction extends TransactionAbstract
{
	/** custom method */
	public function setBank_code($value)
	{
		return str_pad($value, 4, '0', STR_PAD_LEFT);
	}
}

$fioFactory = new Utils\FioFactory([/* ... */], 'MyTransaction');
```


## Payment (writing)

Api has three response languages, default is set **cs**. For change:
```php
/* @var $fioPay Belenka\Fio\FioPay */
$fioPay->setLanguage('en');
```

For send request is method send whose accept, file path to your xml or abo file or instance of class Property.
```php
$myFile = '/path/to/my/xml/or/abo/file'
$fioPay->send($myFile);
```

Object pay only to czech or slovak:
```php
/* @var $national Belenka\Fio\Request\Pay\Payment\National */
$national = $fioPay->createNational($amount, $accountTo);
$national->setVariableSymbol($vs);
/* set next payment property $national->set* */
$fioPay->send($national);
```

Euro zone payment:
```php
/* @var $euro Belenka\Fio\Request\Pay\Payment\Euro */
$euro = $fioPay->createEuro($amount, $accountTo, $name);
$euro->setVariableSymbol($vs);
/* set next payment property $euro->set* */
$fioPay->send($euro);
```

International payment:
```php
/* @var $international Belenka\Fio\Request\Pay\Payment\International */
$international = $fioPay->createInternational($amount, $accountTo, $bic, $name, $street, $city, $country, $info);
$international->setRemittanceInfo2('foo');
/* set next payment property $international->set* */
$fioPay->send($international);
```

Send more payments in one request:
```php
foreach($pamentsRows as $row) {
	/* @var $national Belenka\Fio\Request\Pay\Payment\National */
	$national = $fioPay->createNational($row->amount, $row->accountTo);
	$national->setVariableSymbol($row->vs);
	$fioPay->addPayment($national);
}
$fioPay->send();
```
