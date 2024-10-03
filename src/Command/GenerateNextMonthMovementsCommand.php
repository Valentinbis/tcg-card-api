<?php

namespace App\Command;

use App\Service\RecurrenceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-next-month-movements',
    description: 'Generate next month movements',
)]
class GenerateNextMonthMovementsCommand extends Command
{
    public function __construct(
        private RecurrenceService $recurrenceService
    ) {
        parent::__construct();
    }

    protected function configure(): void {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->recurrenceService->generateNextMonthMovements();
            $io->success('Les mouvements pour le mois suivant ont été générés avec succès.');
        } catch (\Exception $e) {
            $io->error('Une erreur est survenue : ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
