<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ChangesetSaver;

use Mamazu\SuluCliBundle\Object\Changes\ChangeSet;

interface ChangesetSaverInterface
{
    public function save(ChangeSet $changeSet, string $stage): void;
}
