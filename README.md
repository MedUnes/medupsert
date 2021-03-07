<h1 align="center"> 
<br>
    <img src="https://github.com/medunes/medupsert/blob/master/logo.png" width="200">
</h1>

<h5>A shorthand helper that adds batch upsert to your doctrine Entities</h5>

[![release](https://img.shields.io/packagist/v/medunes/medupsert?style=flat-square)](https://packagist.org/packages/medunes/medupsert)
[![Build Status](https://github.com/medunes/medupsert/workflows/build/badge.svg?style=flat-square)](https://github.com/MedUnes/medupsert/actions?query=workflow%3A%22build%22)
[![Author](https://img.shields.io/badge/author-@medunes-blue.svg?style=flat-square)](https://twitter.com/medunes2)
[![codecov](https://codecov.io/gh/medunes/medupsert/branch/master/graph/badge.svg?token=8gRnef3vtR)](https://codecov.io/gh/medunes/medupsert)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%205-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/)
[![Psalm](https://img.shields.io/badge/Psalm-Level%205-brightgreen.svg?style=flat&logo=php)](https://shields.io/#/)
[![Psalm Coverage](https://shepherd.dev/github/MedUnes/medupsert/coverage.svg)](https://shepherd.dev/github/MedUnes/medupsert/coverage.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/medunes/medupsert?style=flat-square)](https://packagist.org/packages/medunes/medupsert)

<br>

## 📦 Installation

To install this application, first ensure you have [Composer](https://getcomposer.org/download//) installed, then: 

```bash
# then, require (aka install) the package
$ composer require medunes/medupsert
```

## ℹ️ FAQ

#### So what is this?

A shorthand helper that adds batch upsert to your doctrine Entities

#### More details?

So apparently (please correct me if I'm wrong), and after a tedious search on the wild, Doctrine doesn't like much batch 
upserting (update/insert at once). I totally understand this as this is usually not an ORM's
intention, as it is rather focused towards loading the data as an Object/Entity and affording
the possibility to persist the eventual changes.

Batch processing by itself, in addition to upsert are specially needed in situations where
big, even huge, amount of incoming data needs to be stored as it is, or after some "array" oriented
processing. Speed is a keyword here and the object representation of the data isn't that important.

If you ever share this requirement with me, then welcome to this tiny medupsert ;)

#### How to use it?

The package is so tiny: a Data Class, a builder for this Data, and an [optional] trait for more ease/less boilerplate.

So briefly, first make the entity you want it to support batch upserting use the *BatchUpsertTrait*

```php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Customer;
use App\Repository\Batch\BatchUpsertTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;

class CustomerRepository extends ServiceEntityRepository implements LoggerAwareInterface
{
    use BatchUpsertTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }
}
```
Then, from a command for example (might also be controller, etc.) call the upsert method providing it with:
* The table name bound to your entity (supposed to be defined in its metadata already)
* The list of fields you want to be considered in your batch upsert
* The array of entities to be upserted

Here is an example illustrating that:


```php
<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use App\Repository\CustomerRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerBatchImportCommand extends Command implements SerializerAwareInterface
{
    use SerializerAwareTrait;

    protected static $defaultName = 'customer:batch-import';
    private CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository, SerializerInterface $serializer)
    {
        parent::__construct($name = null);
        $this->customerRepository = $customerRepository;
        $this->serializer = $serializer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Batch Import of posts based on json file')
            ->addArgument('path', InputArgument::REQUIRED, 'Path of the JSON file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        /** @var Customer[] $posts */
        $customers = $this->serializer->deserialize(file_get_contents($path), Customer::class . '[]', 'json');
        $this->customerRepository->upsert(
            $customers->toArray(),
            'customer', // the table name for the Customer Entity
            ['username', 'fullname', 'phone', 'email'] // You might only be interested in these fields
        );  
                  
        return Command::SUCCESS;
    }
}

```
#### Where to contribute?

[CONTRIBUTING](https://github.com/medunes/medupsert/blob/master/CONTRIBUTING.md)

