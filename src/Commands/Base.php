<?php

namespace LumenHprose\Commands;

use Illuminate\Console\Command;

class Base extends Command
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 输出基础信息
     *
     * @return void
     */
    protected function outputInfo()
    {
        $this->comment('版本:');
        $this->output->writeln(sprintf(' - Laravel/Lumen=<info>%s<\/>', app()->version()), $this->parseVerbosity(null));
        $this->output->writeln(sprintf(' - Hprose-php=<info>2.0.0<\/>'), $this->parseVerbosity(null));
        $this->output->newLine();

        $this->comment('监听:');
        foreach (config('hprose.uris') as $uri) {
            $this->line(sprintf(' - <info>%s<\/>', $uri));
        }
        $this->output->newLine();

        $this->comment('可调用远程方法:');
        $methods = \LumenHproseRouter::getMethods();
        if ($methods) {
            foreach ($methods as $method) {
                $this->line(sprintf(' - <info>%s<\/>', $method));
            }
            $this->output->newLine();
        } else {
            $this->line(sprintf(' - <info>无可调用方法<\/>'));
        }
    }
}
