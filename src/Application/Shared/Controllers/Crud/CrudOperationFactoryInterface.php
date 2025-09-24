<?php

declare(strict_types=1);

namespace App\Application\Shared\Controllers\Crud;

interface CrudOperationFactoryInterface
{
    public function createCreateOperation(): CrudOperationInterface;
    
    public function createUpdateOperation(): CrudOperationInterface;
    
    public function createDeleteOperation(): CrudOperationInterface;
    
    public function createShowOperation(): CrudOperationInterface;
    
    public function createIndexOperation(): CrudOperationInterface;
}
