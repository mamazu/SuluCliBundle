<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ChangesetSaver;

interface WebspaceRemoverInterface
{
    public function removeWebspace(string $webspace, string $stage): void;
}
