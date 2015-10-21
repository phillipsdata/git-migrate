<?php
namespace PhillipsData\GitMigrate\Actions;

class PushRepo implements ActionInterface
{
    protected $dir;
    protected $originUrl;

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
     * Set the origin URL for the remote origin
     *
     * @param string $url
     * @return \PhillipsData\GitMigrate\Actions\CloneRepo
     */
    public function setOrigin($url)
    {
        $this->originUrl = $url;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        fwrite(STDOUT, sprintf("\n----------\Pushing %s to %s...\n", basename($this->dir), $this->originUrl));
        $status = 0;
        chdir($this->dir);

        system(
            sprintf('git remote remove origin'),
            $status
        );

        system(
            sprintf('git remote add origin %s', $this->originUrl),
            $status
        );

        if ($status === 0) {
            system(
                sprintf('git push -u origin --all'),
                $status
            );

            system(
                sprintf('git push -u origin --tags'),
                $status
            );
        }

        return $status;
    }
}
