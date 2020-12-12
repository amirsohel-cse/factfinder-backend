<?php

namespace App\Exceptions;

use Exception;

class TaskNotBelongsToUser extends Exception
{
    public function render(){
        return [
            'errors' => 'Task not belongs to user',
        ];
    }
}
