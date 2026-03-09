<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_starter_content\Unit;

use Drupal\Component\Serialization\Yaml;
use Drupal\Tests\UnitTestCase;

/**
 * Tests integrity of config_devel declarations.
 *
 * This is implemented as a unit test which simply reads yaml files, to avoid a
 * Drupal bootstrap when it is not needed.
 */
class ConfigDevelIntegrityTest extends UnitTestCase {

  /**
   * Asserts that config devel declarations are aligned with actual files.
   */
  public function testConfigDevelAlignment(): void {
    $modules = [
      'oe_starter_content_event',
      'oe_starter_content_news',
      'oe_starter_content_person',
      'oe_starter_content_publication',
    ];
    $actual_modules = array_values(preg_grep('#^\w+$#', scandir($this->getPackageDir() . '/modules')));
    sort($actual_modules);
    $this->assertSameListOfNames($modules, $actual_modules);
    foreach ($modules as $module) {
      $module_dir = $this->getPackageDir() . '/modules/' . $module;
      $info = $this->loadYaml($module_dir . '/' . $module . '.info.yml');
      foreach (['install', 'optional'] as $type) {
        $config_devel_names = $info['config_devel'][$type] ?? [];
        $config_dir = $module_dir . '/config/' . $type;
        $yaml_file_names = is_dir($config_dir)
          ? array_values(preg_grep('#\.yml$#', scandir($config_dir)))
          : [];
        sort($yaml_file_names);
        $this->assertSameListOfNames(
          array_map(
            fn (string $yaml_name) => basename($yaml_name, '.yml'),
            $yaml_file_names,
          ),
          $config_devel_names,
          $config_dir,
        );
      }
    }
  }

  /**
   * Gets the Drupal core directory.
   */
  protected function getDrupalCoreDir(): string {
    return dirname((new \ReflectionClass(\Drupal::class))->getFileName(), 2);
  }

  /**
   * Gets the package directory of this module package.
   */
  protected function getPackageDir(): string {
    return dirname(__DIR__, 3);
  }

  /**
   * Loads a yaml file.
   *
   * @param string $path
   *   File path.
   *
   * @return array|null
   *   Decoded yaml data, or NULL if not found.
   */
  protected function loadYaml(string $path): array|null {
    if (!file_exists($path)) {
      return NULL;
    }
    $yaml = file_get_contents($path);
    return Yaml::decode($yaml);
  }

  /**
   * Asserts that two lists of strings are the same.
   *
   * This is useful for a cleaner failure output.
   *
   * @param mixed $expected
   *   The expected value.
   * @param mixed $actual
   *   The actual value.
   * @param string $message
   *   A message to show with assertion failures.
   */
  protected function assertSameListOfNames(mixed $expected, mixed $actual, string $message = ''): void {
    $this->assertIsListBc($expected);
    $this->assertIsListBc($actual);
    $expected_sorted = $expected;
    $actual_sorted = $actual;
    sort($expected_sorted);
    sort($actual_sorted);
    // Compare sorted lists first, to identify missing values.
    $this->assertSameYaml($expected_sorted, $actual_sorted, $message . ' (sorted)');
    // Compare the unsorted lists, to detect unexpected order.
    $this->assertSameYaml($expected, $actual, $message . ' (unsorted)');
    $this->assertSame($expected, $actual, $message);
  }

  /**
   * Asserts that two values are the same when exported to yaml.
   *
   * This is useful for a cleaner failure output without numeric indices.
   *
   * @param mixed $expected
   *   The expected value.
   * @param mixed $actual
   *   The actual value.
   * @param string $message
   *   A message to show with assertion failures.
   */
  protected function assertSameYaml(mixed $expected, mixed $actual, string $message = ''): void {
    $this->assertSame(
      "\n" . Yaml::encode($expected),
      "\n" . Yaml::encode($actual),
      $message,
    );
  }

  /**
   * Asserts that an array is a list, as backport for PhpUnit 9.
   *
   * @param mixed $array
   *   The array to check.
   * @param string $message
   *   A message to show with failures.
   *
   * @phpstan-assert list<mixed> $array
   *
   * @todo Remove this when we drop Drupal 10 support.
   */
  protected function assertIsListBc(mixed $array, string $message = ''): void {
    // Rely on 'symfony/polyfill-php81' for PHP 8.1 support.
    $this->assertTrue(array_is_list($array), $message);
  }

}
