<?php

namespace Darsyn\IP\Command;

use Darsyn\IP\InvalidIpAddressException;
use Darsyn\IP\IP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PtonCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('darsyn:ip:pton')
            ->setDescription('Convert an IP address (in protocol notation), passed via either command argument or STDIN, to binary notation.')
            ->addArgument(
                'ip',
                InputArgument::OPTIONAL,
                'If set, the IP address to convert. Otherwise STDIN is read.'
            )
            ->addOption(
                'full',
                'f',
                InputOption::VALUE_NONE,
                'Return the formatted IP in full (long IPv6 version).'
            )
        ;
    }
    
    protected function getFirstLineOfStdin()
    {
        if (0 !== ftell(STDIN)) {
            throw new \RuntimeException('Could not read from STDIN.');
        }
        return fgets(STDIN);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');
        try {
            $address = $input->hasArgument('ip')
                ? $input->getArgument('ip')
                : $this->getFirstLineOfStdin();
            $ip = new IP($address);
            $output->writeln($ip->getBinary());
        } catch (InvalidIpAddressException $e) {
            $output->writeln($formatter->formatBlock([
                'Error',
                'The value passed to this command via STDIN is not a valid IP address.',
                'Make sure that just an unformatted IP value is passed.',
            ], 'error'));
            return 1;
        } catch (\RuntimeException $e) {
            $output->writeln($formatter->formatBlock([
                'Error',
                'Could not read value from STDIN.',
            ], 'error'));
            return 2;
        }
        return 0;
    }
}
