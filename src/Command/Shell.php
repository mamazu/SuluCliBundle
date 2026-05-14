<?php
declare(strict_types=1);

/*
 * This file is part of the PHPCR Shell package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Mamazu\SuluCliBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is more or less a copy of the Symfony\Component\Shell.
 *
 * @author Daniel Leech
 */
class Shell
{
    private string $history;
    private bool $hasReadline;
    private string $prompt;
    private string $name = 'sulu_content_cli';

    /**
     * Constructor.
     *
     * If there is no readline support for the current PHP executable
     * a \RuntimeException exception is thrown.
     */
    public function __construct(
        private OutputInterface $output
    ) {
        $this->hasReadline = function_exists('readline');
        $this->history = $this->getHistoryDirectory();
        $this->prompt = '/';
    }

    public function setPrompt(string $prompt): void {
        $this->prompt = $prompt;
    }

    public function getHistoryDirectory(): string
    {
        $dataDirectory = getenv('XDG_DATA_HOME');
        if ($dataDirectory !== '') {
            return $dataDirectory.'/'.$this->name;
        }

        return getenv('HOME').'/.history_'.$this->name;
    }

    /**
    * Runs the shell.
    *
    * @return \Generator<string>
     */
    public function run(): \Generator
    {
        if ($this->hasReadline) {
            readline_read_history($this->history);
            // readline_completion_function([$this, 'autocompleter']);
        }

        $this->output->writeln($this->getHeader());

        while (true) {
            $command = $this->readline();

            if (false === $command) {
                $this->output->writeln("\n");

                break;
            }

            if ($this->hasReadline) {
                readline_add_history($command);
                readline_write_history($this->history);
            }

            if (yield $command) {
                return;
            }
        }
}

   /**
     * Returns the shell header.
     *
     * @return string The header string
     */
    protected function getHeader()
    {
return <<<TXT
Welcome to <info>{$this->name}</info>.

At the prompt, type <comment>help</comment> for some help.
To exit the shell, type <comment>exit</comment>.
TXT;
    }

    /**
     * Reads a single line from standard input.
     */
    private function readline(): string|false
    {
        if ($this->hasReadline) {
            $line = readline($this->prompt. ' > ');
        } else {
            $this->output->write($this->prompt);
            $line = fgets(STDIN, 1024);
            if ($line === false) {
                return false;
            }
            $line = rtrim($line);
        }

        return $line;
    }
}

