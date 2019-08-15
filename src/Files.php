<?php

namespace OsmScripts\Core;

use Exception;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for generating files.
 *
 * @property Command $command @required Currently executed command
 * @property string $path @required Directory of the Composer project containing currently executed script
 * @property string $script @required Currently executed script
 * @property OutputInterface $output @required Output console
 */
class Files extends Object_
{
    #region Properties
    public function __get($property) {
        /* @var Script $script */
        global $script;

        switch ($property) {
            case 'command': return $this->command = $script->command;
            case 'path': return $this->path = $script->path;
            case 'script': return $this->script = $script->name;
            case 'output': return $this->output = $script->output;
        }

        return null;
    }
    #endregion

    /**
     * Render specified template of the current package with specified variable values and
     * return the result as a string.
     *
     * Write the template using plain PHP in `{package_dir}/templates/{script}/{template}.php`.
     *
     * Override template for your needs in `{project_dir}/.osmscripts/{package}/templates/{script}/{template}.php`
     *
     * @param string $template Template name. Use `/` for hierarchical template names
     * @param array $variables Values which will be inserted instead of variable placeholders inside the template
     * @param string $package name of the package which defines the template. If omitted,
     *      template is taken from the package in which currently executed command is defined
     * @return string
     */
    public function render($template, $variables = [], $package = null) {
        if (!$package) {
            $package = $this->command->defined_in;
        }

        $filename = "{$this->path}/vendor/$package/templates/{$this->script}/$template.php";
        if (!is_file($filename)) {
            throw new Exception("Template '{$filename}' not found");
        }

        $overwrite = "{$this->path}/.osmscripts/$package/templates/{$this->script}/$template.php";
        if (is_file($overwrite)) {
            $filename = $overwrite;
        }

        return $this->doRender($filename, $variables);
    }

    protected function doRender($__filename, $__variables = []) {
        extract($__variables);
        ob_start();

        /** @noinspection PhpIncludeInspection */
        include $__filename;

        return ob_get_clean();
    }

    /**
     * Create file with specified contents or overwrite if it already exists.
     *
     * @param string $path Filename
     * @param string $contents Contents
     */
    public function save($path, $contents) {
        $action = is_file($path) ? 'updated' : 'created';

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $contents);

        $this->output->writeln("! {$path} {$action}");
    }
}