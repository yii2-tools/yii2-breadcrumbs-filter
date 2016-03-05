# Yii2 Breadcrumbs ActionFilter

[![Latest Version](https://img.shields.io/packagist/v/yii2-tools/yii2-breadcrumbs-filter.svg?style=flat-square&label=release)](https://packagist.org/packages/yii2-tools/yii2-breadcrumbs-filter)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/yii2-tools/yii2-breadcrumbs-filter.svg?style=flat-square)](https://scrutinizer-ci.com/g/yii2-tools/yii2-breadcrumbs-filter)
[![Total Downloads](https://img.shields.io/packagist/dt/yii2-tools/yii2-breadcrumbs-filter.svg?style=flat-square)](https://packagist.org/packages/yii2-tools/yii2-breadcrumbs-filter)

Yii2 ActionFilter which automatically append module as breadcrumb item if his id exists in requested route.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require yii2-tools/yii2-breadcrumbs-filter:~1.0
```

or add

```
"yii2-tools/yii2-breadcrumbs-filter": "~1.0"
```

to the `require` section of your `composer.json` file.

## Usage

[Attach behavior](http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html#attaching-behaviors) to module:

```PHP
public function behaviors()
{
    return array_merge(parent::behaviors(), [
        'breadcrumbs' => [
            'class' => \yii\tools\filters\BreadcrumbsFilter::className(),
        ]
    ]);
}
```

In [view](https://github.com/yiisoft/yii2/blob/master/docs/guide/structure-views.md) file (perhaps, layout):

```PHP
<div class="container">
    <?= \yii\widgets\Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]) ?>
</div>
```

## Best practices

You can unify building of site breadcrumbs navigation by extending [yii\base\Module](http://www.yiiframework.com/doc-2.0/yii-base-module.html).
It will guarantee what all modules in requested route gets their place in breadcrumbs widget. Example:

```PHP
use yii\base\Module as BaseModule;
use yii\tools\filters\BreadcrumbsFilter;

class Module extends BaseModule
{
    /**
     * Module name
     * @var string
     */
    public $name = 'My Module';

    /**
     * Enable/Disable breadcrumbs natigation via app\components\filters\BreadcrumbsFilter
     * For module and submodules, without affects on parent module
     * @var bool
     */
    public $breadcrumbs = true;

    /**
     * Array of [routes|controllers|actions] names which shouldn't have breadcrumbs
     * ['*'] means what breadcrumbs navigation disabled for all controllers and actions (direct childs)
     * For module and submodules, without affects on parent module
     * @var bool
     */
    public $breadcrumbsExceptRoutes = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [];

        if ($this->breadcrumbs) {
            $behaviors['breadcrumbs'] = [
                'class' => BreadcrumbsFilter::className(),
                'label' => $this->name,
                'defaultRoute' => $this->defaultRoute,
                'exceptRoutes' => $this->breadcrumbsExceptRoutes,
            ];
        }

        return array_merge(parent::behaviors(), $behaviors);
    }
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.