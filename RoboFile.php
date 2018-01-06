<?php
use Symfony\Component\Finder\Finder;

class RoboFile extends \Robo\Tasks
{
    /**
     * Build the Robo phar executable.
     */
    public function pharBuild()
    {
        $this->_exec('phing');
    }

    public function pharSign()
    {
        // signing key: a.menk@imi.de
        $this->_exec('gpg -u 5AECD819 --detach-sign --output imi-conrun.phar.asc imi-conrun.phar');
    }


	/**
	 * Publish iRobo to github
	 *
	 * @return \Robo\Result
	 */
    public function pharPublish()
    {
    	$token = $this->ask('Github Token');
    	$tag = $this->ask('Tag');
    	$repo = 'imi-conrun';

    	return $this->taskExecStack()
		    ->exec('github-release release --security-token ' . escapeshellarg($token)
		           . ' --user imi-digital --repo ' . $repo
		           . ' --tag ' . escapeshellarg($tag))
            // we upload a .phar and the same file without extension
            // .phar seems to be needed for Phive installer
		    ->exec('github-release upload --security-token ' . escapeshellarg($token)
		           . ' --user imi-digital --repo ' . $repo
		           . ' --tag ' . escapeshellarg($tag)
		           . ' --file imi-conrun.phar'
		           . ' --name imi-conrun.phar'
		    )
		    ->exec('github-release upload --security-token ' . escapeshellarg($token)
		           . ' --user imi-digital  --repo ' . $repo
		           . ' --tag ' . escapeshellarg($tag)
		           . ' --file imi-conrun.phar.asc'
		           . ' --name imi-conrun.phar.asc'
		    )
		    ->run();
    }
}
