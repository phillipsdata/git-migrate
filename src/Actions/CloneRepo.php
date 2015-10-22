<?php
namespace PhillipsData\GitMigrate\Actions;

class CloneRepo implements ActionInterface
{
    protected $dir;
    protected $authorsFile;
    protected $svnUrl;
    protected $path;

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
     * Set URL to SVN server
     *
     * @param string $svnUrl
     * @return \PhillipsData\GitMigrate\Actions\CloneRepo
     */
    public function setSvnUrl($svnUrl)
    {
        $this->svnUrl = $svnUrl;
        return $this;
    }

    /**
     * Set relative path for the repository
     *
     * @param string $path
     * @return \PhillipsData\GitMigrate\Actions\CloneRepo
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        fwrite(STDOUT, sprintf("\n----------\nCloning %s...\n", basename($this->dir)));
        $status = 0;
        chdir(dirname($this->dir));

        system(
            sprintf(
                'git svn clone --stdlayout --authors-file=%s %s %s',
                escapeshellarg($this->authorsFile),
                escapeshellarg($this->svnUrl . str_replace('\\', '/', $this->path)),
                escapeshellarg(basename($this->path))
            ),
            $status
        );
        return $status;
    }
}
