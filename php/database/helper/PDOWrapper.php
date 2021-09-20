<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../logger.php';
require_once __DIR__ . '/DatabaseException.php';
require_once __DIR__ . '/DAODeletable.php';
require_once __DIR__ . '/DAODeactivatable.php';

/**
 * Class PDOWrapper
 * A wrapper around the MySQL PDO object. Any actually interaction with the database should happen through this class
 * to ensure a common manner in which to handle logging and errors
 *
 * This class represents a singleton. All database writes that happen from a single request occur in a transaction.
 * The commit function can be called to attempt the commit and retrieve any possible error information at a given point.
 * Otherwise, the transaction will be committed when the request exists and the destructor is called.
 */
class PDOWrapper
{
    private $pdo;
    private $files;

    private function __construct()
    {
        $this->files = [];

        Logger::info("Creating database connection");

        $dsn = "mysql:host=".DATABASE['host'].";dbname=".DATABASE['db_name'];
        try
        {
            $this->pdo = new PDO($dsn, DATABASE['user'], DATABASE['passwd']);
            $this->pdo->beginTransaction();
        }
        catch (PDOException $e)
        {
            Logger::error("Could not create a database connection" . Logger::obj($e), Verbosity::LOW, true);
            require_once __DIR__ . '/../../../html/error/error500.php';
            exit();
        }
    }

    /**
     * Commit all the database actions, including the file deletions.
     * @throws DatabaseException
     */
    public static function commit()
    {
        $wrapper = self::getInstance();

        if ($wrapper->pdo->commit())
        {
            foreach ($wrapper->files as $file)
            {
                if (!unlink($file))
                {
                    Logger::warning("Some files could not be deleted. These now exist on the server without a corresponding database entry. These files should be manually deleted.");
                    throw new DatabaseException("WARNING: The server was unable to delete the files automatically. They will be removed at a later date. Please contact the system administrator if these are sensitive and require urgent removal.", 200, null);
                }
            }
        }
        else
        {
            throw new DatabaseException("An internal error has occurred. Please contact the system administrator", 500, $wrapper->pdo->errorInfo());
        }
    }

    public static function getConnection(): PDO
    {
        return self::getInstance()->pdo;
    }

    /**
     * @throws DatabaseException
     */
    public function __destruct()
    {
        self::commit();
    }

    private static function getInstance(): PDOWrapper
    {
        static $wrapper = null;
        if ($wrapper == null) $wrapper = new PDOWrapper();
        return $wrapper;
    }

    /**
     *
     * @param string $filename The absolute path of the file to be deleted
     */
    public static function markFileForDeletion(string $filename)
    {
        self::getInstance()->files[] = $filename;
    }

    /**
     * @throws DatabaseException
     */
    public static function insert(string $tbl, PDOStatement $smt, string $log): int
    {
        Logger::info("Writing new item to $tbl table: $log");

        if (!$smt->execute())
        {
            $error_info = $smt->errorInfo();

            if ($error_info[0] === "23000")
            {
                Logger::warning("Item already exists in $tbl. Error info: " . Logger::obj($error_info));
                Logger::warning("Details: $log");
                throw new DatabaseException("Duplicate already exists. Please edit your request and try again", 400, $error_info);
            }
            else
            {
                Logger::error("Item insertion failed in $tbl. Error info: " . Logger::obj($error_info));
                Logger::error("Details: $log");
                throw new DatabaseException("An unknown error has occurred. Contact the system administrator if you believe this is a bug.", 500, $error_info);
            }
        }

        $pdo = self::getInstance()->pdo;

        Logger::info("Insertion complete.");
        $id = $pdo->lastInsertId();
        Logger::info("Created with id $id", Verbosity::MED);

        return $id;
    }

    /**
     * @throws DatabaseException
     */
    public static function update(string $tbl, PDOStatement $smt, int $id, string $log)
    {
        Logger::info("Updating item in $tbl table with ID $id: $log");

        if (!$smt->execute())
        {
            $error_info = $smt->errorInfo();

            if ($error_info[0] === "23000")
            {
                Logger::warning("Item already exists in $tbl. Error info: " . Logger::obj($error_info));
                Logger::warning("Details: $log");
                throw new DatabaseException("Duplicate already exists. Please change your edit and try again", 400, $error_info);
            }
            else
            {
                Logger::error("Item update failed in $tbl. Error info: " . Logger::obj($error_info));
                Logger::error("Details: $log");
                throw new DatabaseException("An unknown error has occurred. Contact the system administrator if you believe this is a bug.", 500, $error_info);
            }
        }

        // Since this is a static method, it will return the same PDO object that was used to build the statement.
        $pdo = self::getInstance()->pdo;

        Logger::info("Update complete.");
        Logger::info("Update complete for id $id", Verbosity::MED);
    }

    // Get the IDs of an object that depends on the current object.
    private static function getChildrenIDs(string $table, int $id, string $dependantTable, string $dependantIdName): array
    {
        Logger::info("Retrieving dependant items in of $table table with ID $id from $dependantTable");
        $query = "SELECT id FROM $dependantTable WHERE $dependantIdName=:id";
        $smt = self::getInstance()->pdo->prepare($query);
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Could not retrieve from $dependantTable as dependant objects of $table. Error info: " . Logger::obj($smt->errorInfo()));
            Logger::error("Item ID: $id");
        }

        $ids = flattenResult($smt->fetchAll(PDO::FETCH_NUM));
        Logger::info("Dependant item IDs found: " . Logger::obj($ids));
        return $ids;
    }

    /**
     * Delete an object that has other objects that depend on it. For example, call this with a request object, since
     * it has attachments that depend on it.
     * @throws DatabaseException
     */
    public static function deleteWithChildren(string $table, int $id, string $dependantType, string $dependantTable, string $dependantIdName)
    {
        // The dependant type must implement the DAODeletable interface. However, PHP doesn't support polymorphism for
        // this use case, so we'll use reflection to ensure the method exists
        try
        {
            $r = new ReflectionClass($dependantType);
            $dependant = $r->newInstanceWithoutConstructor();

            if (!$r->implementsInterface(DAODeletable::class))
            {
                Logger::error("$dependantType does not implement " . DAODeletable::class);
                API::error(500, "An internal error has occurred. Please contact the system administrator.");
            }
        }
        catch (ReflectionException $e)
        {
            API::error(500, "An internal error has occurred. Please contact the system administrator.");
        }

        foreach (self::getChildrenIDs($table, $id, $dependantTable, $dependantIdName) as $dependantId)
            $dependant::deleteByID($dependantId);

        self::deleteLeaf($table, $id);
    }

    /**
     * Delete an object that has nothing depending on it.
     * @throws DatabaseException
     */
    public static function deleteLeaf(string $table, int $id)
    {
        Logger::info("Starting deletion from $table for $id");
        $query = "DELETE FROM $table WHERE id=:id";
        $smt = self::getInstance()->pdo->prepare($query);
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Could not delete from $table: " . Logger::obj($smt->errorInfo()));
            Logger::error("Item ID: $id");
            throw new DatabaseException("Could not complete deletion", 500, $smt->errorInfo());
        }
        else
        {
            Logger::info("Deletion from $table completed successfully for $id", Verbosity::MED);
        }
    }

    /**
     * @throws DatabaseException
     */
    public static function deactivateWithChildren(string $table, int $id, string $dependantType, string $dependantTable, string $dependantIdName)
    {
        // The dependant type must implement the DAODeactivatable interface. However, PHP doesn't support polymorphism for
        // this use case, so we'll use reflection to ensure the method exists
        try
        {
            $r = new ReflectionClass($dependantType);
            $dependant = $r->newInstanceWithoutConstructor();

            if (!$r->implementsInterface(DAODeactivatable::class))
            {
                Logger::error("$dependantType does not implement " . DAODeactivatable::class);
                API::error(500, "An internal error has occurred. Please contact the system administrator.");
            }
        }
        catch (ReflectionException $e)
        {
            API::error(500, "An internal error has occurred. Please contact the system administrator.");
        }

        foreach (self::getChildrenIDs($table, $id, $dependantTable, $dependantIdName) as $dependantId)
            $dependant::deactivateByID($dependantId);

        self::deactivateLeaf($table, $id);
    }

    /**
     * Deactivate an object that has nothing depending on it.
     * @throws DatabaseException
     */
    public static function deactivateLeaf(string $table, int $id)
    {
        Logger::info("Setting $id from $table inactive");
        $query = "UPDATE $table SET active=false WHERE id=:id";
        $smt = self::getInstance()->pdo->prepare($query);
        $smt->bindParam(":id", $id, PDO::PARAM_INT);

        if (!$smt->execute())
        {
            Logger::error("Could not deactivate in $table: " . Logger::obj($smt->errorInfo()));
            Logger::error("Item ID: $id");
            throw new DatabaseException("Could not complete deactivation", 500, $smt->errorInfo());
        }
        else
        {
            Logger::info("Deactivation from $table completed successfully for $id", Verbosity::MED);
        }
    }
}
