<?php

/**
 * @link http://www.letyii.com/
 * @copyright Copyright (c) 2014 Let.,ltd
 * @license https://github.com/letyii/cms/blob/master/LICENSE
 * @author Ngua Go <nguago@let.vn>
 */

namespace letyii\rbaccached;

use Yii;
use yii\rbac\DbManager;

class RbacCached extends DbManager {

    /**
     * @var integer Lifetime of cached data in seconds
     */
    public $lifetime = 3600;

    /**
     * @var string cache prefix to ovoid collisions
     */
    public $cachePrefix = 'RbacCached_';
    /**
	 * @var string the ID of the cache application component that is used to cache rbac.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 */
	public $cacheID='cache';

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($userId, $permissionName, $params = []) {
        if (!empty($params))
            return parent::checkAccess($userId, $permissionName, $params);

        $cacheKey = $this->cachePrefix . 'checkAccess:' . $userId . ':' . $permissionName;
        $cached = $this->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::checkAccess($userId, $permissionName);
            $this->set($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments) {
        $cacheKey = $this->cachePrefix . 'checkAccessRecursive:' . $user . ':' . $itemName;
        if (!empty($params))
            $cacheKey .= ':' . current($params)->primaryKey;

        $cached = $this->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::checkAccessRecursive($user, $itemName, $params, $assignments);
            $this->set($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    protected function getItem($name) {
        $cacheKey = $this->cachePrefix . 'Item:' . $name;
        $cached = $this->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::getItem($name);
            $this->set($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    public function getAssignments($userId) {
        $cacheKey = $this->cachePrefix . 'Assignments:' . $userId;
        $cached = $this->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::getAssignments($userId);
            $this->set($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * Set a value in cache
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function cache($key, $value)
    {
        $cacheComponent  = $this->resolveCacheComponent();
        return $cacheComponent->set($key, $value, $this->lifetime);
    }

    /**
     * Get cached value
     * @param $key
     * @return mixed
     */
    protected function get($key)
    {
        $cacheComponent  = $this->resolveCacheComponent();
        return $cacheComponent->get($key);
    }

    /**
     * Returns cache component configured as in cacheId
     * @return CCache
     */
    protected function resolveCacheComponent()
    {
        return Yii::$app->{$this->cacheId};
    }
}
