<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Card;
use App\Service\CardNameTranslatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:translate-card-names', description: 'Translate card names to French where missing')]
class TranslateCardNamesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private CardNameTranslatorService $translator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cards = $this->em->getRepository(Card::class)->findAll();
        $updated = 0;

        foreach ($cards as $card) {
            if ($card->getNameFr()) {
                continue;
            }

            $number = $card->getNumber();
            if (null === $number) {
                continue;
            }

            $setId = $card->getSet()->getId();

            $translated = $this->translator->translate($setId, $number);

            if ($translated) {
                $card->setNameFr($translated);
                ++$updated;
                $output->writeln("Translated: {$card->getName()} → $translated");
            }
        }

        $this->em->flush();
        $output->writeln("Done. $updated names translated.");

        return Command::SUCCESS;
    }
}
