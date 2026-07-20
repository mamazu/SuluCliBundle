<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Command\SubCommands;

use Mamazu\SuluCliBundle\Object\Commands\CommandContext;

class HelpCommand implements SubCommand
{
    /** @param iterable<SubCommand> $commandList */
    public function __construct(private iterable $commandList) {}

    public function run(CommandContext $context): bool
    {
        $context->getStyle()->title('AvailableCommands');
        $context->getStyle()->table(
            ['Command Usage', 'Description'],
            array_map(
                self::printCommand(...),
                iterator_to_array($this->commandList),
            )
        );

        return false;
    }

    /** @return array{string, string} */
    private function printCommand(SubCommand $command): array
    {
        return [
            $command::getCommand().' '.join(' ', $command::getSubArguments()),
            $command::getDescription(),
        ];
    }

    public static function getCommand(): string
    {
        return 'help';
    }

    public static function getSubArguments(): array {
        return [];
    }

    public static function getDescription(): string {
        return 'Prints the help of the program';
    }
}
