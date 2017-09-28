<?php
namespace Jedi\Features\Documents\Models;

use Jedi\Models\AbstractModel;


class DocumentImagesModel extends AbstractModel
{
    protected $table  = 'document_images';

    protected $guards = ['id'];
}
