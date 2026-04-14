<?php

namespace App\Services\AbstractServices;

use App\Repositories\RepositoryInterface;
use App\Services\FileService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

abstract class AbstractBalanceService
{
    protected FileService $fileService;
    protected RepositoryInterface $balanceRepository;

    public function __construct(RepositoryInterface $balanceRepository)
    {
        $this->balanceRepository = $balanceRepository;
    }
}
