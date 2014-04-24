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
     * @param string      $feature
     * @param RolloutUserInterface|null $user
     * @return bool
     */
    public function isActive($feature, RolloutUserInterface $user = null)
    {
        $feature = $this->get($feature);

        return $feature ? $feature->isActive($this, $user) : false;
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
     * @param string $group
     * @param RolloutUserInterface $user
     * @return bool
     */
    public function isActiveInGroup($group, RolloutUserInterface $user)
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
     * Configures and saves a feature.
     *
     * @param Feature $feature    The feature to be configured
     * @param bool    $activate   Should users and groups be activated or deactivated (it has no effect on percentage)
     * @param array   $users      A list of objects implementing RolloutUserInterface
     * @param array   $groups     A list of group names
     * @param int     $percentage New percentage
     *
     * @return array An array which holds users and groups who have been effectively modified
     */
    public function configure(Feature $feature, $activate, array $users, array $groups, $percentage = null)
    {
        $modified = array('users' => array(), 'groups' => array());

        // Percentage
        if (null !== $percentage) {
            $feature->setPercentage($percentage);
        }

        // Users
        foreach ($users as $user) {
            $method = 'removeUser';
            if (true === $activate) {
                $method = 'addUser';
            }
            if (true === call_user_func(array($feature, $method), $user)) {
                $modified['users'][] = $user->getRolloutIdentifier();
            }
        }

        // Groups
        foreach ($groups as $group) {
            $method = 'removeGroup';
            if (true === $activate) {
                $method = 'addGroup';
            }
            if (true === call_user_func(array($feature, $method), $group)) {
                $modified['groups'][] = $group;
            }
        }

        // Save feature
        $this->save($feature);

        return $modified;
    }

    /**
     * @param Feature $feature
     */
    private function save(Feature $feature)
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
