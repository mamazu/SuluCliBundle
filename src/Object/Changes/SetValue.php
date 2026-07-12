<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Object\Changes;

class SetValue implements Change
{
    public function __construct(
        public readonly mixed $value,
    ) {}
}
