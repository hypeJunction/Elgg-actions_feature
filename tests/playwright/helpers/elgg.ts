import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(page: Page, username: string, password: string = process.env.ELGG_ADMIN_PASSWORD || 'admin12345') {
  await page.goto('/login');
  // Elgg 4.x has two login forms: a visible main form and a hidden dropdown.
  // Target the main body form specifically to avoid filling a hidden input.
  await page.locator('.elgg-page-body input[name="username"]').fill(username);
  await page.locator('.elgg-page-body input[name="password"]').fill(password);
  await page.locator('.elgg-page-body button[type="submit"]').click();
  await page.waitForLoadState('networkidle');
}

export async function queryDb(sql: string, params: any[] = []) {
  const conn = await mysql.createConnection(DB_CONFIG);
  const [rows] = await conn.execute(sql, params);
  await conn.end();
  return rows as any[];
}

export async function getMetadata(entityGuid: number, name: string) {
  return queryDb(
    'SELECT * FROM elgg_metadata WHERE entity_guid = ? AND name = ?',
    [entityGuid, name]
  );
}

export async function getGroupByName(name: string) {
  const rows = await queryDb(
    `SELECT e.* FROM elgg_entities e
     JOIN elgg_metadata m ON m.entity_guid = e.guid
     WHERE e.type = 'group' AND m.name = 'name' AND m.value = ?
     ORDER BY e.guid DESC LIMIT 1`,
    [name]
  );
  return rows[0];
}
