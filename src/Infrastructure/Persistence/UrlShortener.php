<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

/**
 * Requires some values to be set in /private/config.php
 * 
 *  urls_host        - Hostname of database server.
 *  urls_username    - Database user.
 *  urls_password    - Database password.
 *  urls_dbname      - Database name.
 *  urls_meta_table  - Database table for storing next_index and max_index.
 *  urls_links_table - Database table for storing full URLs and their shortened versions.
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
    private $meta_table;
    private $links_table;

    public function __construct()
    {
        global $private_config;

        $host = $private_config['urls_host'];
        $dbname = $private_config['urls_dbname'];

        $this->db = new \PDO(
            "mysql:host=$host;dbname=$dbname",
            $private_config['urls_username'],
            $private_config['urls_password']
        );

        $this->meta_table = $private_config['urls_meta_table'];
        $this->links_table = $private_config['urls_links_table'];
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
