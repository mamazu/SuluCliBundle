<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

interface SubCommand
{
    /**
    * @return bool Returns true if this should end the program, else false
    */
    public function run(CommandContext $context): bool;

    public static function getCommand(): string;

    /** @return array<string> */
    public static function getSubArguments(): array;

    public static function getDescription(): string;
}
