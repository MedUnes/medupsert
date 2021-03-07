<?php

declare(strict_types=1);

namespace Medupsert;

class BatchUpsertQuery
{
    private string $preparedQuery;
    private array $queryParameters;

    public function __construct(string $preparedQuery, array $queryParameters)
    {
        $this->preparedQuery = $preparedQuery;
        $this->queryParameters = $queryParameters;
    }

    public function getPreparedQuery(): string
    {
        return $this->preparedQuery;
    }

    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }
}
