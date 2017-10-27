![IDIN logo](https://github.com/nimbles-nl/cm-telecom/blob/master/logo/IDIN_logo_64_pixels.png)

[![Build Status](https://travis-ci.org/nimbles-nl/cm-telecom.svg?branch=master)](https://travis-ci.org/nimbles-nl/cm-telecom) [![Latest Stable Version](https://poser.pugx.org/nimbles-nl/cm-telecom/v/stable)](https://packagist.org/packages/nimbles-nl/cm-telecom) [![License](https://poser.pugx.org/nimbles-nl/cm-telecom/license)](https://packagist.org/packages/nimbles-nl/cm-telecom) [![Total Downloads](https://poser.pugx.org/nimbles-nl/cm-telecom/downloads)](https://packagist.org/packages/nimbles-nl/cm-telecom) [![codecov](https://codecov.io/gh/nimbles-nl/cm-telecom/branch/master/graph/badge.svg)](https://codecov.io/gh/nimbles-nl/cm-telecom) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nimbles-nl/cm-telecom/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nimbles-nl/cm-telecom/?branch=master)

## Introduction

IDIN is used for personal identification by bank and is supported by Dutch banks.
With IDIN you can be sure who is registering on your application.

See for more information: [https://www.idin.nl](https://www.idin.nl/)
This PHP package contains a client for [CM Telecom](https://get.cm.nl/idin/).

## Installation

### Download the package using composer

Install package by running the command:

``` bash
$ composer require nimbles-nl/cm-telecom
```

Initializing IDINClient
-----------------------

``` php
$guzzle          = new \GuzzleHttp\Client();
$apiToken        = 'secret-token';
$apiUrl          = 'https://idin.cmtelecom.com/idin/v1.0/test';
$applicationName = 'MyApp';

$IDINClient = new IDINClient($guzzle, $apiToken, $apiUrl, $applicationName);
```

Get a list of issuers
---------------------

``` php
$issuers = $IDINClient->getIssuers();
```


Start an IDIN Transaction
-------------------------

``` php
$issuers = $IDINClient->getIssuers();

$myBank = $issuers[0];
$IDINTransaction = $IDINClient->getIDINTransaction($myBank);

// Remember this data / store it in your database
$transactionId     = $IDINTransaction->getTransactionId();
$entranceCode      = $IDINTransaction->getEntranceCode();
$merchantReference = $IDINTransaction->getMerchantReference();

// Redirect the user to the bank page
return new RedirectResponse($IDINTransaction->getAuthenticationUrl());
```

Recieve an array of user details with the IDIN Transaction
----------------------------------------------------------

``` php
$IDINTransaction = new IDINTransaction($transactionId, $merchantReference, $entranceCode);

$userData = $IDINClient->getUserInfo($IDINTransaction);
```
