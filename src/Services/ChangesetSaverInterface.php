<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services;

use Mamazu\SuluCliBundle\Object\Changes\ChangeSet;

interface ChangesetSaverInterface
{
    public function save(ChangeSet $changeSet): void;
}
