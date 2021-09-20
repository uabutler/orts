<?php

abstract class DAO
{
    protected $id;


    /**
     * Create a new object. This is called when this object was built by the user
     * @throws DatabaseException
     */
    abstract protected function insert(): void;

    /**
     * Update an existing object. This is called when the object was built from the database.
     * @throws DatabaseException
     */
    abstract protected function update(): void;

    /**
     * Write this object to the database
     * @throws DatabaseException
     */
    public function storeInDB(): void
    {
        if ($this->id == null)
            $this->insert();
        else
            $this->update();
    }
}