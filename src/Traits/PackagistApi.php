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

            // hopefully, based on the conversation i had, this should be the latest version
            $latestVersion = array_values($data['versions'])[0];

            if (!isset($latestVersion['extra']) && !isset($latestVersion['extra']['handle'])) continue;

            $extraHandle = $latestVersion['extra']['handle'];

            $packages[] = [
                'name' => $data['name'],
                'description' => $data['description'],
                'updated' => $latestVersion['time'],
                'handle' => $extraHandle,
                'repository' => $data['repository'],
                'version' => $latestVersion['version'],
                'downloads' => $data['downloads']['monthly'],
                'dependents' => $data['dependents'],
                'favers' => $data['favers'],
            ];
        }
        return $packages;
    }
}