<?php

namespace App\Service\Commission\Bin;

use App\Dto\BinRequestDto;
use App\Dto\BinResponseDto;

interface BinContextInterface
{
    public function getItem(BinRequestDto $binRequestDto): BinResponseDto;
}
