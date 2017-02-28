<?php

namespace Drupal\sticky_helper\Plugin\Block;

use Drupal\brcontact\BrContactInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a 'StickyHelper' block.
 *
 * @Block(
 *  id = "br_sticky_helper",
 *  admin_label = @Translation("Sticky helper"),
 * )
 */
class StickyHelper extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\brcontact\BrContactInterface
   */
  protected $currentContact;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\brcontact\BrContactInterface $current_contact
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    BrContactInterface $current_contact
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentContact = $current_contact;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('brcontact.current_contact')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $contact_link = [
      '#type' => 'link',
      '#title' => $this->t('Contact us'),
      '#url' => $this->currentContact->getContactUrl(),
      '#attributes' => ['class' => ['contact-link']],
    ];

    $template = <<<TEMPLATE
<span class="icon icon-phone" data-grunticon-embed></span>
<a href="tel:{{ phone }}" class="phone-number">{{ phone }}</a>
{{ contact_link }}
TEMPLATE;

    $build = [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => [
        'phone' => $this->currentContact->getPhoneData('phone'),
        'contact_link' => $contact_link,
      ],
    ];

    // Set the cache data appropriately.
    $build['#cache']['contexts'] = $this->getCacheContexts();
    $build['#cache']['tags'] = $this->getCacheTags();
    $build['#cache']['max-age'] = $this->getCacheMaxAge();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache = new CacheableMetadata();
    $contact_node = $this->currentContact->getCurrentContactNode();
    $cache->addCacheableDependency($contact_node);
    return Cache::mergeTags(parent::getCacheTags(), $cache->getCacheTags());
  }

}
