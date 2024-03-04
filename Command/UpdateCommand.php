<?php
namespace Fontai\Bundle\GeoipBundle\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;


class UpdateCommand extends Command
{
  protected $filesystem;
  protected $cacheDir;
  protected $licenseKey;

  public function __construct(
    Filesystem $filesystem,
    string $cacheDir,
    string $licenseKey
  )
  {
    $this->filesystem = $filesystem;
    $this->cacheDir = $cacheDir;
    $this->licenseKey = $licenseKey;

    parent::__construct();
  }

  protected function configure()
  {
    $this
    ->setDescription('Updates GeoIP database.')
    ->setHelp('This command allows you to update GeoIP database.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    try
    {
      $tmpPathTar = $this->cacheDir . DIRECTORY_SEPARATOR . 'geolite.tar';
      $tmpPathGz  = $tmpPathTar . '.gz';
      $dbFileName = 'GeoLite2-City.mmdb';
      $dstPath    = implode(DIRECTORY_SEPARATOR, [$this->cacheDir, '..', '..', $dbFileName]);

      $client = new Client();
      $res    = $client->request(
        'GET',
        sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', $this->licenseKey),
        ['sink' => $tmpPathGz]
      );

      $header = $res->getHeader('Content-Disposition')[0];

      if (preg_match('~filename=([a-zA-Z0-9_\-]+)\.tar\.gz~', $header, $matches))
      {
        $archivePath = $this->cacheDir . DIRECTORY_SEPARATOR . $matches[1];
      
        if (is_file($tmpPathTar))
        {
          unlink($tmpPathTar);
        }

        $p = new \PharData($tmpPathGz);
        $p->decompress();
        unset($p);

        $p = new \PharData($tmpPathTar);
        $p->extractTo($this->cacheDir);
        unset($p);

        if (is_file($dstPath))
        {
          unlink($dstPath);
        }

        rename($archivePath . DIRECTORY_SEPARATOR . $dbFileName, $dstPath);

        $this->filesystem->remove($archivePath);
      }

      $output->writeln('GeoIP database successfully updated.');
    }
    catch (Exception $e)
    {
      $output->writeln('Cannot update GeoIP database.');

      return 1;
    }

    return 0;
  }
}