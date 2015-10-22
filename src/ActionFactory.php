<?php
namespace PhillipsData\GitMigrate;

class ActionFactory
{
    /**
     * Creates an instance of the given action
     *
     * @param string $action
     * @return \PhillipsData\GitMigrate\Actions\ActionInterface
     */
    public function create($action)
    {
        $fqcn = '\\PhillipsData\\GitMigrate\\Actions\\' . $action;
        return new $fqcn();
    }
}
