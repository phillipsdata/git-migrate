<?php
namespace PhillipsData\GitMigrate;

use InvalidArgumentException;

/**
 * Migrates a repo from SVN to git with the help of Atlassian's migration script
 * according to https://www.atlassian.com/git/tutorials/migrating-convert
 *
 * This utility can automate the process of migrating very many sub-repos into
 * their own separate repositories.
 */
class GitMigrate
{
    /**
     * @var string
     */
    protected $rootDir;
    /**
     * @var string
     */
    protected $authorsFile;
    /**
     * @var string
     */
    protected $svnUrl;
    /**
     * @var string
     */
    protected $migrationLib;
    /**
     * @var \PhillipsData\GitMigrate\ActionFactory
     */
    protected $actionFactory;

    /**
     * Initialize
     *
     * @param string $rootDir The root directory on your local file system to start the migration
     * @param string $authorsFile The full path to your authors file
     * @param string $svnUrl The URL to your SVN repository
     * @param string $migrationLib The
     */
    public function __construct($rootDir, $authorsFile, $svnUrl, $migrationLib)
    {
        $this->rootDir = $rootDir;
        $this->authorsFile = $authorsFile;
        $this->svnUrl =  rtrim($svnUrl, '/') . '/';
        $this->migrationLib = $migrationLib;
    }

    /**
     * Set the ActionFactory to use
     *
     * @param \PhillipsData\GitMigrate\ActionFactory $factory
     */
    public function setActionFactory(ActionFactory $factory)
    {
        $this->actionFactory = $factory;
    }

    /**
     * Fetches the ActionFactory in use
     *
     * @return \PhillipsData\GitMigrate\ActionFactory
     */
    public function getActionFactory()
    {
        if (null === $this->actionFactory) {
            $this->actionFactory = new ActionFactory();
        }
        return $this->actionFactory;
    }

    /**
     * Processes the given repositories
     *
     * @param array $repos The repositories to process
     * @param string $action The action to perform on the repos
     * @throws InvalidArgumentException
     */
    public function processRepositories(array $repos, $action = 'clone')
    {
        foreach ($repos as $repo) {
            $fullPath = $this->rootDir . DIRECTORY_SEPARATOR . $repo->path;
            if (!$this->createDir($fullPath)) {
                continue;
            }

            switch ($action) {
                case 'clone':
                    $this->cloneRepo(
                        $fullPath,
                        $repo->path,
                        $this->authorsFile,
                        $this->migrationLib,
                        $this->svnUrl
                    );
                    break;
                case 'sync':
                    $this->syncRepo(
                        $fullPath,
                        $repo->path,
                        $this->authorsFile,
                        $this->migrationLib
                    );
                    break;
                case 'push':
                    $this->pushRepo(
                        $fullPath,
                        !empty($repo->origin)
                        ? $repo->origin
                        : null
                    );
                    break;
                default:
                    throw new InvalidArgumentException(
                        sprintf("'%s' is not a valid action.", $action)
                    );
            }
        }
    }

    /**
     * Clone the repository
     *
     * @param string $dir
     * @param string $path
     * @param string $authorsFile
     * @param string $migrationLib
     * @param string $svnUrl
     */
    private function cloneRepo($dir, $path, $authorsFile, $migrationLib, $svnUrl)
    {
        $clone = $this->getActionFactory()->create('CloneRepo');
        $clone->setDir($dir)
            ->setAuthorsFile($this->authorsFile)
            ->setSvnUrl($this->svnUrl)
            ->setPath($path)
            ->process();

        $this->cleanupRepo($dir, $migrationLib);
    }

    /**
     * Sync the repository
     *
     * @param string $dir
     * @param string $path
     * @param string $authorsFile
     * @param string $migrationLib
     */
    private function syncRepo($dir, $path, $authorsFile, $migrationLib)
    {
        $sync = $this->getActionFactory()->create('SyncRepo');
        $sync->setDir($dir)
            ->setAuthorsFile($this->authorsFile)
            ->setMigrationLib($this->migrationLib)
            ->process();

        $this->cleanupRepo($dir, $migrationLib);
    }

    /**
     * Push the repo to the remote origin
     *
     * @param string $dir
     * @param string $originUrl
     */
    private function pushRepo($dir, $originUrl)
    {
        $push = $this->getActionFactory()->create('PushRepo');
        $push->setDir($dir)
            ->setOrigin($originUrl)
            ->process();
    }

    /**
     * Cleanup the repository
     *
     * @param string $dir
     * @param string $migrationLib
     */
    private function cleanupRepo($dir, $migrationLib)
    {
        $cleanup = $this->getActionFactory()->create('CleanupRepo');
        $cleanup->setDir($dir)
            ->setMigrationLib($migrationLib)
            ->process();
    }

    /**
     * Process the directory
     *
     * @param string $dir The directory to process
     * @param string $path The path to this directory
     * @param string $action 'clone' or 'sync'
     * @deprecated since 1.2.0
     */
    public function process($dir, $path = null, $action = 'clone')
    {
        $repos = [];
        $this->convertToRepos($dir, $path, $repos);
        $this->processRepositories($repos, $action);
    }

    /**
     * Convert item array paths to repositories
     *
     * @param array|string $dir
     * @param string $path
     * @param array $repos
     * @return object|null
     */
    public function convertToRepos($dir, $path, array &$repos)
    {
        $baseDir = trim($path) === ''
            ? null
            : rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_array($dir)) {
            return (object) ['path' => $baseDir . $dir];
        }

        foreach ($dir as $subdir => $item) {
            if (is_int($subdir)) {
                $subdir = null;
            }
            $repo = $this->convertToRepos(
                $item,
                $baseDir . $subdir,
                $repos
            );

            if ($repo !== null) {
                $repos[] = $repo;
            }
        }
        return null;
    }

    /**
     * Attempt to create the directory if it doesn't already exist
     *
     * @param string $dir
     * @return boolean False if the directory could not be created
     */
    protected function createDir($dir)
    {
        if (!is_dir($dir)) {
            fwrite(STDERR, sprintf("%s is not a directory. Creating...\n", $dir));
            mkdir($dir, 0644, true);
            if (!is_dir($dir)) {
                fwrite(STDERR, sprintf("Could not create %s. Skipping.\n", $dir));
                return false;
            }
        }
        return true;
    }
}
