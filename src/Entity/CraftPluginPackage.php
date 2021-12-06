<?php
namespace App\Entity;

class CraftPluginPackage implements \JsonSerializable
{
    public string $name;
    public ?string $description;
    public string $handle; // versions[0].extra.handle attribute
    public string $repository;
    public ?string $testLibrary;
    public string $version; // most recent branch
    public int $downloads; // downloads.monthly
    public int $dependents;
    public int $favers;
    public \DateTime $updated;


    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'repository' => $this->repository,
            'testLibrary' => $this->testLibrary,
        ];
    }

}