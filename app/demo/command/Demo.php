<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * Demo命令行实例
 */
namespace app\demo\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Demo extends Command
{
    protected function configure()
    {
        $this->setName('demo');
        $this->setDescription('This is the demo app console');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("Hello World");
    }
}