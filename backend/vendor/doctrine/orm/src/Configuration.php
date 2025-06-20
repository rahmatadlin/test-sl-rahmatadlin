<?php

declare(strict_types=1);

namespace Doctrine\ORM;

use BadMethodCallException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache as CacheDriver;
use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Common\Persistence\PersistentObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\Deprecations\Deprecation;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\Exception\CacheException;
use Doctrine\ORM\Cache\Exception\MetadataCacheNotConfigured;
use Doctrine\ORM\Cache\Exception\MetadataCacheUsesNonPersistentCache;
use Doctrine\ORM\Cache\Exception\QueryCacheNotConfigured;
use Doctrine\ORM\Cache\Exception\QueryCacheUsesNonPersistentCache;
use Doctrine\ORM\Exception\InvalidEntityRepository;
use Doctrine\ORM\Exception\NamedNativeQueryNotFound;
use Doctrine\ORM\Exception\NamedQueryNotFound;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ProxyClassesAlwaysRegenerating;
use Doctrine\ORM\Exception\UnknownEntityNamespace;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\EntityListenerResolver;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Mapping\QuoteStrategy;
use Doctrine\ORM\Mapping\TypedFieldMapper;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\Reflection\RuntimeReflectionProperty;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\VarExporter\LazyGhostTrait;

use function class_exists;
use function is_a;
use function method_exists;
use function sprintf;
use function strtolower;
use function trait_exists;
use function trim;

/**
 * Configuration container for all configuration options of Doctrine.
 * It combines all configuration options from DBAL & ORM.
 *
 * Internal note: When adding a new configuration option just write a getter/setter pair.
 */
class Configuration extends \Doctrine\DBAL\Configuration
{
    /** @var mixed[] */
    protected $_attributes = [];

    /** @phpstan-var array<class-string<AbstractPlatform>, ClassMetadata::GENERATOR_TYPE_*> */
    private $identityGenerationPreferences = [];

    /** @phpstan-param array<class-string<AbstractPlatform>, ClassMetadata::GENERATOR_TYPE_*> $value */
    public function setIdentityGenerationPreferences(array $value): void
    {
        $this->identityGenerationPreferences = $value;
    }

    /** @phpstan-return array<class-string<AbstractPlatform>, ClassMetadata::GENERATOR_TYPE_*> $value */
    public function getIdentityGenerationPreferences(): array
    {
        return $this->identityGenerationPreferences;
    }

    /**
     * Sets the directory where Doctrine generates any necessary proxy class files.
     *
     * @param string $dir
     *
     * @return void
     */
    public function setProxyDir($dir)
    {
        $this->_attributes['proxyDir'] = $dir;
    }

    /**
     * Gets the directory where Doctrine generates any necessary proxy class files.
     *
     * @return string|null
     */
    public function getProxyDir()
    {
        return $this->_attributes['proxyDir'] ?? null;
    }

    /**
     * Gets the strategy for automatically generating proxy classes.
     *
     * @return ProxyFactory::AUTOGENERATE_*
     */
    public function getAutoGenerateProxyClasses()
    {
        return $this->_attributes['autoGenerateProxyClasses'] ?? ProxyFactory::AUTOGENERATE_ALWAYS;
    }

    /**
     * Sets the strategy for automatically generating proxy classes.
     *
     * @param bool|ProxyFactory::AUTOGENERATE_* $autoGenerate True is converted to AUTOGENERATE_ALWAYS, false to AUTOGENERATE_NEVER.
     *
     * @return void
     */
    public function setAutoGenerateProxyClasses($autoGenerate)
    {
        $this->_attributes['autoGenerateProxyClasses'] = (int) $autoGenerate;
    }

    /**
     * Gets the namespace where proxy classes reside.
     *
     * @return string|null
     */
    public function getProxyNamespace()
    {
        return $this->_attributes['proxyNamespace'] ?? null;
    }

    /**
     * Sets the namespace where proxy classes reside.
     *
     * @param string $ns
     *
     * @return void
     */
    public function setProxyNamespace($ns)
    {
        $this->_attributes['proxyNamespace'] = $ns;
    }

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @return void
     *
     * @todo Force parameter to be a Closure to ensure lazy evaluation
     *       (as soon as a metadata cache is in effect, the driver never needs to initialize).
     */
    public function setMetadataDriverImpl(MappingDriver $driverImpl)
    {
        $this->_attributes['metadataDriverImpl'] = $driverImpl;
    }

    /**
     * Adds a new default annotation driver with a correctly configured annotation reader. If $useSimpleAnnotationReader
     * is true, the notation `@Entity` will work, otherwise, the notation `@ORM\Entity` will be supported.
     *
     * @deprecated Use {@see ORMSetup::createDefaultAnnotationDriver()} instead.
     *
     * @param string|string[] $paths
     * @param bool            $useSimpleAnnotationReader
     * @phpstan-param string|list<string> $paths
     *
     * @return AnnotationDriver
     */
    public function newDefaultAnnotationDriver($paths = [], $useSimpleAnnotationReader = true, bool $reportFieldsWhereDeclared = false)
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/pull/9443',
            '%s is deprecated, call %s::createDefaultAnnotationDriver() instead.',
            __METHOD__,
            ORMSetup::class
        );

        if (! class_exists(AnnotationReader::class)) {
            throw new LogicException(
                'The annotation metadata driver cannot be enabled because the "doctrine/annotations" library'
                . ' is not installed. Please run "composer require doctrine/annotations" or choose a different'
                . ' metadata driver.'
            );
        }

        if ($useSimpleAnnotationReader) {
            if (! class_exists(SimpleAnnotationReader::class)) {
                throw new BadMethodCallException(
                    'SimpleAnnotationReader has been removed in doctrine/annotations 2.'
                    . ' Downgrade to version 1 or set $useSimpleAnnotationReader to false.'
                );
            }

            // Register the ORM Annotations in the AnnotationRegistry
            $reader = new SimpleAnnotationReader();
            $reader->addNamespace('Doctrine\ORM\Mapping');
        } else {
            $reader = new AnnotationReader();
        }

        if (class_exists(ArrayCache::class) && class_exists(CachedReader::class)) {
            $reader = new CachedReader($reader, new ArrayCache());
        }

        return new AnnotationDriver(
            $reader,
            (array) $paths,
            $reportFieldsWhereDeclared
        );
    }

    /**
     * Adds a namespace under a certain alias.
     *
     * @deprecated No replacement planned.
     *
     * @param string $alias
     * @param string $namespace
     *
     * @return void
     */
    public function addEntityNamespace($alias, $namespace)
    {
        if (class_exists(PersistentObject::class)) {
            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/orm/issues/8818',
                'Short namespace aliases such as "%s" are deprecated and will be removed in Doctrine ORM 3.0.',
                $alias
            );
        } else {
            throw NotSupported::createForPersistence3(sprintf(
                'Using short namespace alias "%s" by calling %s',
                $alias,
                __METHOD__
            ));
        }

        $this->_attributes['entityNamespaces'][$alias] = $namespace;
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * @param string $entityNamespaceAlias
     *
     * @return string
     *
     * @throws UnknownEntityNamespace
     */
    public function getEntityNamespace($entityNamespaceAlias)
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/issues/8818',
            'Entity short namespace aliases such as "%s" are deprecated, use ::class constant instead.',
            $entityNamespaceAlias
        );

        if (! isset($this->_attributes['entityNamespaces'][$entityNamespaceAlias])) {
            // @phpstan-ignore staticMethod.deprecatedClass
            throw UnknownEntityNamespace::fromNamespaceAlias($entityNamespaceAlias);
        }

        return trim($this->_attributes['entityNamespaces'][$entityNamespaceAlias], '\\');
    }

    /**
     * Sets the entity alias map.
     *
     * @phpstan-param array<string, string> $entityNamespaces
     *
     * @return void
     */
    public function setEntityNamespaces(array $entityNamespaces)
    {
        $this->_attributes['entityNamespaces'] = $entityNamespaces;
    }

    /**
     * Retrieves the list of registered entity namespace aliases.
     *
     * @phpstan-return array<string, string>
     */
    public function getEntityNamespaces()
    {
        return $this->_attributes['entityNamespaces'];
    }

    /**
     * Gets the cache driver implementation that is used for the mapping metadata.
     *
     * @return MappingDriver|null
     */
    public function getMetadataDriverImpl()
    {
        return $this->_attributes['metadataDriverImpl'] ?? null;
    }

    /**
     * Gets the cache driver implementation that is used for query result caching.
     */
    public function getResultCache(): ?CacheItemPoolInterface
    {
        // Compatibility with DBAL 2
        if (! method_exists(parent::class, 'getResultCache')) {
            // @phpstan-ignore method.deprecated
            $cacheImpl = $this->getResultCacheImpl();

            return $cacheImpl ? CacheAdapter::wrap($cacheImpl) : null;
        }

        return parent::getResultCache();
    }

    /**
     * Sets the cache driver implementation that is used for query result caching.
     */
    public function setResultCache(CacheItemPoolInterface $cache): void
    {
        // Compatibility with DBAL 2
        if (! method_exists(parent::class, 'setResultCache')) {
            // @phpstan-ignore method.deprecated
            $this->setResultCacheImpl(DoctrineProvider::wrap($cache));

            return;
        }

        parent::setResultCache($cache);
    }

    /**
     * Gets the cache driver implementation that is used for the query cache (SQL cache).
     *
     * @deprecated Call {@see getQueryCache()} instead.
     *
     * @return CacheDriver|null
     */
    public function getQueryCacheImpl()
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/pull/9002',
            'Method %s() is deprecated and will be removed in Doctrine ORM 3.0. Use getQueryCache() instead.',
            __METHOD__
        );

        return $this->_attributes['queryCacheImpl'] ?? null;
    }

    /**
     * Sets the cache driver implementation that is used for the query cache (SQL cache).
     *
     * @deprecated Call {@see setQueryCache()} instead.
     *
     * @return void
     */
    public function setQueryCacheImpl(CacheDriver $cacheImpl)
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/pull/9002',
            'Method %s() is deprecated and will be removed in Doctrine ORM 3.0. Use setQueryCache() instead.',
            __METHOD__
        );

        $this->_attributes['queryCache']     = CacheAdapter::wrap($cacheImpl);
        $this->_attributes['queryCacheImpl'] = $cacheImpl;
    }

    /**
     * Gets the cache driver implementation that is used for the query cache (SQL cache).
     */
    public function getQueryCache(): ?CacheItemPoolInterface
    {
        return $this->_attributes['queryCache'] ?? null;
    }

    /**
     * Sets the cache driver implementation that is used for the query cache (SQL cache).
     */
    public function setQueryCache(CacheItemPoolInterface $cache): void
    {
        $this->_attributes['queryCache']     = $cache;
        $this->_attributes['queryCacheImpl'] = DoctrineProvider::wrap($cache);
    }

    /**
     * Gets the cache driver implementation that is used for the hydration cache (SQL cache).
     *
     * @deprecated Call {@see getHydrationCache()} instead.
     *
     * @return CacheDriver|null
     */
    public function getHydrationCacheImpl()
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/pull/9002',
            'Method %s() is deprecated and will be removed in Doctrine ORM 3.0. Use getHydrationCache() instead.',
            __METHOD__
        );

        return $this->_attributes['hydrationCacheImpl'] ?? null;
    }

    /**
     * Sets the cache driver implementation that is used for the hydration cache (SQL cache).
     *
     * @deprecated Call {@see setHydrationCache()} instead.
     *
     * @return void
     */
    public function setHydrationCacheImpl(CacheDriver $cacheImpl)
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/pull/9002',
            'Method %s() is deprecated and will be removed in Doctrine ORM 3.0. Use setHydrationCache() instead.',
            __METHOD__
        );

        $this->_attributes['hydrationCache']     = CacheAdapter::wrap($cacheImpl);
        $this->_attributes['hydrationCacheImpl'] = $cacheImpl;
    }

    public function getHydrationCache(): ?CacheItemPoolInterface
    {
        return $this->_attributes['hydrationCache'] ?? null;
    }

    public function setHydrationCache(CacheItemPoolInterface $cache): void
    {
        $this->_attributes['hydrationCache']     = $cache;
        $this->_attributes['hydrationCacheImpl'] = DoctrineProvider::wrap($cache);
    }

    /**
     * Gets the cache driver implementation that is used for metadata caching.
     *
     * @deprecated Deprecated in favor of getMetadataCache
     *
     * @return CacheDriver|null
     */
    public function getMetadataCacheImpl()
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/issues/8650',
            'Method %s() is deprecated and will be removed in Doctrine ORM 3.0. Use getMetadataCache() instead.',
            __METHOD__
        );

        if (isset($this->_attributes['metadataCacheImpl'])) {
            return $this->_attributes['metadataCacheImpl'];
        }

        return isset($this->_attributes['metadataCache']) ? DoctrineProvider::wrap($this->_attributes['metadataCache']) : null;
    }

    /**
     * Sets the cache driver implementation that is used for metadata caching.
     *
     * @deprecated Deprecated in favor of setMetadataCache
     *
     * @return void
     */
    public function setMetadataCacheImpl(CacheDriver $cacheImpl)
    {
        Deprecation::trigger(
            'doctrine/orm',
            'https://github.com/doctrine/orm/issues/8650',
            'Method %s() is deprecated and will be removed in Doctrine ORM 3.0. Use setMetadataCache() instead.',
            __METHOD__
        );

        $this->_attributes['metadataCacheImpl'] = $cacheImpl;
        $this->_attributes['metadataCache']     = CacheAdapter::wrap($cacheImpl);
    }

    public function getMetadataCache(): ?CacheItemPoolInterface
    {
        return $this->_attributes['metadataCache'] ?? null;
    }

    public function setMetadataCache(CacheItemPoolInterface $cache): void
    {
        $this->_attributes['metadataCache']     = $cache;
        $this->_attributes['metadataCacheImpl'] = DoctrineProvider::wrap($cache);
    }

    /**
     * Adds a named DQL query to the configuration.
     *
     * @param string $name The name of the query.
     * @param string $dql  The DQL query string.
     *
     * @return void
     */
    public function addNamedQuery($name, $dql)
    {
        $this->_attributes['namedQueries'][$name] = $dql;
    }

    /**
     * Gets a previously registered named DQL query.
     *
     * @param string $name The name of the query.
     *
     * @return string The DQL query.
     *
     * @throws NamedQueryNotFound
     */
    public function getNamedQuery($name)
    {
        if (! isset($this->_attributes['namedQueries'][$name])) {
            throw NamedQueryNotFound::fromName($name);
        }

        return $this->_attributes['namedQueries'][$name];
    }

    /**
     * Adds a named native query to the configuration.
     *
     * @param string                 $name The name of the query.
     * @param string                 $sql  The native SQL query string.
     * @param Query\ResultSetMapping $rsm  The ResultSetMapping used for the results of the SQL query.
     *
     * @return void
     */
    public function addNamedNativeQuery($name, $sql, Query\ResultSetMapping $rsm)
    {
        $this->_attributes['namedNativeQueries'][$name] = [$sql, $rsm];
    }

    /**
     * Gets the components of a previously registered named native query.
     *
     * @param string $name The name of the query.
     *
     * @return mixed[]
     * @phpstan-return array{string, ResultSetMapping} A tuple with the first element being the SQL string and the second
     *                                               element being the ResultSetMapping.
     *
     * @throws NamedNativeQueryNotFound
     */
    public function getNamedNativeQuery($name)
    {
        if (! isset($this->_attributes['namedNativeQueries'][$name])) {
            throw NamedNativeQueryNotFound::fromName($name);
        }

        return $this->_attributes['namedNativeQueries'][$name];
    }

    /**
     * Ensures that this Configuration instance contains settings that are
     * suitable for a production environment.
     *
     * @deprecated
     *
     * @return void
     *
     * @throws ProxyClassesAlwaysRegenerating
     * @throws CacheException If a configuration setting has a value that is not
     *                        suitable for a production environment.
     */
    public function ensureProductionSettings()
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/orm',
            'https://github.com/doctrine/orm/pull/9074',
            '%s is deprecated',
            __METHOD__
        );

        $queryCacheImpl = $this->getQueryCacheImpl();

        if (! $queryCacheImpl) {
            throw QueryCacheNotConfigured::create();
        }

        if ($queryCacheImpl instanceof ArrayCache) {
            throw QueryCacheUsesNonPersistentCache::fromDriver($queryCacheImpl);
        }

        if ($this->getAutoGenerateProxyClasses() !== ProxyFactory::AUTOGENERATE_NEVER) {
            throw ProxyClassesAlwaysRegenerating::create();
        }

        if (! $this->getMetadataCache()) {
            throw MetadataCacheNotConfigured::create();
        }

        $metadataCacheImpl = $this->getMetadataCacheImpl();

        if ($metadataCacheImpl instanceof ArrayCache) {
            throw MetadataCacheUsesNonPersistentCache::fromDriver($metadataCacheImpl);
        }
    }

    /**
     * Registers a custom DQL function that produces a string value.
     * Such a function can then be used in any DQL statement in any place where string
     * functions are allowed.
     *
     * DQL function names are case-insensitive.
     *
     * @param string                $name      Function name.
     * @param class-string|callable $className Class name or a callable that returns the function.
     * @phpstan-param class-string<FunctionNode>|callable(string):FunctionNode $className
     *
     * @return void
     */
    public function addCustomStringFunction($name, $className)
    {
        $this->_attributes['customStringFunctions'][strtolower($name)] = $className;
    }

    /**
     * Gets the implementation class name of a registered custom string DQL function.
     *
     * @param string $name
     *
     * @return string|callable|null
     * @phpstan-return class-string<FunctionNode>|callable(string):FunctionNode|null
     */
    public function getCustomStringFunction($name)
    {
        $name = strtolower($name);

        return $this->_attributes['customStringFunctions'][$name] ?? null;
    }

    /**
     * Sets a map of custom DQL string functions.
     *
     * Keys must be function names and values the FQCN of the implementing class.
     * The function names will be case-insensitive in DQL.
     *
     * Any previously added string functions are discarded.
     *
     * @phpstan-param array<string, class-string<FunctionNode>|callable(string):FunctionNode> $functions The map of custom
     *                                                     DQL string functions.
     *
     * @return void
     */
    public function setCustomStringFunctions(array $functions)
    {
        foreach ($functions as $name => $className) {
            $this->addCustomStringFunction($name, $className);
        }
    }

    /**
     * Registers a custom DQL function that produces a numeric value.
     * Such a function can then be used in any DQL statement in any place where numeric
     * functions are allowed.
     *
     * DQL function names are case-insensitive.
     *
     * @param string                $name      Function name.
     * @param class-string|callable $className Class name or a callable that returns the function.
     * @phpstan-param class-string<FunctionNode>|callable(string):FunctionNode $className
     *
     * @return void
     */
    public function addCustomNumericFunction($name, $className)
    {
        $this->_attributes['customNumericFunctions'][strtolower($name)] = $className;
    }

    /**
     * Gets the implementation class name of a registered custom numeric DQL function.
     *
     * @param string $name
     *
     * @return class-string|callable|null
     */
    public function getCustomNumericFunction($name)
    {
        $name = strtolower($name);

        return $this->_attributes['customNumericFunctions'][$name] ?? null;
    }

    /**
     * Sets a map of custom DQL numeric functions.
     *
     * Keys must be function names and values the FQCN of the implementing class.
     * The function names will be case-insensitive in DQL.
     *
     * Any previously added numeric functions are discarded.
     *
     * @param array<string, class-string> $functions The map of custom
     *                                                     DQL numeric functions.
     *
     * @return void
     */
    public function setCustomNumericFunctions(array $functions)
    {
        foreach ($functions as $name => $className) {
            $this->addCustomNumericFunction($name, $className);
        }
    }

    /**
     * Registers a custom DQL function that produces a date/time value.
     * Such a function can then be used in any DQL statement in any place where date/time
     * functions are allowed.
     *
     * DQL function names are case-insensitive.
     *
     * @param string          $name      Function name.
     * @param string|callable $className Class name or a callable that returns the function.
     * @phpstan-param class-string<FunctionNode>|callable(string):FunctionNode $className
     *
     * @return void
     */
    public function addCustomDatetimeFunction($name, $className)
    {
        $this->_attributes['customDatetimeFunctions'][strtolower($name)] = $className;
    }

    /**
     * Gets the implementation class name of a registered custom date/time DQL function.
     *
     * @param string $name
     *
     * @return class-string|callable|null
     */
    public function getCustomDatetimeFunction($name)
    {
        $name = strtolower($name);

        return $this->_attributes['customDatetimeFunctions'][$name] ?? null;
    }

    /**
     * Sets a map of custom DQL date/time functions.
     *
     * Keys must be function names and values the FQCN of the implementing class.
     * The function names will be case-insensitive in DQL.
     *
     * Any previously added date/time functions are discarded.
     *
     * @param array $functions The map of custom DQL date/time functions.
     * @phpstan-param array<string, class-string<FunctionNode>|callable(string):FunctionNode> $functions
     *
     * @return void
     */
    public function setCustomDatetimeFunctions(array $functions)
    {
        foreach ($functions as $name => $className) {
            $this->addCustomDatetimeFunction($name, $className);
        }
    }

    /**
     * Sets a TypedFieldMapper for php typed fields to DBAL types auto-completion.
     */
    public function setTypedFieldMapper(?TypedFieldMapper $typedFieldMapper): void
    {
        $this->_attributes['typedFieldMapper'] = $typedFieldMapper;
    }

    /**
     * Gets a TypedFieldMapper for php typed fields to DBAL types auto-completion.
     */
    public function getTypedFieldMapper(): ?TypedFieldMapper
    {
        return $this->_attributes['typedFieldMapper'] ?? null;
    }

    /**
     * Sets the custom hydrator modes in one pass.
     *
     * @param array<string, class-string<AbstractHydrator>> $modes An array of ($modeName => $hydrator).
     *
     * @return void
     */
    public function setCustomHydrationModes($modes)
    {
        $this->_attributes['customHydrationModes'] = [];

        foreach ($modes as $modeName => $hydrator) {
            $this->addCustomHydrationMode($modeName, $hydrator);
        }
    }

    /**
     * Gets the hydrator class for the given hydration mode name.
     *
     * @param string $modeName The hydration mode name.
     *
     * @return class-string<AbstractHydrator>|null The hydrator class name.
     */
    public function getCustomHydrationMode($modeName)
    {
        return $this->_attributes['customHydrationModes'][$modeName] ?? null;
    }

    /**
     * Adds a custom hydration mode.
     *
     * @param string                         $modeName The hydration mode name.
     * @param class-string<AbstractHydrator> $hydrator The hydrator class name.
     *
     * @return void
     */
    public function addCustomHydrationMode($modeName, $hydrator)
    {
        $this->_attributes['customHydrationModes'][$modeName] = $hydrator;
    }

    /**
     * Sets a class metadata factory.
     *
     * @param class-string $cmfName
     *
     * @return void
     */
    public function setClassMetadataFactoryName($cmfName)
    {
        $this->_attributes['classMetadataFactoryName'] = $cmfName;
    }

    /** @return class-string */
    public function getClassMetadataFactoryName()
    {
        if (! isset($this->_attributes['classMetadataFactoryName'])) {
            $this->_attributes['classMetadataFactoryName'] = ClassMetadataFactory::class;
        }

        return $this->_attributes['classMetadataFactoryName'];
    }

    /**
     * Adds a filter to the list of possible filters.
     *
     * @param string                  $name      The name of the filter.
     * @param class-string<SQLFilter> $className The class name of the filter.
     *
     * @return void
     */
    public function addFilter($name, $className)
    {
        $this->_attributes['filters'][$name] = $className;
    }

    /**
     * Gets the class name for a given filter name.
     *
     * @param string $name The name of the filter.
     *
     * @return class-string<SQLFilter>|null The class name of the filter,
     *                                      or null if it is not defined.
     */
    public function getFilterClassName($name)
    {
        return $this->_attributes['filters'][$name] ?? null;
    }

    /**
     * Sets default repository class.
     *
     * @param class-string<EntityRepository> $className
     *
     * @return void
     *
     * @throws InvalidEntityRepository If $classname is not an ObjectRepository.
     */
    public function setDefaultRepositoryClassName($className)
    {
        if (! class_exists($className) || ! is_a($className, ObjectRepository::class, true)) {
            throw InvalidEntityRepository::fromClassName($className);
        }

        if (! is_a($className, EntityRepository::class, true)) {
            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/orm/pull/9533',
                'Configuring %s as default repository class is deprecated because it does not extend %s.',
                $className,
                EntityRepository::class
            );
        }

        $this->_attributes['defaultRepositoryClassName'] = $className;
    }

    /**
     * Get default repository class.
     *
     * @return class-string<EntityRepository>
     */
    public function getDefaultRepositoryClassName()
    {
        return $this->_attributes['defaultRepositoryClassName'] ?? EntityRepository::class;
    }

    /**
     * Sets naming strategy.
     *
     * @return void
     */
    public function setNamingStrategy(NamingStrategy $namingStrategy)
    {
        $this->_attributes['namingStrategy'] = $namingStrategy;
    }

    /**
     * Gets naming strategy..
     *
     * @return NamingStrategy
     */
    public function getNamingStrategy()
    {
        if (! isset($this->_attributes['namingStrategy'])) {
            $this->_attributes['namingStrategy'] = new DefaultNamingStrategy();
        }

        return $this->_attributes['namingStrategy'];
    }

    /**
     * Sets quote strategy.
     *
     * @return void
     */
    public function setQuoteStrategy(QuoteStrategy $quoteStrategy)
    {
        $this->_attributes['quoteStrategy'] = $quoteStrategy;
    }

    /**
     * Gets quote strategy.
     *
     * @return QuoteStrategy
     */
    public function getQuoteStrategy()
    {
        if (! isset($this->_attributes['quoteStrategy'])) {
            $this->_attributes['quoteStrategy'] = new DefaultQuoteStrategy();
        }

        return $this->_attributes['quoteStrategy'];
    }

    /**
     * Set the entity listener resolver.
     *
     * @return void
     */
    public function setEntityListenerResolver(EntityListenerResolver $resolver)
    {
        $this->_attributes['entityListenerResolver'] = $resolver;
    }

    /**
     * Get the entity listener resolver.
     *
     * @return EntityListenerResolver
     */
    public function getEntityListenerResolver()
    {
        if (! isset($this->_attributes['entityListenerResolver'])) {
            $this->_attributes['entityListenerResolver'] = new DefaultEntityListenerResolver();
        }

        return $this->_attributes['entityListenerResolver'];
    }

    /**
     * Set the entity repository factory.
     *
     * @return void
     */
    public function setRepositoryFactory(RepositoryFactory $repositoryFactory)
    {
        $this->_attributes['repositoryFactory'] = $repositoryFactory;
    }

    /**
     * Get the entity repository factory.
     *
     * @return RepositoryFactory
     */
    public function getRepositoryFactory()
    {
        return $this->_attributes['repositoryFactory'] ?? new DefaultRepositoryFactory();
    }

    /** @return bool */
    public function isSecondLevelCacheEnabled()
    {
        return $this->_attributes['isSecondLevelCacheEnabled'] ?? false;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setSecondLevelCacheEnabled($flag = true)
    {
        $this->_attributes['isSecondLevelCacheEnabled'] = (bool) $flag;
    }

    /** @return void */
    public function setSecondLevelCacheConfiguration(CacheConfiguration $cacheConfig)
    {
        $this->_attributes['secondLevelCacheConfiguration'] = $cacheConfig;
    }

    /** @return CacheConfiguration|null */
    public function getSecondLevelCacheConfiguration()
    {
        if (! isset($this->_attributes['secondLevelCacheConfiguration']) && $this->isSecondLevelCacheEnabled()) {
            $this->_attributes['secondLevelCacheConfiguration'] = new CacheConfiguration();
        }

        return $this->_attributes['secondLevelCacheConfiguration'] ?? null;
    }

    /**
     * Returns query hints, which will be applied to every query in application
     *
     * @phpstan-return array<string, mixed>
     */
    public function getDefaultQueryHints()
    {
        return $this->_attributes['defaultQueryHints'] ?? [];
    }

    /**
     * Sets array of query hints, which will be applied to every query in application
     *
     * @phpstan-param array<string, mixed> $defaultQueryHints
     *
     * @return void
     */
    public function setDefaultQueryHints(array $defaultQueryHints)
    {
        $this->_attributes['defaultQueryHints'] = $defaultQueryHints;
    }

    /**
     * Gets the value of a default query hint. If the hint name is not recognized, FALSE is returned.
     *
     * @param string $name The name of the hint.
     *
     * @return mixed The value of the hint or FALSE, if the hint name is not recognized.
     */
    public function getDefaultQueryHint($name)
    {
        return $this->_attributes['defaultQueryHints'][$name] ?? false;
    }

    /**
     * Sets a default query hint. If the hint name is not recognized, it is silently ignored.
     *
     * @param string $name  The name of the hint.
     * @param mixed  $value The value of the hint.
     *
     * @return void
     */
    public function setDefaultQueryHint($name, $value)
    {
        $this->_attributes['defaultQueryHints'][$name] = $value;
    }

    /**
     * Gets a list of entity class names to be ignored by the SchemaTool
     *
     * @return list<class-string>
     */
    public function getSchemaIgnoreClasses(): array
    {
        return $this->_attributes['schemaIgnoreClasses'] ?? [];
    }

    /**
     * Sets a list of entity class names to be ignored by the SchemaTool
     *
     * @param list<class-string> $schemaIgnoreClasses List of entity class names
     */
    public function setSchemaIgnoreClasses(array $schemaIgnoreClasses): void
    {
        $this->_attributes['schemaIgnoreClasses'] = $schemaIgnoreClasses;
    }

    public function isLazyGhostObjectEnabled(): bool
    {
        return $this->_attributes['isLazyGhostObjectEnabled'] ?? false;
    }

    public function setLazyGhostObjectEnabled(bool $flag): void
    {
        // @phpstan-ignore classConstant.deprecatedTrait (Because we support Symfony < 7.3)
        if ($flag && ! trait_exists(LazyGhostTrait::class)) {
            throw new LogicException(
                'Lazy ghost objects cannot be enabled because the "symfony/var-exporter" library'
                . ' version 6.2 or higher is not installed. Please run "composer require symfony/var-exporter:^6.2".'
            );
        }

        if ($flag && ! class_exists(RuntimeReflectionProperty::class)) {
            throw new LogicException(
                'Lazy ghost objects cannot be enabled because the "doctrine/persistence" library'
                . ' version 3.1 or higher is not installed. Please run "composer update doctrine/persistence".'
            );
        }

        $this->_attributes['isLazyGhostObjectEnabled'] = $flag;
    }

    public function setRejectIdCollisionInIdentityMap(bool $flag): void
    {
        $this->_attributes['rejectIdCollisionInIdentityMap'] = $flag;
    }

    public function isRejectIdCollisionInIdentityMapEnabled(): bool
    {
        return $this->_attributes['rejectIdCollisionInIdentityMap'] ?? false;
    }

    public function setEagerFetchBatchSize(int $batchSize = 100): void
    {
        $this->_attributes['fetchModeSubselectBatchSize'] = $batchSize;
    }

    public function getEagerFetchBatchSize(): int
    {
        return $this->_attributes['fetchModeSubselectBatchSize'] ?? 100;
    }
}
