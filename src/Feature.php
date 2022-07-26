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
     * @var string
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
     * @var string|null
     */
    private $requestParam;

    /**
     * @var array
     */
    private $data = array();

    /**
     * @param string $name
     * @param string|null $settings
     */
    public function __construct($name, $settings = null)
    {
        $this->name = $name;
        if ($settings) {
            $settings = explode('|', $settings);

            if (isset($settings[3])) {
                $rawRequestParam = $settings[3];
                $this->requestParam = $rawRequestParam;
            }

            //We can not trust the list function because of backwords compatibility
            if (isset($settings[4])) {
                $rawData = $settings[4];
                $this->data = !empty($rawData)? json_decode($rawData, true) : array();
            }

            list($rawPercentage, $rawUsers, $rawGroups) = $settings;
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
            implode(',', $this->groups),
            $this->requestParam,
            json_encode($this->data)
        ));
    }

    /**
     * @param RolloutUserInterface $user
     */
    public function addUser(RolloutUserInterface $user)
    {
        if (!in_array($user->getRolloutIdentifier(), $this->users)) {
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
     * @return string|null
     */
    public function getRequestParam()
    {
        return $this->requestParam;
    }

    /**
     * @param string|null $requestParam
     */
    public function setRequestParam($requestParam)
    {
        $this->requestParam = $requestParam;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Clear the feature of all configuration
     */
    public function clear()
    {
        $this->groups = array();
        $this->users = array();
        $this->percentage = 0;
        $this->requestParam = '';
        $this->data = array();
    }

    /**
     * Is the feature active?
     *
     * @param Rollout $rollout
     * @param RolloutUserInterface|null $user
     * @param array $requestParameters
     * @return bool
     */
    public function isActive(
        Rollout $rollout,
        RolloutUserInterface $user = null,
        array $users = array(),
        array $requestParameters = array()
    ) {
        if (null == $user) {
            return $this->isParamInRequestParams($requestParameters)
                || $this->percentage == 100
                || $this->isInActiveGroup($rollout);
        }

        return $this->isParamInRequestParams($requestParameters) ||
            $this->isUserInPercentage($user, $users) ||
            $this->isUserInActiveUsers($user) ||
            $this->isInActiveGroup($rollout, $user);
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
            'requestParam' => $this->requestParam,
            'data'=> $this->data
        );
    }

    /**
     * @param array $requestParameters
     * @return bool
     */
    private function isParamInRequestParams(array $requestParameters)
    {
        $param = explode('=', $this->requestParam);
        $key = array_shift($param);
        $value = array_shift($param);

        return $key && array_key_exists($key, $requestParameters) &&
            (empty($value) || $requestParameters[$key] == $value);
    }

    /**
     * @param RolloutUserInterface $user
     * @return bool
     */
    private function isUserInPercentage(RolloutUserInterface $user, array $users)
    {
        if ($this->percentage === 100) {
            return true;
        }

        if ($this->percentage === 0) {
            return false;
        }

        $limit = ceil(($this->percentage / 100) * count($users));
        $users = array_slice($users, 0, $limit);
        foreach ($users as $userData) {
            if ($userData['slug'] === $user->getRolloutIdentifier()) {
                return true;
            }
        }

        return false;
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
     * @param Rollout $rollout
     * @param RolloutUserInterface|null $user
     * @return bool
     */
    private function isInActiveGroup(Rollout $rollout, RolloutUserInterface $user = null)
    {
        foreach ($this->groups as $group) {
            if ($rollout->isActiveInGroup($group, $user)) {
                return true;
            }
        }

        return false;
    }
}
