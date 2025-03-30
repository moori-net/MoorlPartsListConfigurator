<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Shopware\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class PartsListCalculatorException extends HttpException
{
    public const MISSING_REQUEST_PARAMETER_CODE = 'FRAMEWORK__MISSING_REQUEST_PARAMETER';
    public const CALCULATION_ABORTED_CODE = 'FRAMEWORK__CALCULATION_ABORTED_CODE';

    public static function calculationAborted(string $prefix, ?string $groupName = null, ?string $optionName = null): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CALCULATION_ABORTED_CODE,
            'The parameter "{{ parameter }}" is invalid.',
            [
                'groupName' => $groupName,
                'optionName' => $optionName,
                'prefix' => $prefix,
            ]
        );
    }
}
