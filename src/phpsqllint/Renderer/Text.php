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

/**
 * Textual output, easily readable by humans.
 *
 * @category Tools
 * @package  PHP-SQLlint
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  http://www.gnu.org/licenses/agpl.html GNU AGPL v3
 * @link     http://www.emacswiki.org/emacs/CreatingYourOwnCompileErrorRegexp
 */
class Renderer_Text implements Renderer
{
    /**
     * Begin syntax check output rendering
     *
     * @param string $filename Path to the SQL file
     *
     * @return void
     */
    public function startRendering($filename)
    {
        echo "Checking SQL syntax of " . $filename . "\n";
    }

    /**
     * Show the error to the user.
     *
     * @param string  $msg   Error message
     * @param string  $token Character which caused the error
     * @param integer $line  Line at which the error occured
     * @param integer $col   Column at which the error occured
     *
     * @return void
     */
    public function displayError($msg, $token, $line, $col)
    {
        echo ' Line ' . $line
            . ', col ' . $col
            . ' at "' . $this->niceToken($token) . '":'
            . ' ' . $msg
            . "\n";
    }

    /**
     * Finish syntax check output rendering; no syntax errors found
     *
     * @return void
     */
    public function finishOk()
    {
        echo " OK\n";
    }

    /**
     * Convert the token string to a readable one, especially special
     * characters like newline and tabs
     *
     * @param string $str String with possibly special characters
     *
     * @return string Escaped string
     */
    protected function niceToken($str)
    {
        return str_replace(
            ["\n", "\r", "\t"],
            ['\n', '\r', '\t'],
            $str
        );
    }
}
?>
