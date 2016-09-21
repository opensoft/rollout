<?php

use Opensoft\Rollout\Feature;

/**
 * @author Filip Golonka <filipgolonka@gmail.com>
 */
class FeatureTest extends \PHPUnit_Framework_TestCase
{
    public function testParseOldSettingsFormat()
    {
        $feature = new Feature('chat', '100|4,12|fivesonly');

        $this->assertEquals(100, $feature->getPercentage());
        $this->assertEquals([4, 12], $feature->getUsers());
        $this->assertEquals(['fivesonly'], $feature->getGroups());
    }

    public function testParseNewSettingsFormat()
    {
        $feature = new Feature('chat', '100|4,12|fivesonly|FF_facebookIntegration=1');

        $this->assertEquals(100, $feature->getPercentage());
        $this->assertEquals([4, 12], $feature->getUsers());
        $this->assertEquals(['fivesonly'], $feature->getGroups());
        $this->assertEquals('FF_facebookIntegration=1', $feature->getRequestParam());
    }
}
