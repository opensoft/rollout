<?php
/**
 * 
 */

use Opensoft\Rollout\Rollout;
use Opensoft\Rollout\Storage\ArrayStorage;
use Opensoft\Rollout\RolloutUserInterface;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class RolloutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Rollout
     */
    private $rollout;

    protected function setUp()
    {
        $this->rollout = new Rollout(new ArrayStorage());
    }

    public function testActiveForBlockGroup()
    {
        // When a group is activated
        $this->rollout->defineGroup('fivesonly', function(RolloutUserInterface $user) { return $user->getRolloutIdentifier() == 5; });
        $this->rollout->activateGroup('chat', 'fivesonly');

        // the feature is active for users for which the callback evaluates as true
        $this->assertTrue($this->rollout->isActive('chat', new RolloutUser(5)));

        // is not active for users for which the callback evalutates to false
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(1)));

        // is not active if a group is found in storage, but not defined in the rollout
        $this->rollout->activateGroup('chat', 'fake');
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(1)));
    }

    public function testDefaultAllGroup()
    {
        // the default all group
        $this->rollout->activateGroup('chat', 'all');

        // evaluates to true no matter what
        $this->assertTrue($this->rollout->isActive('chat', new RolloutUser(0)));
    }

    public function testDeactivatingAGroup()
    {
        $this->rollout->defineGroup('fivesonly', function(RolloutUserInterface $user) { return $user->getRolloutIdentifier() == 5; });
        $this->rollout->activateGroup('chat', 'all');
        $this->rollout->activateGroup('chat', 'some');
        $this->rollout->activateGroup('chat', 'fivesonly');
        $this->rollout->deactivateGroup('chat', 'all');
        $this->rollout->deactivateGroup('chat', 'some');

        // deactivates the rules for that group
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(10)));

        // leaves the other groups active
        $this->assertContains('fivesonly', $this->rollout->get('chat')->getGroups());
        $this->assertCount(1, $this->rollout->get('chat')->getGroups());
    }

    public function testDeactivatingAFeatureCompletely()
    {
        $this->rollout->defineGroup('fivesonly', function(RolloutUserInterface $user) { return $user->getRolloutIdentifier() === 5; });
        $this->rollout->activateGroup('chat', 'all');
        $this->rollout->activateGroup('chat', 'fivesonly');
        $this->rollout->activateUser('chat', new RolloutUser(51));
        $this->rollout->activatePercentage('chat', 100);
        $this->rollout->activateRequestParam('chat', 'FF_facebookIntegration=1');
        $this->rollout->activate('chat');
        $this->rollout->deactivate('chat');

        // it should remove all of the groups
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(0)));

        // it should remove all of the users
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(51)));

        // it should remove the percentage
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(24)));

        // it should remove the request param
        $this->assertFalse($this->rollout->isActive('chat', null, array('FF_facebookIntegration', true)));

        // it should be removed globally
        $this->assertFalse($this->rollout->isActive('chat'));
    }

    public function testActivatingASpecificUser()
    {
        $this->rollout->activateUser('chat', new RolloutUser(42));

        // it should be active for that user
        $this->assertTrue($this->rollout->isActive('chat', new RolloutUser(42)));

        // it remains inactive for other users
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(24)));
    }

    public function testActivatingASpecificUserWithStringId()
    {
        $this->rollout->activateUser('chat', new RolloutUser('user-72'));

        // it should be active for that user
        $this->assertTrue($this->rollout->isActive('chat', new RolloutUser('user-72')));

        // it remains inactive for other users
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser('user-12')));
    }

    public function testDeactivatingASpecificUser()
    {
        $this->rollout->activateUser('chat', new RolloutUser(42));
        $this->rollout->activateUser('chat', new RolloutUser(4242));
        $this->rollout->activateUser('chat', new RolloutUser(24));
        $this->rollout->deactivateUser('chat', new RolloutUser(42));
        $this->rollout->deactivateUser('chat', new RolloutUser('4242'));

        // that user should no longer be active
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(42)));

        // it remains active for other users
        $users = $this->rollout->get('chat')->getUsers();
        $this->assertCount(1, $users);
        $this->assertEquals(24, $users[0]);
    }

    public function testActivatingAFeatureGlobally()
    {
        $this->rollout->activate('chat');

        // it should activate the feature
        $this->assertTrue($this->rollout->isActive('chat'));
    }

    public function testActivatingAFeatureForPercentageOfUsers()
    {
        $this->rollout->activatePercentage('chat', 20);

        $activated = array();
        foreach (range(1, 120) as $id) {
            if ($this->rollout->isActive('chat', new RolloutUser($id))) {
                $activated[] = true;
            }
        }

        // it should activate the feature for a percentage of users
        $this->assertLessThanOrEqual(21, count($activated));
        $this->assertGreaterThanOrEqual(19, count($activated));
    }

    public function testActivatingAFeatureForPercentageOfUsers2()
    {
        $this->rollout->activatePercentage('chat', 20);

        $activated = array();
        foreach (range(1, 200) as $id) {
            if ($this->rollout->isActive('chat', new RolloutUser($id))) {
                $activated[] = true;
            }
        }

        // it should activate the feature for a percentage of users
        $this->assertLessThanOrEqual(45, count($activated));
        $this->assertGreaterThanOrEqual(35, count($activated));
    }

    public function testActivatingAFeatureForPercentageOfUsers3()
    {
        $this->rollout->activatePercentage('chat', 5);

        $activated = array();
        foreach (range(1, 100) as $id) {
            if ($this->rollout->isActive('chat', new RolloutUser($id))) {
                $activated[] = true;
            }
        }

        // it should activate the feature for a percentage of users
        $this->assertLessThanOrEqual(7, count($activated));
        $this->assertGreaterThanOrEqual(3, count($activated));
    }

    public function testActivatingAFeatureForAGroupAsAString()
    {
        $this->rollout->defineGroup('admins', function(RolloutUserInterface $user) { return $user->getRolloutIdentifier() == 5; });
        $this->rollout->activateGroup('chat', 'admins');

        // the feature is active for users for which the block is true
        $this->assertTrue($this->rollout->isActive('chat', new RolloutUser(5)));

        // the feature is not active for users for which the block evaluates to false
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(1)));
    }

    public function testDeactivatingThePercentageOfUsers()
    {
        $this->rollout->activatePercentage('chat', 100);
        $this->rollout->deactivatePercentage('chat');

        // it becomes inactive for all users
        $this->assertFalse($this->rollout->isActive('chat', new RolloutUser(24)));
    }

    public function testActivatingRequestParam()
    {
        $this->rollout->activateRequestParam('chat', 'FF_facebookIntegration=1');

        $this->assertTrue($this->rollout->isActive('chat', null, ['FF_facebookIntegration' => true]));

        $this->assertFalse($this->rollout->isActive('chat', null, ['FF_anotherFeature' => true]));
    }

    public function testDeactivatingRequestParam()
    {
        $this->rollout->activateRequestParam('chat', 'FF_facebookIntegration=1');
        $this->rollout->deactivateRequestParam('chat');

        $this->assertFalse($this->rollout->isActive('chat', null, ['FF_facebookIntegration' => true]));

        $this->assertFalse($this->rollout->isActive('chat', null, ['FF_anotherFeature' => true]));
    }

    public function testDeactivatingTheFeatureGlobally()
    {
        $this->rollout->activate('chat');
        $this->rollout->deactivate('chat');

        // inactive feature
        $this->assertFalse($this->rollout->isActive('chat'));
    }

    public function testKeepsAListOfFeatures()
    {
        // saves the feature
        $this->rollout->activate('chat');
        $this->assertContains('chat', $this->rollout->features());

        // does not contain doubles
        $this->rollout->activate('chat');
        $this->rollout->activate('chat');
        $this->assertCount(1, $this->rollout->features());
    }

    public function testGet()
    {
        $this->rollout->activatePercentage('chat', 10);
        $this->rollout->activateGroup('chat', 'caretakers');
        $this->rollout->activateGroup('chat', 'greeters');
        $this->rollout->activate('signup');
        $this->rollout->activateUser('chat', new RolloutUser(42));
        $this->rollout->activateRequestParam('chat', 'FF_facebookIntegration=1');

        // it should return the feature object
        $feature = $this->rollout->get('chat');
        $this->assertContains('caretakers', $feature->getGroups());
        $this->assertContains('greeters', $feature->getGroups());
        $this->assertEquals(10, $feature->getPercentage());
        $this->assertContains(42, $feature->getUsers());
        $this->assertEquals(
            array(
                'groups' => array('caretakers', 'greeters'),
                'percentage' => 10,
                'users' => array('42'),
                'requestParam' => 'FF_facebookIntegration=1'
            ),
            $feature->toArray()
        );

        $feature = $this->rollout->get('signup');
        $this->assertEmpty($feature->getGroups());
        $this->assertEmpty($feature->getUsers());
        $this->assertEquals(100, $feature->getPercentage());
        $this->assertEmpty($feature->getRequestParam());
    }

    public function testRemove()
    {
        $this->rollout->activate('signup');
        $feature = $this->rollout->get('signup');
        $this->assertEquals('signup', $feature->getName());

        $this->rollout->remove('signup');
        $this->assertNotContains('signup', $this->rollout->features());
    }
}


/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class RolloutUser implements RolloutUserInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getRolloutIdentifier()
    {
        return $this->id;
    }
}
