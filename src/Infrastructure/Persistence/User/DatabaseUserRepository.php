<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Domain\User\UserNotFoundException;

use App\Application\Settings\SettingsInterface;

/**
 * Requires some values to be set in /private/config.php
 * 
 *  db_host      - Hostname of database server.
 *  db_name      - Database name.
 *  db_username  - Database user.
 *  db_password  - Database password.
 * 
 * Also, the user table must be set up beforehand.
 * 
 *  user_id        int      primary_key
 *  username  varchar
 */
class DatabaseUserRepository implements UserRepository
{
    private $db;
    private $table = 'fccmsv_users';

    /**
     *
     */
    public function __construct(SettingsInterface $settings)
    {
        $dbs = $settings->require('database');
        $host = $dbs['db_host'];
        $name = $dbs['db_name'];

        $this->db = new \PDO(
            "mysql:host=$host;dbname=$name",
            $dbs['db_username'],
            $dbs['db_password']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $username): ?User
    {
        $stmt = $this->db->prepare("INSERT INTO $this->table (username) VALUES (?)");
        if (!$stmt->execute([$username]))
        {
            return null;
        }

        // Get the id from the DB.
        $id = (int) $this->db->lastInsertId();
        $user = new User($id, $username);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM $this->table");
        $raw_users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $users = [];
        foreach ($raw_users as $raw_user)
        {
            array_push($users, new User($raw_user['user_id'], $raw_user['username']));
        }
        
        return $users;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserOfId(int $user_id): User
    {
        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE `user_id` = ?");
        $stmt->bindParam(1, $user_id, \PDO::PARAM_INT);
        if (!$stmt->execute() || $stmt->rowCount() == 0)
        {
            throw new UserNotFoundException();
        }

        $raw_user = $stmt->fetch(\PDO::FETCH_ASSOC);
        $user = new User($raw_user['user_id'], $raw_user['username']);

        return $user;
    }
}
