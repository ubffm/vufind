<?php
namespace Fiddk\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use VuFind\Record\Loader as RecordLoader;

class RecordExists extends AbstractHelper
{
    public function __construct(private RecordLoader $loader) {}

    public function __invoke(?string $id, array|string $sources = 'Solr'): bool
    {
        if ($id === null || $id === '') {
            return false;
        }
        $sources = is_array($sources) ? $sources : [$sources];

        foreach ($sources as $source) {
            try {
                $this->loader->load($id, $source);
                return true;
            } catch (\Throwable $e) {
                // try next
            }
        }
        return false;
    }

}

