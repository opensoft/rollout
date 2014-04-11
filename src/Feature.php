<?php
/**
 *
 */

namespace Opensoft\Rollout;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class Feature
{
    /**
     * @var array
     */
    private $name;

    /**
     * @var array
     */
    private $groups = array();

    /**
     * @var array
     */
    private $users = array();

    /**
     * @var integer
     */
    private $percentage = 0;

    /**
     * @param string $name
     * @param string|null $settings
     */
    public function __construct($name, $settings = null)
    {
        $this->name = $name;
        if ($settings) {
            list($rawPercentage, $rawUsers, $rawGroups) = explode('|', $settings);
            $this->percentage = (int) $rawPercentage;
            $this->users = !empty($rawUsers) ? explode(',', $rawUsers) : array();
            $this->groups = !empty($rawGroups) ? explode(',', $rawGroups) : array();
        } else {
            $this->clear();
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param integer $percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    /**
     * @return integer
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return implode('|', array(
            $this->percentage,
            implode(',', $this->users),
            implode(',', $this->groups)
        ));
    }

    /**
     * @param RolloutUserInterface $user
     */
    public function addUser(RolloutUserInterface $user)
    {
        if (!in_array($user, $this->users)) {
            $this->users[] = $user->getRolloutIdentifier();
        }
    }

    /**
     * @param RolloutUserInterface $user
     */
    public function removeUser(RolloutUserInterface $user)
    {
        if (($key = array_search($user->getRolloutIdentifier(), $this->users)) !== false) {
            unset($this->users[$key]);
        }
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param string $group
     */
    public function addGroup($group)
    {
        if (!in_array($group, $this->groups)) {
            $this->groups[] = $group;
        }
    }

    /**
     * @param string $group
     */
    public function removeGroup($group)
    {
        if (($key = array_search($group, $this->groups)) !== false) {
            unset($this->groups[$key]);
        }
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Clear the feature of all configuration
     */
    public function clear()
    {
        $this->groups = array();
        $this->users = array();
        $this->percentage = 0;
    }

    /**
     * Is the feature active?
     *
     * @param Rollout $rollout
     * @param RolloutUserInterface|null $user
     * @return bool
     */
    public function isActive(Rollout $rollout, RolloutUserInterface $user = null)
    {
        if (null == $user) {
            return $this->percentage == 100;
        }

        return $this->isUserInPercentage($user) || $this->isUserInActiveUsers($user) || $this->isUserInActiveGroup($user, $rollout);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'percentage' => $this->percentage,
            'groups' => $this->groups,
            'users' => $this->users,
        );
    }

    /**
     * @param RolloutUserInterface $user
     * @return bool
     */
    private function isUserInPercentage(RolloutUserInterface $user)
    {
        return abs(crc32($user->getRolloutIdentifier()) % 100) < $this->percentage;
    }

    /**
     * @param RolloutUserInterface $user
     * @return boolean
     */
    private function isUserInActiveUsers(RolloutUserInterface $user)
    {
        return in_array($user->getRolloutIdentifier(), $this->users);
    }

    /**
     * @param RolloutUserInterface $user
     * @param Rollout $rollout
     * @return bool
     */
    private function isUserInActiveGroup(RolloutUserInterface $user, Rollout $rollout)
    {
        foreach ($this->groups as $group) {
            if ($rollout->isActiveInGroup($group, $user)) {
                return true;
            }
        }

        return false;
    }
}
