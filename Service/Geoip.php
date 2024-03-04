<?php
namespace Fontai\Bundle\GeoipBundle\Service;

use GeoIp2\Database\Reader;


class Geoip
{
  protected $reader;

  public function __construct($cacheDir)
  {
    $this->reader = new Reader($cacheDir . '/../../GeoLite2-City.mmdb');
  }

  public function getLocationForIp($ip)
  {
    try
    {
      return $this->reader->city($ip);
    }
    catch(\Exception $e)
    {
      return FALSE;
    }
  }
}