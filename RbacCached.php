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
        $cached = Yii::$app->cache->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::checkAccess($userId, $permissionName);
            Yii::$app->cache->set($cacheKey, $cached, $this->lifetime);
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

        $cached = Yii::$app->cache->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::checkAccessRecursive($user, $itemName, $params, $assignments);
            Yii::$app->cache->set($cacheKey, $cached, $this->lifetime);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    protected function getItem($name) {
        $cacheKey = $this->cachePrefix . 'Item:' . $name;
        $cached = Yii::$app->cache->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::getItem($name);
            Yii::$app->cache->set($cacheKey, $cached, $this->lifetime);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    public function getAssignments($userId) {
        $cacheKey = $this->cachePrefix . 'Assignments:' . $userId;
        $cached = Yii::$app->cache->get($cacheKey);
        if ($cached === FALSE) {
            $cached = parent::getAssignments($userId);
            Yii::$app->cache->set($cacheKey, $cached, $this->lifetime);
        }
        return $cached;
    }

}
