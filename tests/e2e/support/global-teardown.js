import { execFileSync } from 'node:child_process';

export default function globalTeardown() {
    execFileSync('php', ['artisan', 'e2e:fixtures', 'clean'], { encoding: 'utf8' });
    console.log('  e2e fixtures removed');
}
