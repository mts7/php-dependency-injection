<?php

declare(strict_types=1);

namespace MtsDependencyInjection\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class MissingContainerDefinitionException extends Exception implements NotFoundExceptionInterface
{
}
