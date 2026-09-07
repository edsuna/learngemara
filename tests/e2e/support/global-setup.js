import { execFileSync } from 'node:child_process';

/**
 * The browser suite runs against the development database, so it seeds tagged
 * fixtures it can identify and remove afterwards rather than assuming whatever
 * happens to be there. Without this, tests silently passed against an empty
 * or unfamiliar database.
 */
export default function globalSetup() {
    const out = execFileSync('php', ['artisan', 'e2e:fixtures', 'seed'], { encoding: 'utf8' });
    const line = out.trim().split('\n').filter((l) => l.startsWith('{')).pop();
    process.env.E2E_FIXTURES = line || '{}';
    console.log('  e2e fixtures seeded:', line);
}
