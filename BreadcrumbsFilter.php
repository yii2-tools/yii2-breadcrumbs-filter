<?php

/*
The MIT License (MIT)

Copyright (c) 2016 yii2-tools <itnelo@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace yii\tools\filters;

use Yii;
use yii\helpers\Url;
use yii\base\UnknownMethodException;
use yii\base\UnknownPropertyException;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\web\Controller as WebController;

/**
 * Filter which automatically append module as breadcrumb item if it exists in requested route
 * If module-owner exists in current route, it will be added in breadcrumb widget config
 * (default: Yii::$app->controller->view->params['breadcrumbs'])
 *
 * Usage example:
 *
 *     public function behaviors()
 *     {
 *         return array_merge(parent::behaviors(), [
 *             'breadcrumbs' => [
 *                 'class' => BreadcrumbsFilter::className(),
 *             ]
 *         ]);
 *     }
 *
 * Example owners in use cases:
 *      yii\base\Module (recommended)
 *      yii\base\Component, with implemented 'getUniqueId()'
 *      Object, with routeCreator as callable function
 */
class BreadcrumbsFilter extends ActionFilter
{
    /**
     * Param name of current controller's View
     * which will be passed as input array for breadcrumb widget
     *
     * i.e. $view->params[$breadcrumbsParam] = [breadcrumbs config array ...]
     *
     * @var string
     */
    public $breadcrumbsParam = 'breadcrumbs';

    /**
     * Empty as default value, means what native php array numering used
     * If defined, breadcrumb config array becomes like that:
     *
     * [
     *     $breadcrumbsKey => [
     *         'label' => ...,
     *         'url' => ...
     *     ]
     * ]
     *
     * Useful if you have some override logic in your views, example:
     *
     * { // View context
     *     $this->params['breadcrumbs']['module'] = ...
     * }
     *
     * @var string
     */
    public $breadcrumbsKey = '';

    /**
     * Component field, used as label for breadcrumb widget record
     * @var string
     */
    public $labelParam = 'id';

    /**
     * If presents, used instead of $labelParam for constructing breadcrumb
     * @var string
     */
    public $label = '';

    /**
     * If default route matches current route
     * breadcrumb will not be active (Url parameter in config will be omitted)
     *
     * How it works:
     *      /news/default/create => Home / News / Create
     *      /news/default/index  => Home / News
     *                                instead of:
     *                              Home / News / Index
     *
     * Override this property to FALSE if you don't need such behavior
     * and want to always see full detailed breadcrumbs navigation
     * even if it's default module page
     * @var string
     */
    public $defaultRoute = 'default/index';

    /**
     * Array of strings-routes what should be ignored in breadcrumb navigation
     * Example: ['site/default/index', 'index', 'ind']
     * Comparation of current route with $exceptRoutes performs by strpos() function
     * ['*'] stands for "don't show me in breadcrumbs completely"
     * @var array
     */
    public $exceptRoutes = [];

    /**
     * Used for Url generation as second parameter of breadcrumb widget config
     * May be a callable which received $filter as arguments:
     *
     * function ($filter) {
     *     return $filter->owner->getUniqueId();
     * }
     *
     * Callable function should return full route to filter's owner, example:
     * Module 'users' is a submodule of 'admin', callable returns:
     *
     * 'admin/users' or null
     *
     * Returning null means what this breadcrumb shouldn't contain link,
     * what makes it active by widget
     *
     * @var string|callable
     */
    public $routeCreator = 'getUniqueId';

    /**
     * Identifies that breadcrumbs for this owner is already registered (for current request).
     * @var bool
     */
    private $ready = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (Yii::$app->controller instanceof WebController && !$this->ready) {
            $this->buildBreadcrumbs();
            $this->ready = true;
        }

        return parent::beforeAction($action);
    }

    /**
     * Appends owner's breadcrumb for current controller's view
     */
    protected function buildBreadcrumbs()
    {
        if (!empty($this->exceptRoutes) && $this->reject()) return;
        $params = $this->getBreadcrumbs();
        if (!empty($this->breadcrumbsKey)) {
            Yii::$app->controller->getView()->params[$this->breadcrumbsParam][$this->breadcrumbsKey] = $params;
        } else {
            Yii::$app->controller->getView()->params[$this->breadcrumbsParam][] = $params;
        }
    }

    /**
     * You can call this method directly, for example:
     *
     * ```
     * $breadcrumbItem = $module->getBreadcrumbs();
     * ```
     *
     * Note: this method returns breadcrumbs for owner
     * without checking property $exceptRoutes
     * @return array
     */
    public function getBreadcrumbs()
    {
        $breadcrumb = [
            'label' => $this->buildBreadcrumbLabel()
        ];
        if ($this->isActiveBreadcrumb()) {
            // for compapability with custom breadcrumbs widgets.
            $breadcrumb['options'] = ['class' => 'active'];
        } else {
            $breadcrumb['url'] = $this->buildBreadcrumbUrl();
        }

        return $breadcrumb;
    }

    /**
     * @return string
     * @throws \yii\base\UnknownPropertyException
     */
    protected function buildBreadcrumbLabel()
    {
        if (!empty($this->label)) {
            return $this->label;
        }
        if (!$this->owner->hasProperty($this->labelParam)) {
            throw new UnknownPropertyException("BreadcrumbsFilter's owner should provide property '"
                . $this->labelParam . "'");
        }

        return $this->owner->{$this->labelParam};
    }

    /**
     * @return null|string
     * @throws \yii\base\UnknownMethodException
     * @throws \yii\base\UnknownPropertyException
     */
    protected function buildBreadcrumbUrl()
    {
        return ($route = $this->buildBreadcrumbRoute()) ? Url::to(['/' . $route]) : null;
    }

    protected function isActiveBreadcrumb()
    {
        if ($this->defaultRoute === false) return false;
        if ($this->owner->id === Yii::$app->controller->module->id) {
            $relativeRoute = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

            return strpos($this->defaultRoute, $relativeRoute) !== false;
        }

        return false;
    }

    protected function buildBreadcrumbRoute()
    {
        if (is_string($this->routeCreator)) {
            if (!$this->owner->hasMethod('getUniqueId')) {
                throw new UnknownMethodException("BreadcrumbsFilter's owner should provide method 'getUniqueId'");
            }

            return $this->owner->{$this->routeCreator}();
        }
        if (is_callable($this->routeCreator)) {
            return call_user_func($this->routeCreator, $this);
        }

        throw new UnknownPropertyException("BreadcrumbsFilter's should be configured with valid routeCreator"
            . " (owner's method name or callable)");
    }

    /**
     * Rejects breadcrumbs creation for current route
     * Performs by sequentually checks of $exceptRoutes array
     */
    protected function reject()
    {
        foreach ($this->exceptRoutes as $route) {
            if ($route === '*' || strpos(Yii::$app->requestedRoute, $route) !== false) {
                return true;
            }
        }

        return false;
    }
}