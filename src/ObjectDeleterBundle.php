<?php

namespace TorqIT\ObjectDeleterBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Installer\InstallerInterface;

class ObjectDeleterBundle extends AbstractPimcoreBundle
{
    public function getInstaller(): InstallerInterface
    {
        return $this->container->get(ObjectDeleterBundleInstaller::class);
    }
}