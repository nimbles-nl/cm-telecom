![IDIN logo](https://github.com/nimbles-nl/cm-telecom/blob/master/logo/IDIN_logo_64_pixels.png)

[![Build Status](https://travis-ci.org/nimbles-nl/cm-telecom.svg?branch=master)](https://travis-ci.org/nimbles-nl/cm-telecom) [![Latest Stable Version](https://poser.pugx.org/nimbles-nl/cm-telecom/v/stable)](https://packagist.org/packages/nimbles-nl/cm-telecom) [![License](https://poser.pugx.org/nimbles-nl/cm-telecom/license)](https://packagist.org/packages/nimbles-nl/cm-telecom) [![Total Downloads](https://poser.pugx.org/nimbles-nl/cm-telecom/downloads)](https://packagist.org/packages/nimbles-nl/cm-telecom) [![codecov](https://codecov.io/gh/nimbles-nl/cm-telecom/branch/master/graph/badge.svg)](https://codecov.io/gh/nimbles-nl/cm-telecom) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nimbles-nl/cm-telecom/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nimbles-nl/cm-telecom/?branch=master)

Introduction
------------

iDIN is used for personal identification by bank and is supported by Dutch banks.
With iDIN you can be sure who is registering on your application. This PHP package contains a client for [CM Telecom](https://get.cm.nl/idin/).

See for more information: [https://www.idin.nl](https://www.idin.nl/)

## Installation

### Download the package using composer

Install package by running the command:

``` bash
$ composer require nimbles-nl/cm-telecom
```

## Usage
This package is easy to use and can be used in any php project with php 7.0 or later.


### Initializing IDIN Client
``` php
$guzzle          = new \GuzzleHttp\Client();
$apiToken        = 'secret-token';
$apiUrl          = 'https://idin.cmtelecom.com/idin/v1.0/test';
$applicationName = 'MyApp';

$client = new IDINClient($guzzle, $apiToken, $apiUrl, $applicationName);
```

### Get a list of issuers
``` php
$issuers = $client->getIssuers();
```

### Start an iDIN Transaction

``` php
$issuers = $client->getIssuers();

$issuer = $issuers[0];
$transaction = $client->getIDINTransaction($issuer);

// Remember this data / store it in your database
$transactionId     = $transaction->getTransactionId();
$entranceCode      = $transaction->getEntranceCode();
$merchantReference = $transaction->getMerchantReference();

// Redirect the user to the bank page
return new RedirectResponse($transaction->getAuthenticationUrl());
```

### Receive an array of user details with the iDIN Transaction object
``` php
$transaction = new IDINTransaction($transactionId, $merchantReference, $entranceCode);

$userData = $client->getUserInfo($transaction);
```

You can also receive bank account details with the IBANClient. It works almost the same as with IDIN. The client is already included in this package, but remember you use a different url for the api requests.


### Initializing IBAN Client
``` php
$guzzle          = new \GuzzleHttp\Client();
$apiToken        = 'secret-token';
$apiUrl          = 'https://ibancheck.cmdisp.com/ibancheck/v1.0/test';
$applicationName = 'MyApp';

$client = new IBANClient($guzzle, $apiToken, $apiUrl, $applicationName);
```

### Get a list of issuers
``` php
$issuers = $client->getIssuers();
```

### Start an IBAN Transaction

``` php
$issuers = $client->getIssuers();

$issuer = $issuers[0];
$transaction = $client->getIBANTransaction($issuer);

// Remember this data / store it in your database
$transactionId     = $transaction->getTransactionId();
$entranceCode      = $transaction->getEntranceCode();
$merchantReference = $transaction->getMerchantReference();

// Redirect the user to the bank page
return new RedirectResponse($transaction->getAuthenticationUrl());
```

### Receive an array of bank details with the IBAN Transaction object
``` php
$transaction = new IBANTransaction($transactionId, $merchantReference, $entranceCode);

$userData = $client->getTransactionInfo($transaction);
```
