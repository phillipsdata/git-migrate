<?php
namespace PhillipsData\GitMigrate\Tests\Unit;

use PHPUnit_Framework_TestCase;
use PhillipsData\GitMigrate\ActionFactory;

/**
 * @coversDefaultClass \PhillipsData\GitMigrate\ActionFactory
 */
class ActionFactoryTest extends PHPUnit_Framework_TestCase
{
    private $actionFactory;

    public function setUp()
    {
        $this->actionFactory = new ActionFactory();
    }

    /**
     * @covers ::create
     * @dataProvider createProvider
     * @param string $action
     */
    public function testCreate($action)
    {
        $fqcn = '\\PhillipsData\\GitMigrate\\Actions\\' . $action;

        $this->assertInstanceOf($fqcn, $this->actionFactory->create($action));
    }

    public function createProvider()
    {
        return [
            ['CloneRepo'],
            ['SyncRepo'],
            ['PushRepo'],
            ['CleanupRepo']
        ];
    }
}
