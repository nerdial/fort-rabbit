<?php

namespace App\Command;

use App\Traits\Sortable;
use App\Entity\CraftPluginPackage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\Table;
use GuzzleHttp\Promise;

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
        $result = $this->callApiToGetAllPackageNames($input);

        $packageNames = array_column($result, 'name');

        $packages = $this->callApiToGetEachPackage($packageNames);


        $orderBy = $input->getOption('orderBy');

        if (!in_array(strtolower($orderBy), $this->orderByOptions)) {
            throw new \Exception($orderBy . ' is not supported as order');
        }


        $packages = $this->sortTheResult($input, $result, $orderBy);

        $this->createOutput($input, $output, $packages);


        return Command::SUCCESS;
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

    protected function callApiToGetAllPackageNames(InputInterface $input)
    {
        $response = $this->client->request('GET', 'search.json', [
            'query' => [
                'type' => $this->packageName,
                'abandoned' => false,
                'per_page' => $input->getOption('limit')
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true)['results'];
    }

    protected function callApiToGetEachPackage(array $names)
    {
        // generate request for all packages
        $requests = [];
        foreach ($names as $name) {
            $packageUri = 'packages/' . $name . '.json';
            $requests[$name] = $this->client->getAsync($packageUri);
        }

        $responses = Promise\Utils::settle($requests)->wait();
        $packages = [];
        foreach ($responses as $response) {

            $result = $response['value'];
            $data = json_decode($result->getBody()->getContents(), true)['package'];

            $package = new CraftPluginPackage();
            $package->name = $data['name'];
            $package->description = $data['description'];
            $package->updated = new \DateTime($data['time']);
            $package->repository = $data['repository'];
            $package->version = $data['repository'];
            $package->downloads = $data['downloads']['total'];
            $package->dependents = $data['dependents'];
            $package->favers = $data['favers'];

            array_push($packages, $package);

        }
        return $packages;
    }

}