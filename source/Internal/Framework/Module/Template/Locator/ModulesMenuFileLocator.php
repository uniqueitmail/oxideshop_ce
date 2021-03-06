<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Module\Template\Locator;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ActiveModulesDataProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\Locator\NavigationFileLocatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class ModulesMenuFileLocator implements NavigationFileLocatorInterface
{
    /** @var ActiveModulesDataProviderInterface */
    private $activeModulesDataProvider;
    /** @var Filesystem */
    private $filesystem;
    /** @var string */
    private $fileName = 'menu.xml';

    public function __construct(
        ActiveModulesDataProviderInterface $activeModulesDataProvider,
        Filesystem $filesystem
    ) {
        $this->activeModulesDataProvider = $activeModulesDataProvider;
        $this->filesystem = $filesystem;
    }

    /** @inheritDoc */
    public function locate(): array
    {
        $menuFiles = [];
        foreach ($this->activeModulesDataProvider->getModulePaths() as $modulePath) {
            $menuFilePath = Path::join($modulePath, $this->fileName);
            if ($this->filesystem->exists($menuFilePath)) {
                $menuFiles[] = $menuFilePath;
            }
        }
        return $menuFiles;
    }
}
