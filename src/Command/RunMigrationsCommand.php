<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'app:database:migrate', description: 'Apply pending service SQL migrations.')]
final class RunMigrationsCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $root = dirname(__DIR__, 2) . '/services';
        $directories = ['customer-service', 'api-registry'];
        $appliedFiles = 0;
        $this->connection->executeStatement('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(255) PRIMARY KEY, executed_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP)');
        foreach ($directories as $directory) {
            $files = glob(sprintf('%s/%s/migrations/*.sql', $root, $directory)) ?: [];
            sort($files);
            foreach ($files as $file) {
                $version = basename($file);
                if ($this->connection->fetchOne('SELECT 1 FROM schema_migrations WHERE version = :version', ['version' => $version]) !== false) {
                    continue;
                }
                $sql = file_get_contents($file);
                if ($sql === false || trim($sql) === '') {
                    throw new RuntimeException(sprintf('Migration "%s" is empty or unreadable.', $version));
                }
                $this->connection->beginTransaction();
                try {
                    $this->connection->executeStatement($sql);
                    $this->connection->insert('schema_migrations', ['version' => $version]);
                    $this->connection->commit();
                } catch (Throwable $exception) {
                    $this->connection->rollBack();
                    throw $exception;
                }
                ++$appliedFiles;
                $io->success(sprintf('Applied migration %s (%s)', $version, $directory));
            }
        }
        if ($appliedFiles === 0) {
            $io->note('No pending service migrations found.');
        }
        $io->success('Database migrations are up to date.');
        return Command::SUCCESS;
    }
}
