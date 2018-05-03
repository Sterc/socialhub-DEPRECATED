<?php
$xpdo_meta_map['SocialHubItem']= array (
  'package' => 'socialhub',
  'version' => '0.1',
  'table' => 'socialhub_items',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'source' => '',
    'source_id' => NULL,
    'source_type' => NULL,
    'language' => NULL,
    'avatar' => '',
    'username' => '',
    'fullname' => '',
    'content' => '',
    'image' => '',
    'link' => '',
    'date' => NULL,
    'active' => 0,
    'data' => NULL,
  ),
  'fieldMeta' => 
  array (
    'source' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'source_id' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
    ),
    'source_type' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
    ),
    'language' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
    ),
    'avatar' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'username' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'fullname' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'content' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'image' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'link' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'date' => 
    array (
      'dbtype' => 'timestamp',
      'phptype' => 'timestamp',
    ),
    'active' => 
    array (
      'dbtype' => 'int',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 0,
    ),
    'data' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
      'default' => NULL,
    ),
  ),
);
