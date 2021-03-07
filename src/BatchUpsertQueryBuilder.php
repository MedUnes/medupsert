<?php

declare(strict_types=1);

namespace Medupsert;

use Symfony\Component\PropertyAccess\PropertyAccess;

class BatchUpsertQueryBuilder
{
    /** @var string[] */
    private array $fields = [];

    private BatchUpsertQuery $batchUpsertQuery;
    private array $entities = [];
    private string $tableName;

    public function build(): self
    {
        $preparedQuery = sprintf('INSERT INTO %s (%s) VALUES %s ON DUPLICATE KEY UPDATE %s',
            $this->tableName,
            implode(', ', $this->fields),
            implode(', ', $this->buildInsertValuesPlaceholders()),
            implode(', ', $this->buildOnDuplicateValues())
        );
        $this->batchUpsertQuery = new BatchUpsertQuery($preparedQuery, $this->buildQueryParams());

        return $this;
    }

    /**
     * @return $this
     */
    public function setEntities(array $entities): self
    {
        $this->entities = $entities;

        return $this;
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @param string[] $fields
     *
     * @return $this
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getBatchUpsertQuery(): BatchUpsertQuery
    {
        return $this->batchUpsertQuery;
    }

    private function buildQueryParams(): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $params = [];
        $i = 0;
        foreach ($this->entities as $entity) {
            foreach ($this->fields as $field) {
                $key = sprintf('%s_%s', $field, $i);
                $params[$key] = $propertyAccessor->getValue($entity, $field);
            }
            ++$i;
        }

        return $params;
    }

    private function buildInsertValuesPlaceholders(): array
    {
        for ($i = 0, $insertValuesPlaceholders = []; $i < \count($this->entities); ++$i) {
            $insertValuesPlaceholders[] = sprintf(
                '(%s)',
                implode(', ', array_map(fn ($field) => sprintf(':%s_%d', $field, $i), $this->fields))
            );
        }

        return $insertValuesPlaceholders;
    }

    /**
     * @return string[]
     */
    private function buildOnDuplicateValues(): array
    {
        return array_map(fn ($field) => "$field = VALUES($field)", $this->fields);
    }
}
