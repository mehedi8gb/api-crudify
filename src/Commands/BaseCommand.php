<?php

namespace Mehedi8gb\ApiCrudify\Commands;

use Illuminate\Console\Command;
use Mehedi8gb\ApiCrudify\Services\BaseClassRestorerService;

abstract class BaseCommand extends Command
{
    protected function restoreBaseClasses(): void
    {
        $restorer = new BaseClassRestorerService($this);
        $results  = $restorer->ensureBaseClassesExist();

        $restored = $results['restored'];
        $skipped  = $results['skipped'];

        $this->newLine();
        $this->line('  <fg=blue;options=bold>BASE CLASS INTEGRITY CHECK</>');
        $this->line('  ' . str_repeat('─', 60));

        if (empty($restored)) {
            $this->line("  <fg=green>✓ All " . count($skipped) . " base files are in place.</>");
        } else {
            // Show only restored files in table
            $this->table(
                ['  File', 'Path', 'Status'],
                array_map(fn($row) => [
                    "  {$row[0]}",
                    $row[1],
                    "<fg=green>{$row[2]}</>",
                ], $restored)
            );

            $this->line("  <fg=green>✓ " . count($restored) . " file(s) restored.</>  <fg=gray>" . count($skipped) . " already existed.</>");
        }

        $this->newLine();
    }
}
