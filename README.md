yii2-rbac-cached
================
Cached for yii2 RBAC

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require "letyii/yii2-rbac-cached" "dev-master"
```
or add

```json
"letyii/yii2-rbac-cached": "dev-master"
```

to the require section of your application's `composer.json` file.

## Usage Example
~~~php
'components' => [
    // the rest of your components section
    'authManager' => [
        'class' => 'letyii\rbaccached\RbacCached',
    ],
]
~~~

github: https://github.com/letyii/yii2-rbac-cached

packagist: https://packagist.org/packages/letyii/yii2-rbac-cached
