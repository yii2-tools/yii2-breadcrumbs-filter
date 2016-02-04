# Yii2 Breadcrumbs ActionFilter

[![Latest Version](https://img.shields.io/github/tag/itnelo/yii2-breadcrumbs-filter.svg?style=flat-square&label=release)](https://github.com/itnelo/yii2-breadcrumbs-filter/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/itnelo/yii2-breadcrumbs-filter/master.svg?style=flat-square)](https://travis-ci.org/itnelo/yii2-breadcrumbs-filter)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/itnelo/yii2-breadcrumbs-filter.svg?style=flat-square)](https://scrutinizer-ci.com/g/itnelo/yii2-breadcrumbs-filter/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/itnelo/yii2-breadcrumbs-filter.svg?style=flat-square)](https://scrutinizer-ci.com/g/itnelo/yii2-breadcrumbs-filter)
[![Total Downloads](https://img.shields.io/packagist/dt/itnelo/yii2-breadcrumbs-filter.svg?style=flat-square)](https://packagist.org/packages/itnelo/yii2-breadcrumbs-filter)

Yii2 ActionFilter which automatically append module as breadcrumb item if his id exists in requested route.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require itnelo/yii2-breadcrumbs-filter:~1.0
```

or add

```
"itnelo/yii2-breadcrumbs-filter": "~1.0"
```

to the `require` section of your `composer.json` file.

## Usage

[Attach behavior](http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html#attaching-behaviors) to module

```PHP
public function behaviors()
{
    return array_merge(parent::behaviors(), [
        'breadcrumbs' => [
            'class' => \itnelo\filters\BreadcrumbsFilter::className(),
        ]
    ]);
}
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.