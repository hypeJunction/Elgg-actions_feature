<?php

namespace ActionsFeature;

use Elgg\Event;
use Elgg\IntegrationTestCase;

class MenusTest extends IntegrationTestCase {

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
     * @param \ElggEntity $entity
     * @param array $value
     * @return Event
     */
    protected function makeMenuEvent(\ElggEntity $entity, array $value = []): Event {
		$event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$event->method('getName')->willReturn('register');
		$event->method('getType')->willReturn('menu:entity');
		$event->method('getValue')->willReturn($value);
		$event->method('getEntityParam')->willReturn($entity);
		$event->method('getParam')->willReturnCallback(function ($key, $default = null) use ($entity) {
			return $key === 'entity' ? $entity : $default;
		});
		$event->method('getParams')->willReturn(['entity' => $entity]);
		return $event;
	}

	/**
     * @return void
     */
    public function testEntityMenuReturnsVoidWhenNotPermitted(): void {
		$user = $this->createUser();
		_elgg_services()->session_manager->setLoggedInUser($user);

		$entity = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $user->guid,
		]);

		$event = $this->makeMenuEvent($entity);

		$result = Menus::entityMenu($event);
		$this->assertNull($result);

		_elgg_services()->session_manager->removeLoggedInUser();
	}

	/**
     * @return void
     */
    public function testEntityMenuAddsFeatureAndUnfeatureItemsForAdminOnGroup(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		_elgg_services()->session_manager->setLoggedInUser($admin);

		$group = $this->createGroup();

		\elgg_register_event_handler('feature', 'group', '\Elgg\Values::getTrue');

		$event = $this->makeMenuEvent($group);

		$items = Menus::entityMenu($event);
		$this->assertIsArray($items);
		$this->assertCount(2, $items);

		$names = array_map(function ($i) {
			return $i->getName();
		}, $items);
		$this->assertContains('feature', $names);
		$this->assertContains('unfeature', $names);

		\elgg_unregister_event_handler('feature', 'group', '\Elgg\Values::getTrue');
		_elgg_services()->session_manager->removeLoggedInUser();
	}

	/**
     * @return void
     */
    public function testFeatureItemVisibleAndUnfeatureHiddenWhenNotFeatured(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		_elgg_services()->session_manager->setLoggedInUser($admin);

		$group = $this->createGroup();
		\elgg_register_event_handler('feature', 'group', '\Elgg\Values::getTrue');

		$event = $this->makeMenuEvent($group);

		$items = Menus::entityMenu($event);

		$byName = [];
		foreach ($items as $i) {
			$byName[$i->getName()] = $i;
		}

		$this->assertNotEquals('hidden', $byName['feature']->getItemClass());
		$this->assertStringContainsString('hidden', (string) $byName['unfeature']->getItemClass());

		\elgg_unregister_event_handler('feature', 'group', '\Elgg\Values::getTrue');
		_elgg_services()->session_manager->removeLoggedInUser();
	}

	/**
     * @return void
     */
    public function testUnfeatureItemVisibleAndFeatureHiddenWhenFeatured(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		_elgg_services()->session_manager->setLoggedInUser($admin);

		$group = $this->createGroup();
		$group->featured = true;
		$group->featured_group = 'yes';
		$group->save();

		\elgg_register_event_handler('feature', 'group', '\Elgg\Values::getTrue');

		$event = $this->makeMenuEvent($group);

		$items = Menus::entityMenu($event);

		$byName = [];
		foreach ($items as $i) {
			$byName[$i->getName()] = $i;
		}

		$this->assertStringContainsString('hidden', (string) $byName['feature']->getItemClass());
		$this->assertNotEquals('hidden', $byName['unfeature']->getItemClass());

		\elgg_unregister_event_handler('feature', 'group', '\Elgg\Values::getTrue');
		_elgg_services()->session_manager->removeLoggedInUser();
	}

	/**
     * @return void
     */
    public function testEntityMenuHrefPointsToFeatureAction(): void {
		$admin = $this->createUser();
		$admin->makeAdmin();
		_elgg_services()->session_manager->setLoggedInUser($admin);

		$group = $this->createGroup();
		\elgg_register_event_handler('feature', 'group', '\Elgg\Values::getTrue');

		$event = $this->makeMenuEvent($group);

		$items = Menus::entityMenu($event);

		$byName = [];
		foreach ($items as $i) {
			$byName[$i->getName()] = $i;
		}

		$this->assertStringContainsString('action/feature', $byName['feature']->getHref());
		$this->assertStringContainsString('action/unfeature', $byName['unfeature']->getHref());
		$this->assertStringContainsString('guid=' . $group->guid, $byName['feature']->getHref());

		\elgg_unregister_event_handler('feature', 'group', '\Elgg\Values::getTrue');
		_elgg_services()->session_manager->removeLoggedInUser();
	}
}
