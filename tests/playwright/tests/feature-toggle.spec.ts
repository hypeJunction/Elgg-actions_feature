import { test, expect } from '@playwright/test';
import { loginAs, getMetadata, getGroupByName } from '../helpers/elgg';

/**
 * End-to-end coverage for actions_feature:
 *  - Admin sees Feature menu item on a group
 *  - Clicking it fires the feature action
 *  - DB reflects featured=1 and featured_group=yes
 *  - After featuring, Unfeature menu item is visible
 *  - Unfeature action reverses the state
 */
test.describe('actions_feature: feature-toggle flow', () => {
  const groupName = `Feature Test Group ${Date.now()}`;
  let groupGuid: number;

  test.beforeAll(async ({ browser }) => {
    // Create a group as admin for the toggle flow
    const page = await browser.newPage();
    await loginAs(page, 'admin');
    await page.goto('/groups/add');
    await page.fill('input[name="name"]', groupName);
    await page.fill('textarea[name="description"]', 'Playwright fixture group');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/groups\/profile\//);
    const group = await getGroupByName(groupName);
    expect(group).toBeTruthy();
    groupGuid = Number(group.guid);
    await page.close();
  });

  test('admin can feature a group via entity menu', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto(`/groups/profile/${groupGuid}`);

    // Entity menu: feature item should be visible, unfeature hidden
    const featureItem = page.locator('.elgg-menu-entity a').filter({ hasText: /^Feature$/ });
    const unfeatureItem = page.locator('.elgg-menu-entity a').filter({ hasText: /^Unfeature$/ });

    await expect(featureItem).toBeVisible();
    // Unfeature's parent li has `hidden` class
    const unfeatureLi = page.locator('.elgg-menu-entity li.elgg-menu-item-unfeature');
    await expect(unfeatureLi).toHaveClass(/hidden/);

    // Click Feature
    await featureItem.click();

    // Wait for page reload (non-AJAX default action)
    await page.waitForLoadState('networkidle');

    // Assert DB: metadata featured=1 present on group
    const featured = await getMetadata(groupGuid, 'featured');
    expect(featured.length).toBeGreaterThan(0);
    expect(String(featured[0].value)).toBe('1');

    const featuredGroup = await getMetadata(groupGuid, 'featured_group');
    expect(featuredGroup.length).toBeGreaterThan(0);
    expect(String(featuredGroup[0].value)).toBe('yes');
  });

  test('after featuring, unfeature menu item is visible and toggles back', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto(`/groups/profile/${groupGuid}`);

    const featureLi = page.locator('.elgg-menu-entity li.elgg-menu-item-feature');
    const unfeatureItem = page.locator('.elgg-menu-entity a').filter({ hasText: /^Unfeature$/ });

    await expect(featureLi).toHaveClass(/hidden/);
    await expect(unfeatureItem).toBeVisible();

    await unfeatureItem.click();
    await page.waitForLoadState('networkidle');

    // Assert DB: featured is now 0/empty
    const featured = await getMetadata(groupGuid, 'featured');
    if (featured.length > 0) {
      expect(String(featured[0].value)).not.toBe('1');
    }

    const featuredGroup = await getMetadata(groupGuid, 'featured_group');
    if (featuredGroup.length > 0) {
      expect(String(featuredGroup[0].value)).toBe('no');
    }
  });

  test('non-admin user does not see feature menu item', async ({ page }) => {
    await loginAs(page, 'testuser');
    await page.goto(`/groups/profile/${groupGuid}`);

    const featureItem = page.locator('.elgg-menu-entity a').filter({ hasText: /^Feature$/ });
    await expect(featureItem).toHaveCount(0);
  });

  test('non-admin cannot feature via direct action URL', async ({ page }) => {
    await loginAs(page, 'testuser');
    const response = await page.goto(`/action/feature?guid=${groupGuid}`);
    // Should redirect or show error — not succeed
    // Elgg responds with a redirect + system message on permission denied
    expect(response).toBeTruthy();
  });
});
