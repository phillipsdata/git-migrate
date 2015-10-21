<?php
namespace PhillipsData\GitMigrate\Actions;

class CleanupRepo implements ActionInterface
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
        fwrite(STDOUT, sprintf("\n----------\nCleaning up %s...\n", basename($this->dir)));
        $status = 0;
        chdir($this->dir);

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
