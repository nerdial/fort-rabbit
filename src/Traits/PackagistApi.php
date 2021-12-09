<?php

namespace App\Traits;

use Symfony\Component\Console\Input\InputInterface;
use GuzzleHttp\Promise;

trait PackagistApi
{

    protected function getAllPackagesFromPackagist()
    {
        $response = $this->client->request('GET', 'packages/list.json', [
            'query' => [
                'type' => 'craft-plugin',
                'fields' => ['abandoned']
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true)['packages'];
    }

    protected function getLimitedResultFromPackagist(InputInterface $input)
    {
        $limit = $input->getOption('limit');
        $response = $this->client->request('GET', 'search.json', [
            'query' => [
                'type' => $this->type,
                'per_page' => $limit
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true)['results'];
    }


    protected function getEachPackageSeparately(array $names): array
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

            if (!isset($response['value'])) continue;
            $result = $response['value'];
            $data = json_decode($result->getBody()->getContents(), true)['package'];
            $firstVersion = array_values($data['versions'])[0];

            if (!isset($firstVersion['extra']) && !isset($firstVersion['extra']['handle'])) continue;

            $extraHandle = $firstVersion['extra']['handle'];

            $packages[] = [
                'name' => $data['name'],
                'description' => $data['description'],
                'updated' => $data['time'],
                'handle' => $extraHandle,
                'repository' => $data['repository'],
                'version' => $firstVersion,
                'downloads' => $data['downloads']['monthly'],
                'dependents' => $data['dependents'],
                'favers' => $data['favers'],
            ];
        }
        return $packages;
    }
}