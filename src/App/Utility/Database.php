<?php
namespace App\Utility;

use App\IO\Output;
use App\IO\Input;
use PDO;
use PDOException;

class Database
{
    /**
     * dbh
     *
     * @var PDO
     */
    public PDO $dbh;

    public function __construct(string $user, string $pass, string $name, string $host)
    {
        $this->dbh = new PDO("mysql:host=$host; dbname=$name", $user, $pass);
        $this->dbh->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this;
    }

    public function runQuery(string $query, $auto_yes = false): array
    {
        if (empty($query)) {
            Output::error('Query passed is empty!');
            return ['status' => 'error'];
        }

        Output::warning('Running query...');
        Output::caption($query);
        try {
            if ((strpos($query, 'CREATE TABLE') !== false || strpos($query, 'DROP')) && $auto_yes === false) {
                $ok = Input::affirm('This query may be auto committed and may require a manual revert. Would you like to proceed? Y/n');
                if (!$ok) {
                    return ['status' => false];
                }
            }
            $this->dbh->beginTransaction();

            $this->dbh->exec($query);
            $err = $this->dbh->errorInfo();

            if (!is_null($err[1])) {
                $err_string = implode(', ', $err);
                Output::multi(['Aborting transaction, errors returned:', $err_string], 'error');
                $this->dbh->rollBack();
                // if query is a create table we need to revert that query, some db will always commit CREATE
                return ['status' => 'error'];
            }

            $this->dbh->commit();
            Output::warning('Successfully ran query');

            return ['status' => gmdate('YmdHis')];
        } catch (PDOException $e) {
            $msg = $e->getMessage();

            Output::error("ERROR: $msg");

            $this->dbh->rollBack();
            return ['status' => 'error'];
        }
    }
}
