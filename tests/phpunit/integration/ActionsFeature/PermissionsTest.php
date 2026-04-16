<?php

namespace ActionsFeature;

use Elgg\IntegrationTestCase;

class PermissionsTest extends IntegrationTestCase {

	public function up() {
	}

	public function down() {
	}

	/**
     * @return string
     */
    public function getPluginID(): string {
		return '';
	}

	/**
     * @return void
     */
    public function testIsAllowedTypeReturnsFalseByDefaultForObject(): void {
		$owner = $this->createUser();
		$object = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $owner->guid,
		]);
		$this->assertFalse(Permissions::isAllowedType($object));
	}

	/**
     * @return void
     */
    public function testIsAllowedTypeReturnsTrueForGroupViaPluginHook(): void {
		// The plugin registers a 'feature', 'group' hook that returns true
		$group = $this->createGroup();
		// Register the hook handler in case the plugin isn't active in test DB
		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		$this->assertTrue(Permissions::isAllowedType($group));
		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
	}

	/**
     * @return void
     */
    public function testCanFeatureReturnsFalseForNonLoggedInUser(): void {
		$group = $this->createGroup();
		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');

		// Pass null user, not logged in
		$session = \elgg_get_session();
		$session->removeLoggedInUser();

		$this->assertFalse(Permissions::canFeature($group, null));
		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
	}

	/**
     * @return void
     */
    public function testCanFeatureReturnsTrueForAdmin(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		$group = $this->createGroup();

		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		$this->assertTrue(Permissions::canFeature($group, $admin));
		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
	}

	/**
     * @return void
     */
    public function testCanFeatureReturnsFalseForNonAdmin(): void {
		$user = $this->createUser();
		$group = $this->createGroup();

		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		$this->assertFalse(Permissions::canFeature($group, $user));
		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
	}

	/**
     * @return void
     */
    public function testCanFeatureReturnsFalseWhenTypeNotAllowed(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		$object = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $admin->guid,
		]);
		// No hook registered for blog -> not allowed
		$this->assertFalse(Permissions::canFeature($object, $admin));
	}

	/**
     * @return void
     */
    public function testPermissionsCheckHookCanOverrideDefault(): void {
		$user = $this->createUser();
		$group = $this->createGroup();

		\elgg_register_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		\elgg_register_plugin_hook_handler('permissions_check:feature', 'group', '\Elgg\Values::getTrue');

		$this->assertTrue(Permissions::canFeature($group, $user));

		\elgg_unregister_plugin_hook_handler('feature', 'group', '\Elgg\Values::getTrue');
		\elgg_unregister_plugin_hook_handler('permissions_check:feature', 'group', '\Elgg\Values::getTrue');
	}
}
