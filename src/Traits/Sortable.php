<?php

namespace App\Traits;

use Symfony\Component\Console\Input\InputInterface;

trait Sortable
{

    protected array $orderTypeOptions = [
        'asc', 'desc'
    ];


    protected function sortTheResult(InputInterface $input, array $result, $orderBy): array
    {
        // sort functionality based on order by parameter , asc or desc
        $sort = SORT_DESC;

        $userSelectedOrder = strtolower($input->getOption('order'));

        if(!in_array($userSelectedOrder, $this->orderTypeOptions)){
            throw new \Exception('--order option only supports desc or asc ');
        }

        if (strtolower($userSelectedOrder) === 'asc') {
            $sort = SORT_ASC;
        }
        $packages = $result['results'];
        $keys = array_column($packages, $orderBy);
        array_multisort($keys, $sort, $packages);
        return $packages;
    }
}