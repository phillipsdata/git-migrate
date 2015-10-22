<?php
namespace PhillipsData\GitMigrate;

use InvalidArgumentException;
use stdClass;

/**
 * Simple JSON Config parser
 */
class Config
{
    /**
     * @var object Config data
     */
    protected $data;

    /**
     * Fetches the all config options
     *
     * @return object config options
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * Set of overwrite a particular config option
     *
     * @param string $key
     * @param string|object|array $value
     * @return \PhillipsData\GitMigrate\Config
     */
    public function set($key, $value)
    {
        if (!is_object($this->data)) {
            $this->data = new stdClass();
        }
        $this->data->{$key} = $value;
        return $this;
    }

    /**
     * Fetches a particular config option
     *
     * @param string $key
     * @return string|object|array $value
     */
    public function get($key)
    {
        if ($this->data === null || !property_exists($this->data, $key)) {
            return null;
        }
        return $this->data->{$key};
    }

    /**
     * Loads the config file into memory
     *
     * @param string $filename
     * @return \PhillipsData\GitMigrate\Config
     */
    public function load($filename)
    {
        $this->parse($filename);
        return $this;
    }

    /**
     * Parses the file as JSON
     *
     * @param string $filename
     * @throws InvalidArgumentException
     */
    protected function parse($filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(
                sprintf('File not found: %s', $filename)
            );
        }
        $this->data = json_decode(file_get_contents($filename));
    }
}
