<?php

namespace OsmScripts\Core;

/**
 * @property string $filename
 * @property \PDO $pdo
 * @property int $installed_version
 */
class Db extends Object_
{
    public $version = 0;

    #region Properties

    protected function default($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'filename': return "{$script->path}/.osmscripts/{$script->name}.sqlite";
            case 'pdo': return $this->getPdo();
            case 'installed_version':
                return $this->value("SELECT version FROM settings");
        }

        return parent::default($property);
    }

    protected function getPdo() {
        if (!is_dir(dirname($this->filename))) {
            mkdir(dirname($this->filename), 0775, true);
        }

        if (!is_file($this->filename)) {
            file_put_contents($this->filename, '');
        }

        $this->pdo = new \PDO("sqlite:{$this->filename}");

        $this->install();

        return $this->pdo;
    }

    #endregion

    protected function install() {
        $this->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS settings (
                version INTEGER NOT NULL
            )
SQL
        );

        $this->exec("INSERT INTO settings (version) VALUES (:version)",
            ['version' => 0]);
    }

    public function exec($query, $bindings = []) {
        $stmt = $this->pdo->prepare($query);

        $this->bind($stmt, $bindings);

        return $stmt->execute();
    }

    public function query($query, $bindings = []) {
        $stmt = $this->pdo->prepare($query);

        $this->bind($stmt, $bindings);

        return $stmt->fetchAll(\PDO::FETCH_CLASS);
    }

    public function first($query, $bindings = []) {
        foreach ($this->query($query, $bindings) as $row) {
            return $row;
        }

        return null;
    }

    public function value($query, $bindings = []) {
        if (!($row = $this->first($query, $bindings))) {
            return null;
        }

        $array = (array)$row;

        return count($array) > 0 ? reset($array) : null;
    }

    protected function bind(\PDOStatement $stmt, $bindings) {
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $value = (int)$value;
            }

            if (is_int($value)) {
                $stmt->bindValue(":{$key}", $value, \PDO::PARAM_INT);
            }
            else {
                $stmt->bindValue(":{$key}", $value);
            }
        }
    }
}