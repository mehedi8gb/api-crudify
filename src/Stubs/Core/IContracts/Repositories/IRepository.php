<?php

namespace App\IContracts\Repositories;

interface IRepository extends IReadRepository, IWriteRepository
{
    // Combines read/write responsibilities
}
