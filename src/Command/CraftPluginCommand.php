<?php

namespace App\Command;

use App\Traits\PackagistApi;
use App\Traits\Sortable;
use App\Entity\CraftPluginPackage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\Table;


class CraftPluginCommand extends Command
{

    use Sortable;
    use PackagistApi;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'craft:analyze';

    protected Client $client;
    protected string $type = "craft-plugin";
    protected string $orderBy = 'downloads';
    protected string $order = 'desc';
    protected int $perPage = 50;
    protected array $orderByOptions = [
        'downloads', 'favers', 'dependents', 'updated'
    ];
    protected array $abandoneds = [];

    protected function configure(): void
    {
        $this->setHelp('This command allows you to analyze craft cms plugins.');

        $this->addOption('limit', 'l', InputArgument::OPTIONAL,
            'Limit the number of results, can not exceed 100', $this->perPage);

        $this->addOption('orderBy', 'ob', InputArgument::OPTIONAL,
            'Order by functionality : default=downloads  
            available options = downloads, favers, dependents, updated',
            $this->orderBy);

        $this->addOption('order', 'o', InputArgument::OPTIONAL,
            'Order type :  desc or asc , default : desc', $this->order);

        $this->addOption('output', 'op', InputArgument::OPTIONAL,
            'Limit the number of results', null);

        // using php guzzle client
        $this->client = new Client([
            'base_uri' => 'https://packagist.org/'
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $orderBy = $input->getOption('orderBy');
        if (!in_array(strtolower($orderBy), $this->orderByOptions)) {
            throw new \Exception($orderBy . ' is not supported as order');
        }

        $packages = $this->getAllPackagesFromPackagist();

        // create abandoned filter
        $this->createBlacklist($packages);

        $limitedResult = $this->getLimitedResultFromPackagist($input);

        // filter the result and remove abandoned packages
        $result = $this->removeBlacklistFromResult($limitedResult);


        $packageNames = array_column($result, 'name');
        $packages = $this->getEachPackageSeparately($packageNames);


        $packages = $this->sortResult($input, $packages, $orderBy);
        $this->createOutput($input, $output, $packages);

        return Command::SUCCESS;
    }

    protected function createBlacklist($packages)
    {
        foreach ($packages as $packageName => $value) {
            if ($value['abandoned'] != false) $this->abandoneds [] = $packageName;
        }
    }

    protected function removeBlacklistFromResult($result): array
    {
        return array_filter($result, function ($item) {
            return !in_array($item['name'], $this->abandoneds);
        });
    }

    protected function createOutput(InputInterface $input, OutputInterface $output, array $packages)
    {


        $headers = ['Name', 'Description', 'Updated', 'Handle', 'Repository', 'Downloads', 'Dependents', 'Favers'];
        $data = [];
        foreach ($packages as $item) {
            $data [] = [
                $item['name'],
                $item['description'],
                $item['updated'],
                $item['handle'],
                $item['repository'],
                $item['downloads'],
                $item['dependents'],
                $item['favers'],
            ];
        }

        $option = $input->getOption('output');
        if (!isset($option)) {
            $table = new Table($output);
            $table
                ->setHeaders($headers)
                ->setRows($data);
            $table->render();
        } else {
            file_put_contents($option, json_encode($packages, JSON_PRETTY_PRINT));
        }
    }


}