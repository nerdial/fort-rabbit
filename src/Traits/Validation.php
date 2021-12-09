<?php

namespace App\Traits;

use Symfony\Component\Console\Input\InputInterface;

trait Validation
{
    protected int $maxLimit = 100;

    protected function validateInput(InputInterface $input) :bool
    {
        $limit = $input->getOption('limit');
        $orderBy = $input->getOption('orderBy');
        if (!is_numeric($limit)){
            throw new \Exception('Please provide a number, maximum 100');
        }

        if($limit > $this->maxLimit){
            throw new \Exception('--limit must not exceed 100, packagist limitation');
        }

        if (!in_array(strtolower($orderBy), $this->orderByOptions)) {
            throw new \Exception($orderBy . ' is not supported as order');
        }

        return true;

    }
}