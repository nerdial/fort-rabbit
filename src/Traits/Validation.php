<?php

namespace App\Traits;

use Symfony\Component\Console\Input\InputInterface;

trait Validation
{
    protected int $maxLimit = 100;
    protected array $orderTypeOptions = [
        'asc', 'desc'
    ];

    /**
     * @throws \Exception
     */
    public function validateInput(InputInterface $input)
    {
        $orderBy = $input->getOption('orderBy');
        $order = $input->getOption('order');
        $limit = $input->getOption('limit');


        $this->validateLimitOption($limit);
        $this->validateOrderOption($order);
        $this->validateOrderByOption($orderBy);
    }

    public function validateLimitOption($limit): bool
    {
        if (!is_numeric($limit)) {
            throw new \InvalidArgumentException('Please provide a number, maximum 100');
        }

        if ($limit <= 0 || $limit > $this->maxLimit) {
            throw new \InvalidArgumentException('--limit must be number between 1 to 100, packagist limitation');
        }

        return true;
    }

    public function validateOrderOption($order): bool
    {
        if (!in_array(strtolower($order), $this->orderTypeOptions)) {
            throw new \InvalidArgumentException('--order option only supports desc or asc ');
        }

        return true;
    }

    public function validateOrderByOption($orderBy): bool
    {
        if (!in_array(strtolower($orderBy), $this->orderByOptions)) {
            throw new \InvalidArgumentException($orderBy . ' is not supported as order');
        }

        return true;
    }
}