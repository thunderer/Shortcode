<?php

const THRESHOLD = 0.10;

function results(string $file): array
{
    $results = array();
    foreach(simplexml_load_file($file)->xpath('//variant') as $variant) {
        $class = (string)$variant->xpath('ancestor::benchmark')[0]['class'];
        $name = substr($class, strrpos($class, '\\') + 1);
        $results[$name][(string)$variant->{'parameter-set'}['name']] = (float)$variant->stats['mode'];
    }

    return $results;
}

function duration(float $microseconds): string
{
    return match(true) {
        $microseconds < 1000 => sprintf('%.1f µs', $microseconds),
        $microseconds < 1000000 => sprintf('%.1f ms', $microseconds / 1000),
        default => sprintf('%.2f s', $microseconds / 1000000),
    };
}

function commit(string $ref, string $sha): string
{
    return '<code>'.$ref.'</code> ('.substr($sha, 0, 7).')';
}

[, $baseFile, $headFile, $tableFile] = $argv;
$base = results($baseFile);
$head = results($headFile);

$counts = array();
$changes = array();
$ratios = array();
foreach($head as $benchmark => $cases) {
    $counts[$benchmark] = array('improved' => 0, 'regressed' => 0, 'untouched' => 0, 'new' => 0);
    foreach($cases as $case => $time) {
        if(false === isset($base[$benchmark][$case])) {
            $counts[$benchmark]['new']++;
            continue;
        }
        $efficiency = $base[$benchmark][$case] / $time - 1;
        if(abs($efficiency) < THRESHOLD) {
            $counts[$benchmark]['untouched']++;
            continue;
        }
        $counts[$benchmark][$efficiency > 0 ? 'improved' : 'regressed']++;
        $changes[] = array($efficiency > 0 ? '⚡' : '❌', $benchmark.': '.$case, $base[$benchmark][$case], $time, $efficiency);
        $ratios[] = 1 + $efficiency;
    }
}
usort($changes, fn(array $a, array $b) => abs($b[4]) <=> abs($a[4]));

$impact = $ratios ? array_product($ratios) ** (1 / count($ratios)) - 1 : 0.0;
echo "<!-- benchmark -->\n";
echo match(true) {
    !$ratios => '## Merging this PR will **not alter performance**',
    $impact >= 0 => sprintf('## Merging this PR will **improve performance by %.2f%%**', 100 * $impact),
    default => sprintf('## Merging this PR will **degrade performance by %.2f%%**', -100 * $impact),
}, "\n\n";

echo "| Benchmark | ⚡ improved | ❌ regressed | ✅ untouched | 🆕 new |\n";
echo "| --------- | ---------- | ----------- | ----------- | ------ |\n";
foreach($counts as $benchmark => $count) {
    printf("| `%s` | %d | %d | %d | %d |\n", $benchmark, $count['improved'], $count['regressed'], $count['untouched'], $count['new']);
}

if($changes) {
    echo "\n### Performance Changes\n\n";
    echo "|     | Benchmark | `BASE` | `HEAD` | Efficiency |\n";
    echo "| --- | --------- | ------ | ------ | ---------- |\n";
    foreach($changes as [$icon, $name, $before, $after, $efficiency]) {
        printf("| %s | `%s` | %s | %s | %+.2f%% |\n", $icon, $name, duration($before), duration($after), 100 * $efficiency);
    }
}

printf("\nA benchmark is changed when its efficiency (`BASE` / `HEAD` - 1) is at least %d%% in either direction.\n", 100 * THRESHOLD);
echo "\n<details><summary>All benchmarks</summary>\n\n```\n", rtrim(file_get_contents($tableFile)), "\n```\n\n</details>\n";
echo "\n---\n\n<sub>Comparing ", commit(getenv('HEAD_REF'), getenv('HEAD_SHA')), ' with ', commit(getenv('BASE_REF'), getenv('BASE_SHA')), "</sub>\n";
