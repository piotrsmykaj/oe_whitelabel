<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_whitelabel\Kernel;

use Drupal\Tests\token\Kernel\KernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the Site Branding Block rendering.
 */
class SiteBrandingBlockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container->get('theme_installer')->install(['oe_whitelabel']);
    $this->container->get('theme_handler')->setDefault('oe_whitelabel');
    $this->container->set('theme.registry', NULL);
    $this->config('system.site')
      ->set('name', 'Site name')
      ->set('slogan', 'Slogan')
      ->save();

    $this->container->get('cache.render')->deleteAll();
  }

  /**
   * Tests the rendering of blocks.
   */
  public function testBlockRendering(): void {
    $entity_type_manager = $this->container
      ->get('entity_type.manager')
      ->getStorage('block');
    $entity = $entity_type_manager->create([
      'id' => 'test_block',
      'theme' => 'oe_whitelabel',
      'plugin' => 'system_branding_block',
      'settings' => [
        'id' => 'system_branding_block',
        'label' => 'Site branding',
        'provider' => 'system',
        'label_display' => '0',
        'use_site_logo' => TRUE,
        'use_site_name' => TRUE,
        'use_site_slogan' => FALSE,
      ],
    ]);
    $entity->save();
    $builder = \Drupal::entityTypeManager()->getViewBuilder('block');
    $build = $builder->view($entity, 'block');
    $render = $this->container->get('renderer')->renderRoot($build);
    $crawler = new Crawler($render->__toString());

    $actual = $crawler->filter('.site-name.h1.text-white.text-decoration-none.align-bottom');
    $this->assertCount(1, $actual);
    $actual = $crawler->filter('.site-logo');
    $this->assertCount(1, $actual);
    $logo = $actual->filter('img');
    $this->assertCount(1, $logo);
    $expected = '/' . drupal_get_path('theme', 'oe_whitelabel') . '/logo.svg';
    $this->assertSame($expected, $logo->attr('src'));
  }

}
