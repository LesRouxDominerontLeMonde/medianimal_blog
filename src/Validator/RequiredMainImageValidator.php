<?php

namespace App\Validator;

use App\Entity\Article;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequiredMainImageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RequiredMainImage) {
            throw new UnexpectedTypeException($constraint, RequiredMainImage::class);
        }

        if (!$value instanceof Article) {
            return;
        }

        // Si l'article est nouveau (pas d'ID) et n'a pas d'image principale
        if ($value->getId() === null && !$value->hasMainImage()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('imageMainFile')
                ->addViolation();
        }

        // Si l'article existe mais n'a ni fichier ni chemin d'image
        if ($value->getId() !== null && 
            $value->getImageMainFile() === null && 
            empty($value->getImageMainPath())) {
            $this->context->buildViolation($constraint->message)
                ->atPath('imageMainFile')
                ->addViolation();
        }
    }
} 