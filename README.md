# Elastic Email Plugin for CakePHP 3

[![Build Status](https://travis-ci.org/pnglabz/cakephp-elastic-email.svg?branch=master)](https://travis-ci.org/pnglabz/cakephp-elastic-email)
[![codecov](https://codecov.io/gh/pnglabz/cakephp-elastic-email/branch/master/graph/badge.svg)](https://codecov.io/gh/pnglabz/cakephp-elastic-email)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Stable Version](https://poser.pugx.org/pnglabz/cakephp-elastic-email/v/stable)](https://packagist.org/packages/pnglabz/cakephp-elastic-email)
[![Total Downloads](https://poser.pugx.org/pnglabz/cakephp-elastic-email/downloads)](https://packagist.org/packages/pnglabz/cakephp-elastic-email)

This plugin provides email delivery using [Elastic Email](https://elasticemail.com/).

## Requirements

This plugin has the following requirements:

* CakePHP 3.4.0 or greater.
* PHP 5.6 or greater.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

```
composer require pnglabz/cakephp-elastic-email
```

After installation, [Load the plugin](http://book.cakephp.org/3.0/en/plugins.html#loading-a-plugin)
```php
Plugin::load('ElasticEmail');
```
Or, you can load the plugin using the shell command
```sh
$ bin/cake plugin load ElasticEmail
```

## Setup

Set your Elastic Email Api key in `EmailTransport` settings in app.php

```php
'EmailTransport' => [
...
  'elasticemail' => [
      'className' => 'ElasticEmail.ElasticEmail',
      'apiKey' => 'your-api-key' // your api key
  ]
]
```

If you face an SSL certificate error, please follow below steps:

1. Open http://curl.haxx.se/ca/cacert.pem  
2. Copy the entire page and save it as a "cacert.pem"  
3. Open your php.ini file and insert or update the following line: curl.cainfo = "[pathtofile]\cacert.pem"

And create new delivery profile in `Email` settings.

```php
'Email' => [
    'default' => [
        'transport' => 'default',
        'from' => 'you@localhost',
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    ],
    'elasticemail' => [
        'transport' => 'elasticemail'
    ]
]
```

## Usage

You can now simply use the CakePHP `Email` to send an email via Mailgun.

```php
$email = new Email('elasticemail');
        
$email->setFrom(['you@yourdomain.com' => 'CakePHP Elastic Email'])
    ->setSender('someone@example.com', 'Someone')
    ->setTo('foo@example.com.com')
    ->addTo('bar@example.com')
    ->setHeaders(['X-Custom' => 'headervalue'])
    ->setSubject('Email from CakePHP Elastic Email plugin')
    ->send('Message from CakePHP Elastic Email plugin');
```

That is it.

## Advance Use
You can also use few more options to send email via Elastic Email APIs. To do so, get the transport instance and call the appropriate methods before sending the email.

### Transactional Email
You can mark the email as `transaction` email.

```php
$email = new Email('elasticemail');
$emailInstance = $email->getTransport();
$emailInstance->isTransactional(true);
$email->send();
```

### Template
You can use the template created in Elastic Email backend. Get the template id by either using their API or from the URL.
Set the template id using `setTemplate` method.

```php
$email = new Email('elasticemail');
$emailInstance = $email->getTransport();
$emailInstance->setTemplte(123);
$email->send();
```

### Template Variables
Elastic Email provides a nice way to replace the template content using template variables. You can use variables like {firstname}, {lastname} in your template and pass their replacement value.

```php
$mergeVars = [
    'firstname' => 'Foo',
    'lastname' => 'Bar',
    'title' => 'Good Title'
];

$email = new Email('elasticemail');
$emailInstance = $email->getTransport();
$emailInstance->setMergeVariables($mergeVars);

$email->setFrom(['from@example.com' => 'CakePHP Elastic Email'])
    ->setTo('to@example.com')
    ->setEmailFormat('both')
    ->setSubject('{title} - Email from CakePHP Elastic Email plugin')
    ->send('Hello {firstname} {lastname}, <br> This is an email from CakePHP Elastic Email plugin.');
```

### Schedule
You can schedule the email to be sent in future date. You can set upto 1 year in future i.e. 524160 minutes.

```php
$email = new Email('elasticemail');
$emailInstance = $email->getTransport();
$emailInstance->setScheduleTime(60); // after 1 hour from sending time
$email->send();
```

## Reporting Issues

If you have a problem with this plugin or any bug, please open an issue on [GitHub](https://github.com/pnglabz/cakephp-elastic-email/issues).
