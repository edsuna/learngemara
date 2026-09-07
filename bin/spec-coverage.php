#!/usr/bin/env php
<?php
/**
 * Reports which spec scenarios have no test referencing them.
 *
 * Every scenario in docs/spec/ carries a stable ID (DIAG-07, SAVE-03, ...).
 * A test claims a scenario by naming its ID in a docblock, comment or test
 * name. An ID nothing references is a specified behavior nothing verifies --
 * the only completeness check the spec can actually enforce.
 *
 * Usage: php bin/spec-coverage.php
 * Exits 1 if any scenario is unclaimed.
 */

$root = dirname(__DIR__);

$scenarios = [];
foreach (glob("$root/docs/spec/*.md") as $file) {
    if (basename($file) === 'README.md') {
        continue; // illustrative examples only
    }
    preg_match_all(
        '/^\s*(?:@defect\s*\n\s*)?Scenario(?: Outline)?:\s*([A-Z]+-\d+)/m',
        file_get_contents($file),
        $m
    );
    foreach ($m[1] as $id) {
        $scenarios[$id] = basename($file);
    }
}

$referenced = [];
foreach (['tests', 'resources/js/__tests__'] as $dir) {
    if (!is_dir("$root/$dir")) {
        continue;
    }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$dir"));
    foreach ($files as $file) {
        if ($file->isDir() || !preg_match('/\.(php|js|mjs)$/', $file->getFilename())) {
            continue;
        }
        preg_match_all('/\b([A-Z]+-\d+)\b/', file_get_contents($file->getPathname()), $m);
        foreach ($m[1] as $id) {
            $referenced[$id] = true;
        }
    }
}

$byDomain = [];
foreach ($scenarios as $id => $file) {
    $domain = preg_replace('/-\d+$/', '', $id);
    $byDomain[$domain]['total'] = ($byDomain[$domain]['total'] ?? 0) + 1;
    if (isset($referenced[$id])) {
        $byDomain[$domain]['covered'] = ($byDomain[$domain]['covered'] ?? 0) + 1;
    } else {
        $byDomain[$domain]['missing'][] = $id;
    }
}
ksort($byDomain);

$total = count($scenarios);
$covered = count(array_intersect_key($scenarios, $referenced));

printf("Spec coverage: %d/%d scenarios claimed by a test (%d%%)\n\n", $covered, $total, $total ? round($covered / $total * 100) : 0);
printf("  %-7s %7s %6s   %s\n", 'DOMAIN', 'COVERED', 'TOTAL', 'UNCLAIMED');

foreach ($byDomain as $domain => $d) {
    $missing = $d['missing'] ?? [];
    printf(
        "  %-7s %7d %6d   %s\n",
        $domain,
        $d['covered'] ?? 0,
        $d['total'],
        $missing ? implode(' ', array_slice($missing, 0, 6)) . (count($missing) > 6 ? ' +' . (count($missing) - 6) : '') : '-'
    );
}

$orphans = array_diff(array_keys($referenced), array_keys($scenarios));
if ($orphans) {
    echo "\nReferenced by tests but absent from the spec:\n  " . implode(' ', $orphans) . "\n";
}

exit($covered === $total ? 0 : 1);
