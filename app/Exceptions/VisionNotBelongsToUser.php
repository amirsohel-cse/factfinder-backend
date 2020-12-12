<?php

namespace App\Exceptions;

use Exception;

class VisionNotBelongsToUser extends Exception
{
    public function render(){
        return [
            'errors' => 'Vision not belongs to user',
        ];
    }
}
