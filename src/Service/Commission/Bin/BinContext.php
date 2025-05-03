<?php

namespace App\Service\Commission\Bin;

use App\Dto\BinRequestDto;
use App\Dto\BinResponseDto;
use App\Service\Commission\Bin\Strategy\BinGetterInterface;

readonly class BinContext implements BinContextInterface
{
    /**
     * @param iterable<BinGetterInterface> $strategies
     */
    public function __construct(protected iterable $strategies)
    {
    }

    public function getItem(BinRequestDto $binRequestDto): BinResponseDto
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($binRequestDto)) {
                return $strategy->getItem($binRequestDto);
            }
        }

        throw new \LogicException('Failed to get BIN data.');
    }
}
