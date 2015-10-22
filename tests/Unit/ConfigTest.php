<?php
namespace PhillipsData\GitMigrate\Tests\Unit;

use PHPUnit_Framework_TestCase;
use PhillipsData\GitMigrate\Config;

/**
 * @coversDefaultClass \PhillipsData\GitMigrate\Config
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    private $config;
    private $fixtureDir;

    public function setUp()
    {
        $this->config = new Config();
        $this->fixtureDir = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR;
    }

    /**
     * @covers ::getAll
     * @covers ::set
     * @dataProvider configProvider
     */
    public function testGetAll($options)
    {
        $this->assertNull($this->config->getAll());

        foreach ($options as $key => $value) {
            $this->config->set($key, $value);
        }

        $this->assertEquals((object) $options, $this->config->getAll());
    }

    /**
     * @covers ::get
     * @covers ::set
     * @dataProvider configProvider
     */
    public function testGet($options)
    {
        $this->assertNull($this->config->get('invalidKey'));

        foreach ($options as $key => $value) {
            $this->config->set($key, $value);
            $this->assertEquals($value, $this->config->get($key));
        }
    }

    /**
     * Data provider for setting config options
     *
     * @return array
     */
    public function configProvider()
    {
        return [
            [
                [
                    'dir' => '/path/to/',
                    'url' => 'svn://svn.yourdomain.com/'
                ]
            ]
        ];
    }

    /**
     * @covers ::load
     * @covers ::parse
     * @covers ::get
     */
    public function testLoad()
    {
        $filename = $this->fixtureDir . 'config.json';
        $this->config->load($filename);

        $this->assertCount(2, $this->config->get('repositories'));
    }

    /**
     * @covers ::load
     * @covers ::parse
     * @expectedException \InvalidArgumentException
     */
    public function testLoadException()
    {
        $filename = $this->fixtureDir . 'nonexistentfile.json';
        $this->config->load($filename);
    }
}
