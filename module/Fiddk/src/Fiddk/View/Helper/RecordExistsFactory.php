<?php
namespace Fiddk\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use VuFind\Record\Loader as RecordLoader;

class RecordExistsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $c, $requestedName, array $options = null)
    {
        return new RecordExists($c->get(RecordLoader::class));
    }
}

