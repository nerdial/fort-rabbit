<?php

namespace App\Traits;

use Symfony\Component\Console\Input\InputInterface;

trait Sortable
{

    protected function sortResult(InputInterface $input, array $packages): array
    {
        // sort functionality based on order by parameter , asc or desc
        $sort = SORT_DESC;
        $orderBy = strtolower($input->getOption('orderBy'));

        $userSelectedOrder = strtolower($input->getOption('order'));

        if (strtolower($userSelectedOrder) === 'asc') {
            $sort = SORT_ASC;
        }
        $keys = array_column($packages, $orderBy);
        array_multisort($keys, $sort, $packages);
        return $packages;
    }
}