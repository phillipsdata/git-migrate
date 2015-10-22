<?php
namespace PhillipsData\GitMigrate\Actions;

class SyncRepo implements ActionInterface
{
    protected $dir;
    protected $migrationLib;

    /**
     * Set the directory to process under
     *
     * @param string $dir
     * @return \PhillipsData\GitMigrate\Actions\Cleanup
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * Set path to authors file
     *
     * @param string $authorsFile
     * @return \PhillipsData\GitMigrate\Actions\CloneRepo
     */
    public function setAuthorsFile($authorsFile)
    {
        $this->authorsFile = $authorsFile;
        return $this;
    }

    /**
     * Set the migration library (.jar) to use
     *
     * @param string $migrationLib
     * @return \PhillipsData\GitMigrate\Actions\Cleanup
     */
    public function setMigrationLib($migrationLib)
    {
        $this->migrationLib = $migrationLib;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        fwrite(STDOUT, sprintf("\n----------\nSyncing %s...\n", basename($this->dir)));
        $status = 0;
        chdir($this->dir);

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
}
