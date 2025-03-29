<?php

namespace App\Factory;

use App\Entity\Department;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<Department>
 *
 * @method        Department|Proxy create(array|callable $attributes = [])
 * @method static Department|Proxy createOne(array $attributes = [])
 * @method static Department|Proxy find(object|array|mixed $criteria)
 * @method static Department|Proxy findOrCreate(array $attributes)
 * @method static Department|Proxy first(string $sortedField = 'id')
 * @method static Department|Proxy last(string $sortedField = 'id')
 * @method static Department|Proxy random(array $attributes = [])
 * @method static Department|Proxy randomOrCreate(array $attributes = [])
 * @method static DepartmentRepository|ProxyRepositoryDecorator repository()
 * @method static Department[]|Proxy[] all()
 * @method static Department[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Department[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Department[]|Proxy[] findBy(array $attributes)
 * @method static Department[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Department[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class DepartmentFactory extends PersistentProxyObjectFactory{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Department::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->state(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Department $department): void {})
        ;
    }
}
