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
                $allfine &= $this->checkFile($filename);
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

        if ($filename == '-') {
            $sql = file_get_contents('php://stdin');
        } else {
            $sql = file_get_contents($filename);
        }
        if (trim($sql) == '') {
            $this->renderer->displayError('SQL file empty', '', 0, 0);
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
