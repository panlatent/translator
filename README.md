Translator
===========

Translator is command tools that can extract message strings from PHP source code and Twig templates. 
It is designed to work with tools like Poedit and supports CraftCMS.

Requirements
------------
+ PHP 8.2 or later

Installation
------------

```bash
composer global require panlatent/translator:cli
```

Usages
------

Add custom extractor on Poedit. 

```bash
./translator extract %F --output=%o
```

Add config to `config/app.php`:
```php
   'components' => [
        'i18n' => [
            'translations' => [
                'site' => [
                    'class' => GettextMessageSource::class,
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@translations',
                ],
                '*' => [
                    'class' => GettextMessageSource::class,
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@translations',
                ],
            ]
        ],
    ],
```

License
-------
The Translator is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).