<?php

namespace Drupal\schoolprofiler\Service;

use Drupal\Core\Database\Connection;

/**
 * Class GeoCacheService
 *
 * Provides methods to save and retrieve data from schoolfinder_geo_cache table.
 */
class GeoCacheService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Save data to the schoolprofiler table.
   *
   * @param string $location
   *   The location data to save.
   * @param string $geo_data
   *   The geo data associated with the location.
   *
   * @return int
   *   The ID of the newly inserted record.
   */
  public function saveGeoCache($location, $geo_data) {
    return $this->database->insert('schoolfinder_geo_cache')
      ->fields([
        'location' => $location,
        'geo_data' => json_encode($geo_data),
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();
  }

  /**
   * Retrieve geo data from the schoolprofiler table.
   *
   * @param string $location
   *   The location to look for.
   *
   * @return object|false
   *   The geo data if found, or FALSE if not.
   */
  public function getGeoCache($location) {
    $query = $this->database->select('schoolfinder_geo_cache', 'g')
      ->fields('g', ['location', 'geo_data', 'created'])
      ->condition('location', $location)
      ->execute();

    $result = $query->fetchObject();

    if ($result){
	    return $result->geo_data ; 
    }

    return false  ; 
  }

}


