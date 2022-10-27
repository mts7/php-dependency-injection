<?php

declare(strict_types=1);

namespace MtsDependencyInjection\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends Exception implements ContainerExceptionInterface
{
}
