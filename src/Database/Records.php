<?php

namespace App\Database;

use Doctrine\DBAL\Connection;

/**
 * A collection of functions for interacting with records in the database
 */
class Records
{
    /**
     * Insert a record into the database
     * 
     * If a field on the update array is null,
     * the field on the record will not be updated
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is to be created
     * @param array $record A map of the fields on the record to insert
     * 
     * @return array The created record
     */
    public static function insert(Connection $connection, string $table, array $record): array
    {
        $connection->insert($table, $record);
        return Self::selectById($connection, $table, $connection->lastInsertId());
    }

    /**
     * Get a record by its ID
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is located
     * @param int $id The ID of the record
     * 
     * @return array The specified record 
     */
    public static function selectById(Connection $connection, string $table, int $id): array
    {
        return $connection->fetchAssociative(
            'SELECT * FROM ' . $table . ' WHERE id = ?',
            [$id]
        );
    }

    /**
     * Update a record by its ID
     * 
     * If a field on the update array is null,
     * the field on the record will not be updated
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is located
     * @param int $recordId The record's ID
     * @param array $replacements A map of the fields on the record to update
     * 
     * @return array The updated record
     */
    public static function update(Connection $connection, string $table, int $recordId, array $replacements): array
    {
        // Filter out null values
        $replacements = array_filter($replacements, function($v) { return !is_null($v); });

        $connection->update($table, $replacements, ['id' => $recordId]);
        return Self::selectById($connection, $table, $recordId);
    }

    /**
     * Delete a record by its ID
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is located
     * @param int $recordId The record's ID
     * 
     * @return array The deleted record
     */
    public static function delete(Connection $connection, string $table, int $recordId): array
    {
        $record = Self::selectById($connection, $table, $recordId);
        $connection->delete($table, ['id' => $recordId]);
        return $record;
    }
}