<?php

namespace ActionsFeature;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap for actions_feature.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		\elgg_import_esm('feature');
	}
}
