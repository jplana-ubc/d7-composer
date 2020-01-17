<?php

/**
 * @file
 * Find patches, move them to folder and add it to composer.patches.json.
 */

// Create patches folder.
$patch_folder = 'patches';
if (!mkdir($patch_folder, 0755)) {
  echo 'Could not create patches folder! Exiting ...' . PHP_EOL;
  die;
}

$patches = [];

$di = new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($di);
foreach ($it as $file) {
  if (pathinfo($file, PATHINFO_EXTENSION) == "patch") {
    // Consider only contrib modules.
    if (strpos($file, 'public/sites/all/modules/contrib') === FALSE) {
      continue;
    }

    // Move file.
    $filename = pathinfo($file, PATHINFO_FILENAME);
    echo 'Moving ' . $filename . ' to ' . __DIR__ . DIRECTORY_SEPARATOR . $patch_folder . DIRECTORY_SEPARATOR . $filename . '.patch' . PHP_EOL;
    $move = rename($file, __DIR__ . DIRECTORY_SEPARATOR . $patch_folder . DIRECTORY_SEPARATOR . $filename . '.patch');
    if (!$move) {
      echo 'Failed!' . PHP_EOL;
      continue;
    }

    // Get module name.
    $path_array = explode('/', $file);
    end($path_array);
    $module = prev($path_array);

    // Add to composer.patches.json.
    $patches['patches']['drupal/' . $module][$filename] = $patch_folder . DIRECTORY_SEPARATOR . $filename . '.patch';

  }
}
if (!empty($patches)) {
  $patches_file = fopen('composer.patches.json', 'w') or die('Unable to open file!');
  fwrite($patches_file, json_encode($patches, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
  fclose($patches_file);
}
