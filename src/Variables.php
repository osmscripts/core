<?php

namespace OsmScripts\Core;

use stdClass;

/**
 * Helper class for managing script variables.
 *
 * @property Files $files @required Helper for generating files.
 * @property string $script @required Currently executed script
 * @property string $filename @required File where surrent script variables are stored
 * @property array $data @required Array storing variable values
 * @property bool $dirty
 */
class Variables extends Object_
{
    #region Properties
    public function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'files': return $script->singleton(Files::class);
            case 'script': return $script->name;
            case 'filename': return ".osmscripts/{$this->script}.json";
            case 'data': return $this->readJson($this->filename) ?: [];
        }

        return parent::default($property);
    }
    #endregion

    /**
     * Returns all defined script variables
     *
     * @return array
     */
    public function all() {
        return $this->data;
    }

    /**
     * Returns specified script variable
     *
     * @param $variable
     * @return mixed
     */
    public function get($variable) {
        return $this->data[$variable] ?? null;
    }

    /**
     * Sets specified script variable
     *
     * @param string $variable
     * @param mixed $value
     */
    public function set($variable, $value) {
        // make sure data is read from the file
        $this->data;

        if (isset($this->data[$variable]) && !is_string($this->data[$variable])) {
            throw new \Exception("Can't set non-string variable '{$variable}'");
        }

        $this->data[$variable] = $value;
        $this->dirty = true;
    }

    /**
     * Deletes specified script variable
     *
     * @param $variable
     */
    public function unset($variable) {
        // make sure data is read from the file
        $this->data;

        if (isset($this->data[$variable]) && !is_string($this->data[$variable])) {
            throw new \Exception("Can't unset non-string variable '{$variable}'");
        }

        unset($this->data[$variable]);
        $this->dirty = true;
    }

    public function save() {
        if (!$this->dirty) {
            return;
        }

        $this->files->save($this->filename, json_encode((object)$this->data,
            JSON_PRETTY_PRINT));
    }

    protected function readJson($filename) {
        if (!is_file($filename)) {
            return null;
        }

        if (!($contents = file_get_contents($filename))) {
            return null;
        }

        if (!($result = json_decode($contents, true))) {
            return null;
        }

        return $result;
    }
}