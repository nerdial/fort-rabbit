<?php

namespace App\Command;

use App\Traits\Sortable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\Table;

class CraftPluginCommand extends Command
{

    use Sortable;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'craft:analyze';

    protected Client $client;
    protected string $packageName = "craft-plugin";
    protected string $orderBy = 'downloads';
    protected string $order = 'desc';
    protected int $perPage = 50;
    protected array $orderByOptions = [
        'downloads', 'favers', 'dependents', 'updated'
    ];

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
            'Order by name desc or asc , default : desc', $this->order);

        $this->addOption('output', 'op', InputArgument::OPTIONAL,
            'Limit the number of results', null);

        // using php guzzle client
        $this->client = new Client([
            'base_uri' => 'https://packagist.org/'
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->callPackagistApi($input);

        $packages = $this->sortByName($input, $result);

        $orderBy = $input->getOption('orderBy');

        if ($orderBy) {
            $packages = $this->sortByCategory($packages, $orderBy);
        }

        $this->createOutput($input, $output, $packages);


        return Command::SUCCESS;
    }

    protected function sortByCategory(array $result, string $orderBy): array
    {

        if (!in_array(strtolower($orderBy), $this->orderByOptions)) {
            throw new \Exception($orderBy . ' is not supported as order');
        }

        $keys = array_column($result, $orderBy);
        array_multisort($keys, SORT_DESC, $result);
        return $result;
    }

    protected function sortByName(InputInterface $input, array $result): array
    {
        // sort by name , asc or desc
        $sort = SORT_DESC;
        if (strtolower($input->getOption('order')) === 'asc') {
            $sort = SORT_ASC;
        }
        $packages = $result['results'];
        $keys = array_column($packages, 'name');
        array_multisort($keys, $sort, $packages);
        return $packages;
    }

    protected function createOutput(InputInterface $input, OutputInterface $output, array $packages)
    {
        $headers = ['Name', 'Description', 'Url', 'Repository', 'Downloads', 'Favers'];
        $data = [];
        foreach ($packages as $item) {
            $data [] = [
                $item['name'],
                $item['description'],
                $item['url'],
                $item['repository'],
                $item['downloads'],
                $item['favers']
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

    protected function callPackagistApi(InputInterface $input)
    {
        $response = $this->client->request('GET', 'search.json', [
            'query' => [
                'type' => $this->packageName,
                'abandoned' => false,
                'per_page' => $input->getOption('limit')
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

}