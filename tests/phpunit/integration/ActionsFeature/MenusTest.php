<?php

namespace ActionsFeature;

use Elgg\Hook;
use Elgg\IntegrationTestCase;

class MenusTest extends IntegrationTestCase {

	public function up() {
	}

	public function down() {
	}

	public function getPluginID(): string {
		return '';
	}

	public function testEntityMenuReturnsVoidWhenNotPermitted(): void {
		$user = $this->createUser();
		\elgg_get_session()->setLoggedInUser($user);

		$entity = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $user->guid,
		]);

		$hook = new Hook(
			\elgg(),
			'register',
			'menu:entity',
			['entity' => $entity],
			[]
		);

		$result = Menus::entityMenu($hook);
		$this->assertNull($result);

		\elgg_get_session()->removeLoggedInUser();
	}

	public function testEntityMenuAddsFeatureAndUnfeatureItemsForAdminOnGroup(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		\elgg_get_session()->setLoggedInUser($admin);

		$group = $this->createGroup();

		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');

		$hook = new Hook(
			\elgg(),
			'register',
			'menu:entity',
			['entity' => $group],
			[]
		);

		$items = Menus::entityMenu($hook);
		$this->assertIsArray($items);
		$this->assertCount(2, $items);

		$names = array_map(function ($i) {
			return $i->getName();
		}, $items);
		$this->assertContains('feature', $names);
		$this->assertContains('unfeature', $names);

		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		\elgg_get_session()->removeLoggedInUser();
	}

	public function testFeatureItemVisibleAndUnfeatureHiddenWhenNotFeatured(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		\elgg_get_session()->setLoggedInUser($admin);

		$group = $this->createGroup();
		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');

		$hook = new Hook(
			\elgg(),
			'register',
			'menu:entity',
			['entity' => $group],
			[]
		);

		$items = Menus::entityMenu($hook);

		$byName = [];
		foreach ($items as $i) {
			$byName[$i->getName()] = $i;
		}

		$this->assertNotEquals('hidden', $byName['feature']->getItemClass());
		$this->assertStringContainsString('hidden', (string) $byName['unfeature']->getItemClass());

		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		\elgg_get_session()->removeLoggedInUser();
	}

	public function testUnfeatureItemVisibleAndFeatureHiddenWhenFeatured(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		\elgg_get_session()->setLoggedInUser($admin);

		$group = $this->createGroup();
		$group->featured = true;
		$group->featured_group = 'yes';
		$group->save();

		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');

		$hook = new Hook(
			\elgg(),
			'register',
			'menu:entity',
			['entity' => $group],
			[]
		);

		$items = Menus::entityMenu($hook);

		$byName = [];
		foreach ($items as $i) {
			$byName[$i->getName()] = $i;
		}

		$this->assertStringContainsString('hidden', (string) $byName['feature']->getItemClass());
		$this->assertNotEquals('hidden', $byName['unfeature']->getItemClass());

		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		\elgg_get_session()->removeLoggedInUser();
	}

	public function testEntityMenuHrefPointsToFeatureAction(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		\elgg_get_session()->setLoggedInUser($admin);

		$group = $this->createGroup();
		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');

		$hook = new Hook(
			\elgg(),
			'register',
			'menu:entity',
			['entity' => $group],
			[]
		);

		$items = Menus::entityMenu($hook);

		$byName = [];
		foreach ($items as $i) {
			$byName[$i->getName()] = $i;
		}

		$this->assertStringContainsString('action/feature', $byName['feature']->getHref());
		$this->assertStringContainsString('action/unfeature', $byName['unfeature']->getHref());
		$this->assertStringContainsString('guid=' . $group->guid, $byName['feature']->getHref());

		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		\elgg_get_session()->removeLoggedInUser();
	}
}
