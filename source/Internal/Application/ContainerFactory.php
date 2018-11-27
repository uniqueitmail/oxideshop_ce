<?php declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Application;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Facts\Facts;
use OxidEsales\EshopCommunity\Internal\Utility\Context;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
  *
 * @internal
 */
class ContainerFactory
{
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @var ContainerInterface
     */
    private $symfonyContainer = null;

    /**
     * ContainerFactory constructor.
     *
     * Make the constructor private
     */
    private function __construct()
    {
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        if ($this->symfonyContainer === null) {
            $this->initializeContainer();
        }

        return $this->symfonyContainer;
    }

    /**
     * Loads container from cache if available, otherwise
     * create the container from scratch.
     */
    private function initializeContainer()
    {
        $cacheFilePath = $this::getCacheFilePath();

        if (file_exists($cacheFilePath)) {
            $this->loadContainerFromCache($cacheFilePath);
        } else {
            $this->getCompiledSymfonyContainer();
            $this->saveContainerToCache($cacheFilePath);
        }
    }

    /**
     * @param string $cachefile
     */
    private function loadContainerFromCache($cachefile)
    {
        include_once $cachefile;
        $this->symfonyContainer = new \ProjectServiceContainer();
    }

    /**
     * Returns compiled Container
     */
    private function getCompiledSymfonyContainer()
    {
        $containerBuilder = new ContainerBuilder(new Facts());
        $this->symfonyContainer = $containerBuilder->getContainer();
        $this->symfonyContainer->compile();
    }

    /**
     * Dumps the compiled container to the cachefile.
     *
     * @param string $cachefile
     */
    private function saveContainerToCache($cachefile)
    {
        $dumper = new PhpDumper($this->symfonyContainer);
        file_put_contents($cachefile, $dumper->dump());
    }

    /**
     * @todo: move it to another place.
     *
     * @return string
     */
    private static function getCacheFilePath()
    {
        $context = new Context(Registry::getConfig());
        return $context->getContainerCacheFile();
    }

    /**
     * @return ContainerFactory
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ContainerFactory();
        }
        return self::$instance;
    }

    /**
     * Forces reload of the ContainerFactory on next request.
     */
    public static function resetContainer()
    {
        if (file_exists(self::getCacheFilePath())) {
            unlink(self::getCacheFilePath());
        }
        self::$instance = null;
    }
}
