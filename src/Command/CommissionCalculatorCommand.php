<?php

namespace App\Command;

use App\Dto\TransactionDto;
use App\Service\Commission\CommissionFacadeInterface;
use App\Service\Logger\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommissionCalculatorCommand extends Command
{
    public function __construct(
        private readonly CommissionFacadeInterface $commissionFacade,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:commission:calculate')
            ->setDescription('Calculate and display transaction commission.')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the file.')
            ->addOption('base', null, InputOption::VALUE_OPTIONAL, 'Base Currency.', 'EUR');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $file = $this->getFile($input);
            if (!is_file($file)) {
                $msg = '<error>File not found.</error>';
                $output->writeln($msg);
                $this->logger->error($msg);

                return Command::FAILURE;
            }

            if ($output->isVerbose()) {
                $output->writeln(
                    sprintf(
                        '<info>Reading file `%s` and calculating commission for every row.</info>',
                        $file
                    )
                );
            }

            $handle = fopen($file, 'r');
            if (!$handle) {
                $msg = '<error>Could not open file for reading.</error>';
                $output->writeln($msg);
                $this->logger->error($msg);

                return Command::FAILURE;
            }

            while (($line = fgets($handle)) !== false) {
                $rowData = json_decode($line, true);

                $transactionDto = TransactionDto::fromArray(
                    array_merge(
                        $rowData,
                        [
                            'base' => $this->getBaseCurrency($input),
                        ]
                    )
                );
                $commissionResponseDto = $this->commissionFacade->calculateCommission($transactionDto);
                if (!$commissionResponseDto->getSuccess()) {
                    $errMsg = sprintf(
                        '<error>Failed to calculate commission for row line `%s`. Reason: `%s`.</error>',
                        $line,
                        implode(', ', $commissionResponseDto->getErrors())
                    );
                    if ($output->isVerbose()) {
                        $output->writeln($errMsg);
                    }
                    $this->logger->error($errMsg);

                    continue;
                }

                $output->writeln((string)$commissionResponseDto->getAmount());
            }
            fclose($handle);

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            var_dump($exception);
            $msg = '<error>' . $exception->getMessage() . '</error>';
            $output->writeln($msg);
            $this->logger->error($msg);

            return Command::FAILURE;
        }
    }

    protected function getBaseCurrency(InputInterface $input): string
    {
        return $input->getOption('base');
    }

    protected function getFile(InputInterface $input): string
    {
        return $input->getArgument('file');
    }
}
