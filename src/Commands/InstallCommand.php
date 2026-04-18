<?php

namespace Mehedi8gb\ApiCrudify\Commands;

use Symfony\Component\Process\Process;

class InstallCommand extends BaseCommand
{
    protected $signature = 'crudify:install';
    protected $description = 'Install ApiCrudify — copies base classes and configures composer.json';

    public function handle(): void
    {
        $this->printBanner();

        $this->restoreBaseClasses();

        $this->line('  <fg=blue;options=bold>COMPOSER AUTOLOAD</>');
        $this->line('  ' . str_repeat('─', 60));
        $this->addHelpersAutoload();

        $this->newLine();
        $this->line('  ' . str_repeat('─', 60));
        $this->line('  <fg=green;options=bold>✓ Installation complete!</>');
        $this->newLine();
    }

    private function addHelpersAutoload(): void
    {
        $composerPath = base_path('composer.json');
        $composer     = json_decode(file_get_contents($composerPath), true);
        $helpersFile  = 'app/Helpers/Helpers.php';

        $this->newLine();

        if (in_array($helpersFile, $composer['autoload']['files'] ?? [])) {
            $this->line('  <fg=yellow>⚠  Helpers.php already registered in composer.json</>');
        } else {
            $composer['autoload']['files'][] = $helpersFile;
            file_put_contents(
                $composerPath,
                json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
            );
            $this->line('  <fg=green>✓ composer.json autoload updated</>');
        }

        $this->newLine();
        $this->runComposerDumpAutoload();
    }

    private function runComposerDumpAutoload(): void
    {
        if (function_exists('generateUniqueNumber')) {
            $this->line('  <fg=green>✓ Helpers already autoloaded — dump-autoload not needed</>');
            return;
        }

        $this->line('  <fg=blue>↻ Running composer dump-autoload...</>');

        $process = new Process(['composer', 'dump-autoload']);
        $process->setWorkingDirectory(base_path());
        $process->run();

        if ($process->isSuccessful()) {
            $this->line('  <fg=green>✓ composer dump-autoload completed</>');
        } else {
            $this->line('  <fg=yellow>⚠  Could not run composer automatically.</>');
            $this->line('  <fg=yellow>   Run manually: composer dump-autoload</>');
        }
    }

    private function printBanner(): void
    {
        $this->newLine();
        $this->line('<fg=blue>
  █████╗ ██████╗ ██╗     ██████╗██████╗ ██╗   ██╗██████╗ ██╗███████╗██╗   ██╗
 ██╔══██╗██╔══██╗██║    ██╔════╝██╔══██╗██║   ██║██╔══██╗██║██╔════╝╚██╗ ██╔╝
 ███████║██████╔╝██║    ██║     ██████╔╝██║   ██║██║  ██║██║█████╗   ╚████╔╝
 ██╔══██║██╔═══╝ ██║    ██║     ██╔══██╗██║   ██║██║  ██║██║██╔══╝    ╚██╔╝
 ██║  ██║██║     ██║    ╚██████╗██║  ██║╚██████╔╝██████╔╝██║██║        ██║
 ╚═╝  ╚═╝╚═╝     ╚═╝     ╚═════╝╚═╝  ╚═╝ ╚═════╝╚═════╝ ╚═╝╚═╝        ╚═╝
</>');
        $this->line('  <fg=gray>Laravel API Engine for Scalable Query-Driven Applications & Service-Repository CRUD Generator — Installer</>');
        $this->newLine();
        $this->line('  <fg=gray>' . str_repeat('─', 78) . '</>');
        $this->newLine();
    }
}
