<?php

namespace app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class Test extends Command
{
    //php webman make:command test
    protected static $defaultName = 'test';
    protected static $defaultDescription = '测试自定义脚本';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('level', null, InputOption::VALUE_NONE, '无参数字段');
        $this->addOption('age', null, InputOption::VALUE_OPTIONAL, '有参数字段');
        $this->addArgument('name', InputArgument::OPTIONAL, 'Name description');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $level = $input->getOption('level'); //php webman test --level 这里接收到的是 true ，未传递参数是 false
        $age = $input->getOption('age'); //php webman test --age=11 这里接收到的是 11
        $name = $input->getArgument('name'); //php webman test shiroi 这里接收到的是 shiroi
        $output->writeln('Hello ' . ($name ?: 'test') . "({$age})");
        return self::SUCCESS;
    }

}
