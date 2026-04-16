<?php

namespace ActionsFeature;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        \elgg_require_js('feature');
    }
}
