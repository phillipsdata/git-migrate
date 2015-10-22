<?php
namespace PhillipsData\GitMigrate\Tests\Unit;

use PHPUnit_Framework_TestCase;
use PhillipsData\GitMigrate\GitMigrate;
use PhillipsData\GitMigrate\Actions\ActionInterface;

/**
 * @coversDefaultClass \PhillipsData\GitMigrate\GitMigrate
 */
class GitMigrateTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers ::convertToRepos
     * @covers ::__construct
     * @dataProvider convertToReposProvider
     * @param array $items
     * @param array $expected
     */
    public function testConvertToRepos(array $items, $expected)
    {
        $gitMigrate = new GitMigrate(null, null, null, null);

        $repos = [];
        $gitMigrate->convertToRepos($items, null, $repos);
        $this->assertEquals($expected, $repos);
    }

    /**
     * Data provider for testConvertToRepos
     *
     * @return array
     */
    public function convertToReposProvider()
    {
        return [
            [
                [
                    'my-external-dir' => [
                        'another-dir' => [
                            'repo1',
                            'repo2'
                        ]
                    ],
                    'repo3',
                    'my-other-external-dir' => [
                        'repo4'
                    ]
                ],
                [
                    (object)[
                        'path' => 'my-external-dir' . DIRECTORY_SEPARATOR
                            . 'another-dir' . DIRECTORY_SEPARATOR . 'repo1'
                    ],
                    (object)[
                        'path' => 'my-external-dir' . DIRECTORY_SEPARATOR
                            . 'another-dir' . DIRECTORY_SEPARATOR . 'repo2'
                    ],
                    (object)[
                        'path' => 'repo3'
                    ],
                    (object)[
                        'path' => 'my-other-external-dir' . DIRECTORY_SEPARATOR
                            . 'repo4'
                    ]
                ]
            ]
        ];
    }

    /**
     * @covers ::getActionFactory
     * @covers ::setActionFactory
     * @covers ::__construct
     */
    public function testActionFactory()
    {
        $gitMigrate = new GitMigrate(null, null, null, null);
        $this->assertInstanceOf(
            '\\PhillipsData\\GitMigrate\\ActionFactory',
            $gitMigrate->getActionFactory()
        );

        $factoryMock = $this->getMockBuilder('\\PhillipsData\\GitMigrate\\ActionFactory')
            ->getMock();

        $gitMigrate->setActionFactory($factoryMock);

        $this->assertSame($factoryMock, $gitMigrate->getActionFactory());
    }

    /**
     * @covers ::process
     */
    public function testProcess()
    {
        $dir = '/path/to/dir';
        $path = 'repo/path';
        $repos = [];
        $action = 'sync';

        $stub = $this->getMockBuilder('\\PhillipsData\\GitMigrate\\GitMigrate')
            ->disableOriginalConstructor()
            ->setMethods(['convertToRepos', 'processRepositories'])
            ->getMock();
        $stub->expects($this->once())
            ->method('convertToRepos')
            ->with(
                $this->equalTo($dir),
                $this->equalTo($path),
                $this->equalTo($repos)
            );
        $stub->expects($this->once())
            ->method('processRepositories')
            ->with(
                $this->equalTo($repos),
                $this->equalTo($action)
            );

        $stub->process($dir, $path, $action);
    }

    /**
     * @covers ::processRepositories
     * @covers ::__construct
     * @covers ::createDir
     * @expectedException \InvalidArgumentException
     */
    public function testProcessRepositoryException()
    {
        $gitMigrate = new GitMigrate(dirname(__DIR__), null, null, null);
        $gitMigrate->processRepositories(
            [(object)['path' => basename(__DIR__)]],
            'not-a-real-action'
        );
    }

    /**
     * @covers ::__construct
     * @covers ::processRepositories
     * @covers ::createDir
     * @covers ::cloneRepo
     * @covers ::cleanupRepo
     * @covers ::setActionFactory
     * @covers ::getActionFactory
     * @uses \PhillipsData\GitMigrate\Actions\CloneRepo
     * @uses \PhillipsData\GitMigrate\Actions\CleanupRepo
     */
    public function testProcessRepositoryClone()
    {
        $gitMigrate = new GitMigrate(dirname(__DIR__), null, null, null);
        $gitMigrate->setActionFactory($this->getMockFactory());

        $gitMigrate->processRepositories(
            [(object)['path' => basename(__DIR__)]],
            'clone'
        );
    }

    /**
     * @covers ::__construct
     * @covers ::processRepositories
     * @covers ::createDir
     * @covers ::syncRepo
     * @covers ::cleanupRepo
     * @covers ::setActionFactory
     * @covers ::getActionFactory
     * @uses \PhillipsData\GitMigrate\Actions\SyncRepo
     * @uses \PhillipsData\GitMigrate\Actions\CleanupRepo
     */
    public function testProcessRepositorySync()
    {
        $gitMigrate = new GitMigrate(dirname(__DIR__), null, null, null);
        $gitMigrate->setActionFactory($this->getMockFactory());

        $gitMigrate->processRepositories(
            [(object)['path' => basename(__DIR__)]],
            'sync'
        );
    }

    /**
     * @covers ::__construct
     * @covers ::processRepositories
     * @covers ::createDir
     * @covers ::pushRepo
     * @covers ::setActionFactory
     * @covers ::getActionFactory
     * @uses \PhillipsData\GitMigrate\Actions\PushRepo
     */
    public function testProcessRepositoryPush()
    {
        $gitMigrate = new GitMigrate(dirname(__DIR__), null, null, null);
        $gitMigrate->setActionFactory($this->getMockFactory());

        $gitMigrate->processRepositories(
            [
                (object)['path' => basename(__DIR__), 'origin' => 'https://domain.com/repo.git'],
                (object)['path' => basename(__DIR__)]
            ],
            'push'
        );
    }

    private function getMockFactory()
    {
        $factoryMock = $this->getMockBuilder('\\PhillipsData\\GitMigrate\\ActionFactory')
            ->getMock();
        $factoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function ($action) {
                $actionMock = $this->getMockBuilder('\\PhillipsData\\GitMigrate\\Actions\\' . $action)
                    ->setMethods(['process'])
                    ->getMock();
                $actionMock->expects($this->once())
                    ->method('process');
                return $actionMock;
            }));

        return $factoryMock;
    }
    /*
    public function testProcessRepositorySync()
    {

    }

    public function testProcessRepositoryPush()
    {

    }
     *
     */
}
