Mandrill API Extension
======================
Mandrill Api Integration for Yii2  
Version 1.0

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist nickcv/yii2-mandrill "*"
```

or add

```
"nickcv/yii2-mandrill": "*"
```

to the require section of your `composer.json` file.


Set Up
------

To use Mandrill you will need to have a [Mandrill Account](https://mandrill.com/signup/). 

Every single account can send up to **12k emails per month for free**.

The [cost for sending emails over that threshold is really low](https://mandrill.com/pricing/).

Once you have an account you will need to create an **API Key**.  
You can create as many API keys as you want, and it's best practice to create one for each website.  
You can also create **test API keys**. Every email submitted using a test API key will not actually be submitted, but you'll be able to check inside the **test dashboard** if the test went thorugh successfully.

Mandrill will keep track of every single email you submit. You can filter the data using tags and you'll also be able to check how many times each email was opened and if the links within it have been clicked.

Usage
-----

Once the extension is installed, change your application config file ```web.php```:

First of all you will need to add an ```application name```.
By default this extension will send every single email using the application name as the sender name and the ```adminEmail``` parameter inside ```params.php``` as the sender email.


```
'id' => 'basic',
'name' => 'Application Name',
```

```
return [
    'adminEmail' => 'admin@example.com',
];
```

You will then need to add the component

```
    'mailer' => [
        'class' => 'nickcv\mandrill\Mailer',
        'apikey' => 'YourApiKey',
    ],
```

From now on you can just use the mandrill mailer just as you used to use the default one.

```
\Yii::$app->mailer
    ->compose('mailViewName', ['model' => $model])
    ->setTo('email@email.com')
    ->send();
```


Additional Methods
------------------

Mandrill lets you set up tags. The method ```\nickcv\mandrill\Message::setTags($tags)``` accept as an argument both a string or an array of strings:

```
\Yii::$app->mailer
    ->compose('mailViewName', ['model' => $model])
    ->setTags(['registration']);
```

For more informations check the component documentation.