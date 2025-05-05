<?php

namespace App\Service\Commission\Bin\Strategy;

use App\Dto\BinRequestDto;
use App\Dto\BinResponseDto;

interface BinGetterInterface
{
    public function supports(BinRequestDto $binRequestDto): bool;

    public function getItem(BinRequestDto $binRequestDto): BinResponseDto;
}
