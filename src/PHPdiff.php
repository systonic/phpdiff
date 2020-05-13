<?php

/**
 * PHPdiff.
 */

namespace Systonic\PHPdiff;

class Options {

    /**
     * @var CLImate
     */
    public $cli;

    /**
     * Options constructor.
     *
     * @param CLImate $climate
     * @param array $argv an optional argv array, otherwise arguments will be read from CLI
     * @throws \Exception
     */
    public function __construct($climate, array $argv = null)
    {
        $this->cli = $climate;

        $this->build();
        $this->parse($argv);
    }

    /**
     * Register available options.
     * @throws \Exception
     */
    private function build() {
        $this->cli->description('PHPdiff - Use phploy ini file to diff with server version.');
        $this->cli->arguments->add([
            'server' => [
                'prefix' => 's',
                'longPrefix' => 'server',
                'description' => 'PHPloy ini file server section',
            ],
            'user' => [
                'prefix' => 'u',
                'longPrefix' => 'user',
                'description' => 'Username',
            ],
            'host' => [
                'prefix' => 'h',
                'longPrefix' => 'host',
                'description' => 'Hostname',
            ],
            'path' => [
                'prefix' => 'p',
                'longPrefix' => 'path',
                'description' => 'Server path',
            ],
            'sync' => [
                'longPrefix' => 'sync',
                'description' => 'Sync from server',
                'noValue' => true,
            ],
        ]);
    }

    /**
     * @param array|null $argv
     * @throws \Exception
     */
    private function parse(array $argv = null) {
        $this->cli->arguments->parse($argv);
    }
}

class PHPdiff {

    /**
     * @var string
     */
    protected $version = '0.0.1';

    /**
     * @var \League\CLImate\CLImate
     */
    private $cli;

    /**
     * @var Options
     */
    private $opt;

    /**
     * @var string
     */
    private $iniFileName = 'phploy.ini';

    /**
     * @var string
     */
    private $user = '';

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var bool
     */
    protected $syncFiles = false;

    /**
     * @var array
     */
    private $files = array();

    /**
     * Constructor.
     *
     * @param Options|null $opt an optional set of Options, if null options will be read from CLI args
     * @throws \Exception
     */
    public function __construct(Options $opt = null) {
        $this->opt = $opt !== null ? $opt : new Options(new \League\CLImate\CLImate());
        $this->cli = $this->opt->cli;

        $this->cli->backgroundGreen()->bold()->out('--------------------------------------------------');
        $this->cli->backgroundGreen()->bold()->out('|                     PHPdiff                    |');
        $this->cli->backgroundGreen()->bold()->out('--------------------------------------------------');

        $this->setup();

        if (!$this->syncFiles) {
            $this->cli->bold()->yellow('DRY RUN');
        }

        if (!empty($this->user) && !empty($this->host) && !empty($this->path)) {
            $this->diff();
        } else {
            throw new \Exception("No user, host and path given.");
        }
    }

    /**
     * Setup CLI options.
     */
    private function setup() {
        if (file_exists($this->iniFileName) && $this->cli->arguments->defined('server')) {
            $values = parse_ini_file($this->iniFileName, true);
            $server = $this->cli->arguments->get('server');

            $this->user = $values[$server]['user'];
            $this->host = $values[$server]['host'];
            $this->path = $values[$server]['path'];
        }
        else {
            if ($this->cli->arguments->defined('user')) {
                $this->user = $this->cli->arguments->get('user');
            }
            if ($this->cli->arguments->defined('host')) {
                $this->host = $this->cli->arguments->get('host');
            }
            if ($this->cli->arguments->defined('path')) {
                $this->path = $this->cli->arguments->get('path');
            }
        }

        if ($this->cli->arguments->defined('sync')) {
            $this->syncFiles = true;
        }
    }

    /**
     * List changed remote files.
     * @throws \Exception
     */
    private function diff() {
        $dryrun = '--dry-run ';
        if ($this->syncFiles) {
          $dryrun = '';
        }

        $cmd = sprintf('rsync --checksum %s--exclude-from=.gitignore --recursive --rsh=ssh --verbose %s@%s:%s ./', $dryrun, $this->user, $this->host, $this->path);

        $output = array();
        exec($cmd, $output);

        foreach ($output as $line) {
            $this->cli->out($line);
        }
    }

}
