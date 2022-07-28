<?php
/**
 *
 */

namespace Opensoft\Rollout;

use Opensoft\Rollout\Storage\StorageInterface;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class Rollout
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    private $groups;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
        $this->groups = array(
            'all' => function(RolloutUserInterface $user) { return $user !== null; }
        );
    }

    /**
     * @param string $feature
     */
    public function activate($feature)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->setPercentage(100);
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     */
    public function deactivate($feature)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->clear();
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     * @param string $group
     */
    public function activateGroup($feature, $group)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->addGroup($group);
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     * @param string $group
     */
    public function deactivateGroup($feature, $group)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->removeGroup($group);
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     * @param RolloutUserInterface $user
     */
    public function activateUser($feature, RolloutUserInterface $user)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->addUser($user);
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     * @param RolloutUserInterface $user
     */
    public function deactivateUser($feature, RolloutUserInterface $user)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->removeUser($user);
            $this->save($feature);
        }
    }

    /**
     * @param string $group
     * @param \Closure $closure
     */
    public function defineGroup($group, \Closure $closure)
    {
        $this->groups[$group] = $closure;
    }

    /**
     * @param string $feature
     * @param RolloutUserInterface|null $user
     * @param array $requestParameters
     * @return bool
     */
    public function isActive(
        $feature,
        RolloutUserInterface $user = null,
        array $users = array(),
        array $requestParameters = array()
    ) {
        $feature = $this->get($feature);

        return $feature && $feature->isActive($this, $user, $users, $requestParameters);
    }

    /**
     * @param string $feature
     * @param integer $percentage
     */
    public function activatePercentage($feature, $percentage)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->setPercentage($percentage);
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     */
    public function deactivatePercentage($feature)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->setPercentage(0);
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     * @param string $requestParam
     */
    public function activateRequestParam($feature, $requestParam)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->setRequestParam($requestParam);
            $this->save($feature);
        }
    }

    /**
     * @param string $feature
     */
    public function deactivateRequestParam($feature)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->setRequestParam('');
            $this->save($feature);
        }
    }

    /**
     * @param string $group
     * @param RolloutUserInterface|null $user
     * @return bool
     */
    public function isActiveInGroup($group, RolloutUserInterface $user = null)
    {
        if (!isset($this->groups[$group])) {
            return false;
        }

        $g = $this->groups[$group];

        return $g && $g($user);
    }

    /**
     * @param string $feature
     * @return Feature
     */
    public function get($feature)
    {
        $settings = $this->storage->get($this->key($feature));

        if (!empty($settings)) {
            $f = new Feature($feature, $settings);
        } else {
            $f = new Feature($feature);

            $this->save($f);
        }

        return $f;
    }

    /**
     * Remove a feature definition from rollout
     *
     * @param string $feature
     */
    public function remove($feature)
    {
        $this->storage->remove($this->key($feature));

        $features = $this->features();
        if (in_array($feature, $features)) {
            $features = array_diff($features, array($feature));
        }
        $this->storage->set($this->featuresKey(), implode(',', $features));
    }

    /**
     * Update feature specific data
     *
     * @example $rollout->setFeatureData('chat', array(
     *  'description'  => 'foo',
     *  'release_date' => 'bar',
     *  'whatever'     => 'baz'
     * ));
     *
     * @param string $feature
     * @param array  $data
     */
    public function setFeatureData($feature, array $data)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->setData(array_merge($feature->getData(), $data));
            $this->save($feature);
        }
    }

    /**
     * Clear all feature data
     *
     * @param  string $feature
     */
    public function clearFeatureData($feature)
    {
        $feature = $this->get($feature);
        if ($feature) {
            $feature->setData(array());
            $this->save($feature);
        }
    }

    /**
     * @return array
     */
    public function features()
    {
        $content = $this->storage->get($this->featuresKey());

        if (!empty($content)) {
            return explode(',', $content);
        }

        return array();
    }

    /**
     * @param string $name
     * @return string
     */
    private function key($name)
    {
        return 'feature:' . $name;
    }

    /**
     * @return string
     */
    private function featuresKey()
    {
        return 'feature:__features__';
    }

    /**
     * @param Feature $feature
     */
    public function save(Feature $feature)
    {
        $name = $feature->getName();
        $this->storage->set($this->key($name), $feature->serialize());

        $features = $this->features();
        if (!in_array($name, $features)) {
            $features[] = $name;
        }
        $this->storage->set($this->featuresKey(), implode(',', $features));
    }
}
