<?php

namespace Darsyn\IP\Command;

use Darsyn\IP\InvalidIpAddressException;
use Darsyn\IP\IP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NtopCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('darsyn:ip:ntop')
            ->setDescription('Convert an IP address (in binary notation), passed via STDIN, to protocol notation.')
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
            $address = trim($this->getFirstLineOfStdin());
            $ip = new IP($address);
            $output->writeln(
                $input->hasOption('full')
                    ? $ip->getLongAddress()
                    : $ip->getShortAddress()
            );
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
