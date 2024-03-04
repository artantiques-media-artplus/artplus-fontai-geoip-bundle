<?php
namespace Fontai\Bundle\GeoipBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder('geoip');

    $treeBuilder
    ->getRootNode()
      ->children()
        ->scalarNode('license_key')->isRequired()->cannotBeEmpty()->end()
      ->end()
    ->end();

    return $treeBuilder;
  }
}