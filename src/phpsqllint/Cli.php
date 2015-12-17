<?php
/**
 * Part of php-sqllint
 *
 * PHP version 5
 *
 * @category Tools
 * @package  PHP-SQLlint
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link     http://cweiske.de/php-sqllint.htm
 */
namespace phpsqllint;
use SqlParser\Parser;

require_once 'Console/CommandLine.php';

/**
 * Command line interface
 *
 * @category Tools
 * @package  PHP-SQLlint
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link     http://www.emacswiki.org/emacs/CreatingYourOwnCompileErrorRegexp
 */
class Cli
{
    protected $renderer;

    protected $format = false;
    protected $colors = false;

    /**
     * Start processing.
     *
     * @return void
     */
    public function run()
    {
        try {
            $parser = $this->loadOptionParser();
            $files  = $this->parseParameters($parser);

            $allfine = true;
            foreach ($files as $filename) {
                if ($this->format) {
                    $allfine &= $this->formatFile($filename);
                } else {
                    $allfine &= $this->checkFile($filename);
                }
            }

            if ($allfine == true) {
                exit(0);
            } else {
                exit(10);
            }
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Check a .sql file for syntax errors
     *
     * @param string $filename File path
     *
     * @return boolean True if there were no errors, false if there were some
     */
    public function checkFile($filename)
    {
        $this->renderer->startRendering($filename);
        $sql = $this->loadSql($filename);
        if ($sql === false) {
            return false;
        }

        $parser = new \SqlParser\Parser($sql);
        if (count($parser->errors) == 0) {
            $this->renderer->finishOk();
            return true;
        }

        $lines = array(1 => 0);
        $pos = -1;
        $line = 1;
        while (false !== $pos = strpos($sql, "\n", ++$pos)) {
            $lines[++$line] = $pos;
        }

        foreach ($parser->errors as $error) {
            /* @var SqlParser\Exceptions\ParserException $error) */
            reset($lines);
            $line = 1;
            while (next($lines) && $error->token->position >= current($lines)) {
                ++$line;
            }
            $col = $error->token->position - $lines[$line];

            $this->renderer->displayError(
                $error->getMessage(),
                //FIXME: ->token or ->value?
                $error->token->token,
                $line,
                $col
            );
        }

        return false;
    }

    /**
     * Reformat the given file
     */
    protected function formatFile($filename)
    {
        $this->renderer->startRendering($filename);
        $sql = $this->loadSql($filename);
        if ($sql === false) {
            return false;
        }

        $options = array(
            //FIXME: automatically detect if the shell/tool supports colors
            'type' => $this->colors ? 'cli' : 'text'
        );
        echo \SqlParser\Utils\Formatter::format($sql, $options) . "\n";
    }

    protected function loadSql($filename)
    {
        if ($filename == '-') {
            $sql = file_get_contents('php://stdin');
        } else {
            $sql = file_get_contents($filename);
        }
        if (trim($sql) == '') {
            $this->renderer->displayError('SQL file empty', '', 0, 0);
            return false;
        }
        return $sql;
    }

    /**
     * Load parameters for the CLI option parser.
     *
     * @return \Console_CommandLine CLI option parser
     */
    protected function loadOptionParser()
    {
        $parser = new \Console_CommandLine();
        $parser->description = 'php-sqllint';
        $parser->version = '0.0.2';
        $parser->avoid_reading_stdin = true;

        $parser->addOption(
            'format',
            array(
                'short_name'  => '-f',
                'long_name'   => '--format',
                'description' => 'Reformat SQL instead of checking',
                'action'      => 'StoreTrue',
                'default'     => false,
            )
        );
        $parser->addOption(
            'colors',
            array(
                'long_name'   => '--color',
                'description' => 'Use colors in formatting output',
                'action'      => 'StoreTrue',
            )
        );
        $parser->addOption(
            'nocolors',
            array(
                'long_name'   => '--nocolor',
                'description' => 'Disable colors in formatting output',
                'action'      => 'StoreTrue',
            )
        );
        $parser->addOption(
            'renderer',
            array(
                'short_name'  => '-r',
                'long_name'   => '--renderer',
                'description' => 'Output mode',
                'action'      => 'StoreString',
                'choices'     => array(
                    'emacs',
                    'text',
                ),
                'default'     => 'text',
                'add_list_option' => true,
            )
        );

        $parser->addArgument(
            'sql_files',
            array(
                'description' => 'SQL files, "-" for stdin',
                'multiple'    => true
            )
        );

        return $parser;
    }

    /**
     * Let the CLI option parser parse the options.
     *
     * @param object $parser Option parser
     *
     * @return array Array of file names
     */
    protected function parseParameters(\Console_CommandLine $parser)
    {
        try {
            $result = $parser->parse();

            $rendClass = '\\phpsqllint\\Renderer_'
                . ucfirst($result->options['renderer']);
            $this->renderer = new $rendClass();

            $this->format = $result->options['format'];

            if ($result->options['colors'] !== null) {
                $this->colors = $result->options['colors'];
            } else if ($result->options['nocolors'] !== null) {
                $this->colors = !$result->options['nocolors'];
            } else {
                //default coloring to enabled, except
                // when piping | to another tool
                $this->colors = true;
                if (function_exists('posix_isatty')
                    && !posix_isatty(STDOUT)
                ) {
                    $this->colors = false;
                }
            }

            foreach ($result->args['sql_files'] as $filename) {
                if ($filename == '-') {
                    continue;
                }
                if (!file_exists($filename)) {
                    throw new \Exception('File does not exist: ' . $filename);
                }
                if (!is_file($filename)) {
                    throw new \Exception('Not a file: ' . $filename);
                }
            }

            return $result->args['sql_files'];
        } catch (\Exception $exc) {
            $parser->displayError($exc->getMessage());
        }
    }

}
?>
