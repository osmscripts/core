<?php

namespace OsmScripts\Core;

/**
 * @property string $global_filename
 * @property string $project_filename
 */
class Configs extends Object_
{
    #region Properties

    protected function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'global_filename':
                return "{$script->path}/.osmscripts/g_{$script->name}.php";
            case 'project_filename':
                return "{$script->cwd}/.osmscripts/{$script->name}.php";
        }

        return parent::default($property);
    }

    #endregion

    public function readGlobalConfig() {
        return $this->readConfig($this->global_filename);
    }

    public function readProjectConfig() {
        return $this->readConfig($this->project_filename);
    }

    protected function readConfig($__filename) {
        if (!is_file($__filename)) {
            return null;
        }

        /** @noinspection PhpIncludeInspection */
        return include $__filename;
    }
}