<?php

namespace VheissuLabs\PHPStanLaravelAttributes;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Resolves Model::factory() to the concrete factory class declared by the
 * model's #[UseFactory] attribute, replacing the type the @use HasFactory
 * generic docblock used to provide.
 */
final class UseFactoryReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    private const USE_FACTORY_ATTRIBUTE = 'Illuminate\Database\Eloquent\Attributes\UseFactory';

    public function __construct(private readonly ReflectionProvider $reflectionProvider) {}

    public function getClass(): string
    {
        return 'Illuminate\Database\Eloquent\Model';
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'factory';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        if (! $methodCall->class instanceof Name) {
            return null;
        }

        $modelClass = $scope->resolveName($methodCall->class);

        if (! $this->reflectionProvider->hasClass($modelClass)) {
            return null;
        }

        $attributes = $this->reflectionProvider->getClass($modelClass)
            ->getNativeReflection()
            ->getAttributes(self::USE_FACTORY_ATTRIBUTE);

        if ($attributes === []) {
            return null;
        }

        $factoryClass = $attributes[0]->getArguments()[0] ?? null;

        if (! is_string($factoryClass) || ! $this->reflectionProvider->hasClass($factoryClass)) {
            return null;
        }

        return new ObjectType($factoryClass);
    }
}
