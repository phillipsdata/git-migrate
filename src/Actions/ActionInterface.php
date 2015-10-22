<?php
namespace PhillipsData\GitMigrate\Actions;

interface ActionInterface
{
    /**
     * Proces the action
     *
     * @return int The status of running the action process
     */
    public function process();
}
