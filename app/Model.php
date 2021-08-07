<?php
namespace App;

use LCloss\ActiveRecord;

class Model extends ActiveRecord
{
    protected $log_timestamp = true;
    protected $soft_deletes = true;
}