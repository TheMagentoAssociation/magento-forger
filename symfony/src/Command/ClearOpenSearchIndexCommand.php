<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\Command;

use OpenSearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'opensearch:clear-index',
    description: 'Interactively delete all documents from a selected OpenSearch index',
)]
class ClearOpenSearchIndexCommand extends Command
{
    public function __construct(
        private readonly Client $client,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $indices = $this->client->cat()->indices(['format' => 'json']);
        } catch (\Exception $e) {
            $io->error('Error fetching indices: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $availableIndices = array_values(array_filter(
            array_column($indices, 'index'),
            fn($name) => !str_starts_with($name, '.') && !str_starts_with($name, 'top_queries')
        ));

        if (empty($availableIndices)) {
            $io->warning('No suitable indices found.');
            return Command::SUCCESS;
        }

        $selected = $io->choice('Which index do you want to clear?', $availableIndices);

        if (!$io->confirm("Really delete all documents from '{$selected}'?", false)) {
            $io->info('Operation canceled.');
            return Command::SUCCESS;
        }

        try {
            $response = $this->client->deleteByQuery([
                'index' => $selected,
                'body' => [
                    'query' => [
                        'match_all' => (object) []
                    ]
                ],
                'conflicts' => 'proceed',
                'refresh' => true,
            ]);

            $io->success("Successfully deleted {$response['deleted']} documents from '{$selected}'.");
        } catch (\Exception $e) {
            $io->error('Delete failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
