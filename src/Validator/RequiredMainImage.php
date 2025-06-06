<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class RequiredMainImage extends Constraint
{
    public string $message = 'Une image principale est obligatoire.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
} 