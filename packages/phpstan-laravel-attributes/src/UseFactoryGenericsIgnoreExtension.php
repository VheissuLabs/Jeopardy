<?php

namespace VheissuLabs\PHPStanLaravelAttributes;

use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;

/**
 * Suppresses the missingType.generics nag for the HasFactory trait when the
 * class already declares its factory via the #[UseFactory] attribute — the
 * generic docblock would only duplicate information the attribute carries.
 */
final class UseFactoryGenericsIgnoreExtension implements IgnoreErrorExtension
{
    private const USE_FACTORY_ATTRIBUTE = 'Illuminate\Database\Eloquent\Attributes\UseFactory';

    private const HAS_FACTORY_TRAIT = 'Illuminate\Database\Eloquent\Factories\HasFactory';

    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        if ($error->getIdentifier() !== 'missingType.generics') {
            return false;
        }

        if (! str_contains($error->getMessage(), self::HAS_FACTORY_TRAIT)) {
            return false;
        }

        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return false;
        }

        return $classReflection->getNativeReflection()->getAttributes(self::USE_FACTORY_ATTRIBUTE) !== [];
    }
}
