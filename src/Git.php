<?php

namespace OsmScripts\Core;

/**
 * Git helper, works with current directory
 *
 * @property Shell $shell @required Helper for running commands in local shell
 */
class Git extends Object_
{
    #region Properties
    public function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'shell': return $script->singleton(Shell::class);
        }

        return parent::default($property);
    }
    #endregion

    public function init() {
        // create Git repository
        $this->shell->run('git init');

        // mark all files in current directory as tracked by Git, uncommitted new files
        $this->shell->run('git add .');

        // create first Git commit
        $this->shell->run('git commit -am "Initial commit"');
    }

    public function setOrigin($url) {
        $this->shell->run("git remote add origin {$url}");
    }

    public function push($branch = 'master', $remote = 'origin') {
        $this->shell->run("git push -u {$remote} {$branch}");
    }

    public function pushTags($remote = 'origin') {
        $this->shell->run("git push {$remote} --tags");
    }

    public function getUncommittedFiles() {
        $this->shell->run('git update-index -q --refresh', true);

        return $this->shell->output('git diff-index --name-only HEAD --');
    }

    public function fetch($quiet = false) {
        $this->shell->run('git fetch', $quiet);
    }

    public function getCurrentBranch() {
        return implode($this->shell->output('git rev-parse --abbrev-ref HEAD'));
    }

    /**
     * Returns number of Git commits local Git repo is behind (if result is
     * positive number) or ahead (if result is negative number)
     *
     * @return int
     */
    public function getPendingCommitCount() {
        $branch = $this->getCurrentBranch();

        return intval(implode($this->shell->output(
            "git rev-list {$branch}...origin/{$branch} --ignore-submodules --count")));

    }

    public function commit($message) {
        // mark all files in current directory as tracked by Git, uncommitted new files
        $this->shell->run('git add .');

        // create first Git commit
        $this->shell->run("git commit -am \"{$message}\"");
    }

    public function getLatestTag() {
        return $this->shell->output('git describe --tags')[0];
    }

    public function getCommitMessagesSince($commit) {
        return $this->shell->output("git log {$commit}.. --format=%s");

    }

    public function getTags() {
        return $this->shell->output('git tag');
    }

    public function createTag(string $tag) {
        $this->shell->run("git tag {$tag}");
    }

    public function remoteBranchExists($branch, $remote = 'origin') {
        $refs = $this->shell->output('git branch -r --format=%(refname)');
        foreach ($refs as $ref) {
            if ($ref == "refs/remotes/{$remote}/{$branch}") {
                return true;
            }
        }

        return false;
    }

    public function merge($branch) {
        $this->shell->run("git merge {$branch}");
    }

    public function config($config) {
        return $this->shell->output("git config --get {$config}")[0] ?? '';
    }
}