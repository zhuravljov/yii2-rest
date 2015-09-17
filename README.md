Yii2 REST Client
================

[![Latest Stable Version](https://poser.pugx.org/zhuravljov/yii2-rest/v/stable.png)](https://packagist.org/packages/zhuravljov/yii2-rest)
[![Build Status](https://travis-ci.org/zhuravljov/yii2-rest.svg)](https://travis-ci.org/zhuravljov/yii2-rest)


What is this?
-------------

![Screen](/docs/images/screen1.png)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Add

```
"zhuravljov/yii2-rest": "*",
"yiisoft/yii2-httpclient": "@dev"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['rest-client'],
    'modules' => [
        'rest-client' => [
            'class' => 'zhuravljov\yii\rest\Module',
            'baseUrl' => 'http://localhost/api/v1',
        ],
    ],
];
```

You can then access Rest Client through the following URL:

```
http://localhost/path/to/index.php?r=rest-client
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/rest-client
```
