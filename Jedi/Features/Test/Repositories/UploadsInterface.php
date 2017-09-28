<?php
namespace Jedi\Features\Documents\Repositories;

use Illuminate\Database\Eloquent\Model;

interface UploadsInterface
{
    public function upload($s3, $uploads, array $attributes);
    public function copy_image($s3, array $images);
    public function getUrl($s3, $file_name, $expiration = false);
    public function download($s3, $key, $filename);
    public function validate_inputs (array $inputs);
}