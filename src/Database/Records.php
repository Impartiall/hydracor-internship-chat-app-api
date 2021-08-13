<?php

namespace App\Database;

use App\Exceptions\DatabaseException;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * A collection of functions for interacting with records in the database
 */
class Records
{
    /**
     * Insert a record into the database
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is to be created
     * @param array $record A map of the fields on the record to insert
     * 
     * @throws DatabaseException if the query fails
     * 
     * @return array The created record
     */
    public static function insert(Connection $connection, string $table, array $record): array
    {
        try {
            $connection->insert($table, $record);
        } catch (Exception $_) {
            throw new DatabaseException('Failed to insert record.');
        }
        return Self::selectById($connection, $table, $connection->lastInsertId());
    }

    /**
     * Get a record by its ID
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is located
     * @param int $id The ID of the record
     * 
     * @throws DatabaseException if the query fails
     * 
     * @return array The specified record 
     */
    public static function selectById(Connection $connection, string $table, int $id): array
    {
        try {
            return $connection->fetchAssociative(
                "SELECT * FROM $table WHERE id = ?",
                [$id]
            );
        } catch (Exception $_) {
            throw new DatabaseException('Record does not exist.');
        }
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
     * @throws DatabaseException if the query fails
     * 
     * @return array The updated record
     */
    public static function update(Connection $connection, string $table, int $recordId, array $replacements): array
    {
        // Filter out null values
        $replacements = array_filter($replacements, function($v) { return !is_null($v); });

        try {
            $connection->update($table, $replacements, ['id' => $recordId]);
        } catch (Exception $_) {
            throw new DatabaseException('Failed to update record.');
        }
        return Self::selectById($connection, $table, $recordId);
    }

    /**
     * Delete a record by its ID
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is located
     * @param int $recordId The record's ID
     * 
     * @throws DatabaseException if the query fails
     * 
     * @return array The deleted record
     */
    public static function delete(Connection $connection, string $table, int $recordId): array
    {
        $record = Self::selectById($connection, $table, $recordId);

        try {
            $connection->delete($table, ['id' => $recordId]);
        } catch (Exception $_) {
            throw new DatabaseException('Failed to delete record.');
        }
        return $record;
    }

    /**
     * Check that a value does not exist in any row in a given column of a given table
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the record is located
     * @param string $column The column to search
     * @param mixed $value The value to search for
     * 
     * @return bool True if the value is unique, false otherwise
     */
    public static function isUnique(Connection $connection, string $table, string $column, mixed $value): array
    {
        return $connection->fetchOne(
            "SELECT $column FROM $table WHERE $column = ?",
            [$value]
        );
    }
}