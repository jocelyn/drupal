<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Database\UpdateLobTest.
 */

namespace Drupal\system\Tests\Database;

/**
 * Test update queries involving LOB values.
 */
class UpdateLobTest extends DatabaseTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Update tests, LOB',
      'description' => 'Test the Update query builder with LOB fields.',
      'group' => 'Database',
    );
  }

  /**
   * Confirm that we can update a blob column.
   */
  function testUpdateOneBlob() {
    $data = "This is\000a test.";
    $this->assertTrue(strlen($data) === 15, t('Test data contains a NULL.'));
    $id = db_insert('test_one_blob')
      ->fields(array('blob1' => $data))
      ->execute();

    $data .= $data;
    db_update('test_one_blob')
      ->condition('id', $id)
      ->fields(array('blob1' => $data))
      ->execute();

    $r = db_query('SELECT * FROM {test_one_blob} WHERE id = :id', array(':id' => $id))->fetchAssoc();
    $this->assertTrue($r['blob1'] === $data, t('Can update a blob: id @id, @data.', array('@id' => $id, '@data' => serialize($r))));
  }

  /**
   * Confirm that we can update two blob columns in the same table.
   */
  function testUpdateMultipleBlob() {
    $id = db_insert('test_two_blobs')
      ->fields(array(
        'blob1' => 'This is',
        'blob2' => 'a test',
      ))
      ->execute();

    db_update('test_two_blobs')
      ->condition('id', $id)
      ->fields(array('blob1' => 'and so', 'blob2' => 'is this'))
      ->execute();

    $r = db_query('SELECT * FROM {test_two_blobs} WHERE id = :id', array(':id' => $id))->fetchAssoc();
    $this->assertTrue($r['blob1'] === 'and so' && $r['blob2'] === 'is this', t('Can update multiple blobs per row.'));
  }
}
