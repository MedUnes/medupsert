<?php

declare(strict_types=1);

namespace Medupsert;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Psr\Log\LoggerAwareTrait;
use Throwable;

trait BatchUpsertTrait
{
    use LoggerAwareTrait;

    private EntityManagerInterface $entityManager;
    private BatchUpsertQueryBuilder $batchUpsertBuilder;

    public function __construct(EntityManagerInterface $entityManager, BatchUpsertQueryBuilder $batchUpsertBuilder)
    {
        $this->entityManager = $entityManager;
        $this->batchUpsertBuilder = $batchUpsertBuilder;
    }

    /**
     * @throws MappingException
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    public function upsert(array $entityList, string $tableName, array $fields): void
    {
        $batchUpsertQuery = $this
            ->batchUpsertBuilder
            ->setEntities($entityList)
            ->setTableName($tableName)
            ->setFields($fields)
            ->build()
            ->getBatchUpsertQuery();

        try {
            $this->getEntityManager()->getConnection()->executeQuery(
                $batchUpsertQuery->getPreparedQuery(),
                $batchUpsertQuery->getQueryParameters()
            );
            $this->logger->info(sprintf('UPSERT: Success for %d %s(s)', \count($entityList), $tableName));
        } catch (Throwable $e) {
            $this->logger->error('Error on Upsert', ['exception' => $e]);
            $this->getEntityManager()->clear();
        }
    }
}
