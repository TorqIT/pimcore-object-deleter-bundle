<?php

namespace TorqIT\ObjectDeleterBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class ObjectDeleterBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/objectdeleter/js/pimcore/startup.js'
        ];
    }

    public function getInstaller()
    {
        return $this->container->get(ObjectDeleterBundleInstaller::class);
    }
}