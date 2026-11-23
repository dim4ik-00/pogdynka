<?php

namespace App\Command;

use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:country-city',
    description: 'Displays weather measurements for a given country and city',
)]
class WeatherCountryCityCommand extends Command
{
    public function __construct(
        private readonly WeatherUtil $weatherUtil,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('countryCode', InputArgument::REQUIRED, 'Country code (e.g. PL)')
            ->addArgument('cityName', InputArgument::REQUIRED, 'City name (e.g. Szczecin)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $countryCode = $input->getArgument('countryCode');
        $cityName = $input->getArgument('cityName');

        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($countryCode, $cityName);

        $io->writeln(sprintf('Location: %s, %s', $cityName, $countryCode));

        if (empty($measurements)) {
            $io->warning('No measurements found or location does not exist.');
            return Command::SUCCESS;
        }

        $io->table(
            ['Date', 'Temperature'],
            array_map(fn($m) => [
                $m->getDate()->format('Y-m-d'),
                $m->getCelsius(),
            ], $measurements)
        );

        return Command::SUCCESS;
    }
}
