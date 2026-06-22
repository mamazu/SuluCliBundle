<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services;

use Mamazu\SuluCliBundle\Object\DeletePath;

class ChangesetSaver implements ChangesetSaverInterface
{
    /**
     * @param array<string, string|DeletePath> $changes
     */
    public function save(array $changes): void
    {
        ksort($changes);
        dd($changes);
    }
}
