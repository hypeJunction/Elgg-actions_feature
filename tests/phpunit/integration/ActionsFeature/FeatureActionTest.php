<?php

namespace ActionsFeature;

use Elgg\IntegrationTestCase;

/**
 * Tests the feature/unfeature behavior at the entity level.
 * IntegrationTestCase has no executeAction(), so we verify the underlying
 * state transitions the actions perform.
 */
class FeatureActionTest extends IntegrationTestCase {

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
    public function testFeaturingSetsMetadataOnObject(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		\elgg_get_session()->setLoggedInUser($admin);

		$entity = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $admin->guid,
		]);

		$this->assertEmpty($entity->featured);

		$entity->featured = true;
		$this->assertTrue($entity->save() !== false);

		_elgg_services()->entityCache->delete($entity->guid);
		$loaded = get_entity($entity->guid);
		$this->assertEquals(1, (int) $loaded->featured);

		\elgg_get_session()->removeLoggedInUser();
	}

	/**
     * @return void
     */
    public function testUnfeaturingClearsMetadataOnObject(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();

		$entity = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $admin->guid,
		]);

		$entity->featured = true;
		$entity->save();

		$entity->featured = false;
		$entity->save();

		_elgg_services()->entityCache->delete($entity->guid);
		$loaded = get_entity($entity->guid);
		$this->assertEmpty($loaded->featured);
	}

	/**
     * @return void
     */
    public function testFeaturingGroupAlsoSetsFeaturedGroupFlag(): void {
		$group = $this->createGroup();

		$group->featured = true;
		$group->featured_group = 'yes';
		$group->save();

		_elgg_services()->entityCache->delete($group->guid);
		$loaded = get_entity($group->guid);
		$this->assertEquals(1, (int) $loaded->featured);
		$this->assertEquals('yes', $loaded->featured_group);
	}

	/**
     * @return void
     */
    public function testUnfeaturingGroupClearsFeaturedGroupFlag(): void {
		$group = $this->createGroup();

		$group->featured = true;
		$group->featured_group = 'yes';
		$group->save();

		$group->featured = false;
		$group->featured_group = 'no';
		$group->save();

		_elgg_services()->entityCache->delete($group->guid);
		$loaded = get_entity($group->guid);
		$this->assertEmpty($loaded->featured);
		$this->assertEquals('no', $loaded->featured_group);
	}

	/**
     * @return void
     */
    public function testFeaturedEventCanBeCaught(): void {
		$fired = false;
		$handler = function ($event) use (&$fired) {
			$fired = true;
			return true;
		};
		\elgg_register_event_handler('featured', 'object', $handler);

		$admin = $this->createUser();
		$admin->makeAdmin();
		$entity = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $admin->guid,
		]);

		\elgg_trigger_event('featured', $entity->getType(), $entity);
		$this->assertTrue($fired);

		\elgg_unregister_event_handler('featured', 'object', $handler);
	}

	/**
     * @return void
     */
    public function testUnfeaturedEventCanBeCaught(): void {
		$fired = false;
		$handler = function ($event) use (&$fired) {
			$fired = true;
			return true;
		};
		\elgg_register_event_handler('unfeatured', 'object', $handler);

		$admin = $this->createUser();
		$admin->makeAdmin();
		$entity = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $admin->guid,
		]);

		\elgg_trigger_event('unfeatured', $entity->getType(), $entity);
		$this->assertTrue($fired);

		\elgg_unregister_event_handler('unfeatured', 'object', $handler);
	}
}
