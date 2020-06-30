<?php
/*
Plugin Name:         VASSAL delete original uploaded media file
Description:         This plugin replaces original file with largest resized image when uploading media to wordpress
Author:             Jonathan Leenman
Author URI:         https://github.com/jleenman/
GitHub Plugin URI:	https://github.com/jleenman/vassal-delete-original
Version:			       1.0

Source:         https://wordpress.stackexchange.com/questions/20702/delete-original-image-keep-thumbnail
Note:         I just made it as a plugin to more easily install and maintain
*/

function replace_uploaded_image($image_data) {
    // if there is no large image : return
    if (!isset($image_data['sizes']['large'])) return $image_data;

    // paths to the uploaded image and the large image
    $upload_dir = wp_upload_dir();
    $uploaded_image_location = $upload_dir['basedir'] . '/' .$image_data['file'];
    $large_image_filename = $image_data['sizes']['large']['file'];

    // Do what wordpress does in image_downsize() ... just replace the filenames ;)
    $image_basename = wp_basename($uploaded_image_location);
    $large_image_location = str_replace($image_basename, $large_image_filename, $uploaded_image_location);

    // delete the uploaded image
    unlink($uploaded_image_location);

    // rename the large image
    rename($large_image_location, $uploaded_image_location);

    // update image metadata and return them
    $image_data['width'] = $image_data['sizes']['large']['width'];
    $image_data['height'] = $image_data['sizes']['large']['height'];
    unset($image_data['sizes']['large']);

    // Check if other size-configurations link to the large-file
    foreach($image_data['sizes'] as $size => $sizeData) {
        if ($sizeData['file'] === $large_image_filename)
            unset($image_data['sizes'][$size]);
    }

    return $image_data;
}
add_filter('wp_generate_attachment_metadata', 'replace_uploaded_image');

?>
