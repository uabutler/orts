<?php

interface DAODeletable
{
    /**
     * Delete an object. This is called when the object was built from the database.
     * @throws DatabaseException
     */
    public function delete(): void;

    /**
     * @throws DatabaseException
     */
    public static function deleteByID(int $id): void;
}