<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Settings\SettingsInterface;

/**
 * Requires some values to be set in /private/config.php
 * 
 *  db_host      - Hostname of database server.
 *  db_name      - Database name.
 *  db_username  - Database user.
 *  db_password  - Database password.
 * 
 * Also, the database tables must be set up beforehand.
 * The meta table:
 * 
 *  next_index  int  primary_key
 *  max_index   int
 * 
 * The links table:
 * 
 *  index  int   primary_key
 *  url    text
 */
class UrlShortener
{
    private $db;
    private $meta_table = 'fccmsv_urls_meta';
    private $links_table = 'fccmsv_urls_links';

    public function __construct()
    {
        // TODO: ???
        global $settings;
        // ???

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
     * Adds URL to the database and returns the short ID.
     * 
     * @param string $url
     * @return int|bool
     */
    public function add(string $url)
    {
        // Check if this is a valid URL.
        $parsed = parse_url($url);
        if (empty($parsed['scheme']) || empty($parsed['host']))
        {
            // TODO
            return false;
        }

        $host = $parsed['host'];

        // Check if the host points to an IP address.
        $records = dns_get_record($host, DNS_A);
        if (empty($records))
        {
            // TODO
            return false;
        }

        // Get the next and max index.
        $stmt = $this->db->query("SELECT * FROM $this->meta_table");
        [$next_index, $max_index] = $stmt->fetch(\PDO::FETCH_BOTH);
        $short = $next_index;
        
        // Add this URL to the database.
        $stmt = $this->db->prepare("REPLACE INTO $this->links_table VALUES (?, ?)");
        $stmt->execute([$next_index, $url]);

        // Increment next index.
        $next_index++;
        if ($next_index >= $max_index)
            $next_index = 1;

        $stmt = $this->db->prepare("UPDATE $this->meta_table SET `next_index` = ?");
        $stmt->execute([$next_index]);

        return $short;
    }

    /**
     * Retrieves the full URL from the database.
     * 
     * @param string $short
     * @return string|bool
     */
    public function get(string $short)
    {
        $stmt = $this->db->prepare("SELECT `url` FROM $this->links_table WHERE `index` = ?");
        $stmt->bindParam(1, $short, \PDO::PARAM_INT);
        if (!$stmt->execute())
        {
            // TODO: Need some error handling mechanism...
            return false;
        }
        
        $row = $stmt->fetch();
        return $row['url'];
    }
}
