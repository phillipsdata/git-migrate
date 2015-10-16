<?php
namespace PhillipsData\GitMigrate;

/**
 * Migrates a repo from SVN to git with the help of Atlassian's migration script
 * according to https://www.atlassian.com/git/tutorials/migrating-convert
 *
 * This utility can automate the process of migrating very many sub-repos into
 * their own separate repositories.
 */
class GitMigrate
{
    protected $rootDir;
    protected $authorsFile;
    protected $svnUrl;
    protected $migrationLib;

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
     * Process the directory
     *
     * @param string $dir The directory to process
     * @param string $path The path to this directory
     * @param string $action 'clone' or 'sync'
     */
    public function process($dir, $path = null, $action = 'clone')
    {
        if (is_array($dir)) {
            foreach ($dir as $subdir => $item) {
                if (is_int($subdir)) {
                    $subdir = null;
                }
                $this->process($item, $path . DIRECTORY_SEPARATOR . $subdir);
            }
            return;
        }

        $fullPath = $this->rootDir . DIRECTORY_SEPARATOR . $path;

        if (!is_dir($fullPath)) {
            fwrite(STDERR, sprintf("%s is not a directory. Creating...\n", $fullPath));
            mkdir($fullPath, 0644, true);
            if (!is_dir($fullPath)) {
                fwrite(STDERR, sprintf("Could not create %s. Skipping.\n", $fullPath));
                return;
            }
        }

        fwrite(STDOUT, sprintf("\n----------\nProcessing %s...\n", $dir));

        if ('clone' === $action) {
            $this->cloneRepo($fullPath, $dir, $path);
        } elseif ('sync' === $action) {
            $this->syncRepo($fullPath);
        }
        $this->cleanup($dir);
    }

    /**
     * Clone an SVN repo into a git repo
     *
     * @param string $dir The directory to execute the command under
     * @param string $subdir The subdirectory to use for the repository
     * @param string $path The path within the SVN URL to clone from
     * @return int The return status of executing the command
     */
    protected function cloneRepo($dir, $subdir, $path = null)
    {
        $status = 0;
        chdir($dir);

        system(
            sprintf(
                'git svn clone --stdlayout --authors-file=%s %s %s',
                escapeshellarg($this->authorsFile),
                escapeshellarg($this->svnUrl . str_replace('\\', '/', $path) . $subdir),
                escapeshellarg($subdir)
            ),
            $status
        );
        return $status;
    }

    /**
     * Sync the git repo with its SVN equivalent
     *
     * @param string $dir The directory to execute the command under
     * @return int The return status of executing the command
     */
    protected function syncRepo($dir)
    {
        $status = 0;
        chdir($dir);

        system(
            sprintf(
                'git svn fetch --authors-file=%s',
                escapeshellarg($this->authorsFile)
            ),
            $status
        );

        system(
            sprintf(
                'java -Dfile.encoding=utf-8 -jar %s sync-rebase',
                escapeshellarg($this->migrationLib)
            ),
            $status
        );
        return $status;
    }

    /**
     * Cleanup tags and branches in the git repo
     *
     * @param string $dir The directory to execute the command under
     * @return int The return status of executing the command
     */
    protected function cleanup($dir)
    {
        $status = 0;
        chdir($dir);

        system(
            sprintf(
                'java -Dfile.encoding=utf-8 -jar %s clean-git --force',
                escapeshellarg($this->migrationLib)
            ),
            $status
        );
        return $status;
    }
}
