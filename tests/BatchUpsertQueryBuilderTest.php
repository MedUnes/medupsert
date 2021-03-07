<?php

declare(strict_types=1);

namespace Medupsert\Tests;

use Medupsert\BatchUpsertQuery;
use Medupsert\BatchUpsertQueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Medupsert\BatchUpsertQueryBuilder
 * @covers \Medupsert\BatchUpsertQuery
 */
final class BatchUpsertQueryBuilderTest extends TestCase
{
    public function provideData(): array
    {
        return [
            'typical' => [
                'tableName' => 'users',
                'entities' => [
                    new User('medunes', 'contact@medunes.net', new \DateTime('2011-01-14 00:00:01')),
                    new User('ionic', 'ionic@medunes.net', new \DateTime('2012-01-14 00:00:01')),
                    new User('sosa', 'sosa@medunes.net', new \DateTime('2013-01-14 00:00:01')),
                ],
                'fields' => ['username', 'email'],
                'expected' => new BatchUpsertQuery(
                    'INSERT INTO users (username, email) VALUES (:username_0, :email_0), (:username_1, :email_1), (:username_2, :email_2) ON DUPLICATE KEY UPDATE username = VALUES(username), email = VALUES(email)',
                    [
                        'username_0' => 'medunes',
                        'email_0' => 'contact@medunes.net',
                        'username_1' => 'ionic',
                        'email_1' => 'ionic@medunes.net',
                        'username_2' => 'sosa',
                        'email_2' => 'sosa@medunes.net',
                    ]),
            ],
        ];
    }

    /**
     * @dataProvider provideData
     */
    public function testBuild(string $tableName, array $entities, array $fields, BatchUpsertQuery $expected): void
    {
        $sut = new BatchUpsertQueryBuilder();
        $actual = $sut->setTableName($tableName)->setEntities($entities)->setFields($fields)->build()->getBatchUpsertQuery();

        static::assertEquals($expected, $actual);
    }
}
