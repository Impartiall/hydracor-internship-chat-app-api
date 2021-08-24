<?php

namespace App\Database;

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
     * @throws Exception if the query fails
     * 
     * @return array The created record
     */
    public static function insert(Connection $connection, string $table, array $record): array
    {
        try {
            $connection->insert($table, $record);
        } catch (Exception $_) {
            throw new Exception('Failed to insert record.');
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
     * @throws Exception if the query fails
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
            throw new Exception('Record does not exist.');
        }
    }

    /**
     * Get child records from a parent record's ID
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the child records are located
     * @param string $fk The name of the foreign key referencing the parent table
     * @param int $id The ID of the parent record
     * 
     * @throws Exception if the query fails
     * 
     * @return array The child records of the parent 
     */
    public static function selectMany(Connection $connection, string $table, string $fk, int $id)
    {
        try {
            return $connection->fetchAllAssociative(
                "SELECT * FROM $table WHERE $fk = ?",
                [$id],
                [Connection::PARAM_INT_ARRAY]
            );
        } catch (Exception $_) {
            throw new Exception('Failed to read members.');
        }
    }

    /**
     * Get child records from a parent record's ID through a join table
     * 
     * @param Connection $connection A database connection
     * @param string $table The table in which the child records are located
     * @param string $child_fk The name of the foreign key referencing the child table
     * @param string $joinTable The join table to use
     * @param string $parent_fk The name of the foreign key referencing the parent table
     * @param int $id The ID of the parent record
     * 
     * @throws Exception if the query fails
     * 
     * @return array The child records of the parent 
     */
    public static function selectManyFromJoin(Connection $connection, string $table, string $child_fk, string $joinTable, string $parent_fk, int $id)
    {
        try {
            $recordIds = $connection->fetchFirstColumn(
                "SELECT $child_fk FROM $joinTable WHERE $parent_fk = ?",
                [$id],
            );
            return $connection->fetchAllAssociative(
                "SELECT * FROM $table WHERE id IN (?)",
                [$recordIds],
                [Connection::PARAM_INT_ARRAY]
            );
        } catch (Exception $_) {
            throw new Exception('Failed to read members.');
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
     * @throws Exception if the query fails
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
            throw new Exception('Failed to update record.');
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
     * @throws Exception if the query fails
     * 
     * @return array The deleted record
     */
    public static function delete(Connection $connection, string $table, int $recordId): array
    {
        $record = Self::selectById($connection, $table, $recordId);

        try {
            $connection->delete($table, ['id' => $recordId]);
        } catch (Exception $_) {
            throw new Exception('Failed to delete record.');
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
    public static function isUnique(Connection $connection, string $table, string $column, $value): bool
    {
        return !$connection->fetchOne(
            "SELECT $column FROM $table WHERE $column = ?",
            [$value]
        );
    }

    /**
     * Check that a relationship between records exists in a join table
     * 
     * @param Connection $connection A database connection
     * @param string $joinTable The join table to use
     * @param string $fk1 The name of the foreign key referencing one table
     * @param int $id1 The ID of one record
     * @param string $fk2 The name of the foreign key referencing another table
     * @param int $id2 The ID of another record
     * 
     * @return bool True if the the relationship exists, false otherwise
     */
    public static function doesRelationshipExist(Connection $connection, string $joinTable, string $fk1, int $id1, string $fk2, int $id2): bool
    {
        return !!$connection->fetchOne(
            "SELECT id FROM $joinTable WHERE $fk1 = ? AND $fk2 = ?",
            [$id1, $id2]
        );
    }
}