<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\Exercise;
use App\Domain\User\ExerciseRepository;
use App\Domain\User\User;

use App\Application\Settings\SettingsInterface;

/**
 * Requires some values to be set in /private/config.php
 * 
 *  db_host      - Hostname of database server.
 *  db_name      - Database name.
 *  db_username  - Database user.
 *  db_password  - Database password.
 * 
 * Also, the exercise table must be set up beforehand.
 * 
 *  exercise_id    int  primary_key
 *  user_id        int
 *  description    text
 *  duration       int
 *  date           varchar
 */
class DatabaseExerciseRepository implements ExerciseRepository
{
    private $db;
    private $table = 'fccmsv_exercises';

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
    public function add(string $description, int $duration, string $date, User $user): ?Exercise
    {
        $stmt = $this->db->prepare("INSERT INTO $this->table (user_id, description, duration, date) VALUES (?, ?, ?, ?)");
        $stmt->bindParam(1, $user->id, \PDO::PARAM_INT);
        $stmt->bindParam(2, $description, \PDO::PARAM_STR);
        $stmt->bindParam(3, $duration, \PDO::PARAM_INT);
        $stmt->bindParam(4, $date, \PDO::PARAM_STR);
        if (!$stmt->execute())
        {
            return null;
        }

        // Get the id from the DB.
        $exercise_id = (int) $this->db->lastInsertId();
        $exercise = new Exercise($exercise_id, $description, $duration, $date, $user);
        return $exercise;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function findAllById(int $user_id, string $from_date = null, string $to_date = null, int $limit = 0): array
    {
        // TODO: This is dumb.

        $date_range = "";
        if(isset($from_date)) $date_range = " AND date >= :from";
        if(isset($to_date))   $date_range .= " AND date <= :to";
        if($limit > 0)
        {
            $limiter = " LIMIT :limit";
        }
        else
        {
            $limiter = "";
        }

        $stmt = $this->db->prepare("SELECT * FROM $this->table WHERE `user_id` = :user$date_range$limiter");
        $stmt->bindParam('user', $user_id, \PDO::PARAM_INT);
        if(isset($from_date)) $stmt->bindParam('from', $from_date, \PDO::PARAM_STR);
        if(isset($to_date))   $stmt->bindParam('to', $to_date, \PDO::PARAM_STR);
        if($limit > 0)        $stmt->bindParam('limit', $limit, \PDO::PARAM_INT);

        if (!$stmt->execute())
        {
            return [];
        }

        $raw_exercises = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $exercises = [];
        foreach ($raw_exercises as $raw_exercise)
        {
            array_push($exercises, [
                'description' => $raw_exercise['description'],
                'duration' => $raw_exercise['duration'],
                'date' =>  date_format(date_create($raw_exercise['date']), 'D M d Y') 
            ]);
        }
        
        return $exercises;
    }
}
