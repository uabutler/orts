<?php

interface DAODeactivatable
{
    /**
     * Deactivate an object. This is called when the object was built from the database.
     * @throws DatabaseException
     */
    public function deactivate(): void;

    /**
     * @throws DatabaseException
     */
    public static function deactivateByID(int $id): void;
}