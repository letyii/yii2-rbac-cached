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
use yii\helpers\ArrayHelper;

class RbacCached extends DbManager {

    /**
     * @var string the ID of the cache application component that is used to cache rbac.
     * Defaults to 'cache' which refers to the primary cache application component.
     */
    public $cache = 'cache';

    /**
     * @var integer Lifetime of cached data in seconds
     */
    public $cacheDuration = 3600;

    /**
     * @var string cache key name
     */
    public $cacheKeyName = 'RbacCached';

    /**
     * @var array php cache
     */
    protected $cachedData = [];

    /**
     * @inheritdoc
     */
    public function checkAccess($userId, $permissionName, $params = []) {
        if (!empty($params))
            return parent::checkAccess($userId, $permissionName, $params);

        $cacheKey = 'checkAccess:' . $userId . ':' . $permissionName;
        $cached = $this->getCache($cacheKey);
        if (empty($cached)) {
            $cached = parent::checkAccess($userId, $permissionName);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments) {
        $cacheKey = 'checkAccessRecursive:' . $user . ':' . $itemName;
        if (!empty($params))
            $cacheKey .= ':' . current($params)->primaryKey;

        $cached = $this->getCache($cacheKey);
        if (empty($cached)) {
            $cached = parent::checkAccessRecursive($user, $itemName, $params, $assignments);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    protected function getItem($name) {
        $cacheKey = 'Item:' . $name;
        $cached = $this->getCache($cacheKey);
        if (empty($cached)) {
            $cached = parent::getItem($name);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    public function getAssignments($userId) {
        if (empty($userId))
            return parent::getAssignments($userId);

        $cacheKey = 'Assignments:' . $userId;
        $cached = $this->getCache($cacheKey);
        if (empty($cached)) {
            $cached = parent::getAssignments($userId);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * Set a value in cache
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function setCache($key, $value) {
        $this->cachedData = $this->resolveCacheComponent()->get($this->cacheKeyName);
        if (empty($this->cachedData))
            $this->cachedData = [];
        $this->cachedData[$key] = $value;
        return $this->resolveCacheComponent()->set($this->cacheKeyName, $this->cachedData, $this->cacheDuration);
    }

    /**
     * Get cached value
     * @param $key
     * @return mixed
     */
    protected function getCache($key) {
        $cached = ArrayHelper::getValue($this->cachedData, $key);
        if (!isset($cached)) {
            $cacheData = $this->resolveCacheComponent()->get($this->cacheKeyName);
            $cached = $this->cachedData[$key] = ArrayHelper::getValue($cacheData, $key);
        }
        return $cached;
    }

    /**
     * Get cached value
     * @param $key
     * @return mixed
     */
    public static function deleteAllCache() {
        return $this->resolveCacheComponent()->delete($this->cacheKeyName);
    }

    /**
     * Returns cache component configured as in cacheId
     * @return Cache
     */
    protected function resolveCacheComponent() {
        return Yii::$app->get($this->cache);
    }
}
