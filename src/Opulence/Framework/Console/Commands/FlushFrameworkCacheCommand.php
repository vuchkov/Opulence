<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Opulence\Framework\Console\Commands;

use Opulence\Console\Commands\Command;
use Opulence\Console\Responses\IResponse;
use Opulence\Views\Caching\ICache as ViewCache;

/**
 * Defines the command that flushes the framework's cache
 */
class FlushFrameworkCacheCommand extends Command
{
    /** @var ViewCache The view cache */
    private ViewCache $viewCache;

    /**
     * @param ViewCache $viewCache The view cache
     */
    public function __construct(ViewCache $viewCache)
    {
        parent::__construct();

        $this->viewCache = $viewCache;
    }

    /**
     * @inheritdoc
     */
    protected function define(): void
    {
        $this->setName('framework:flushcache')
            ->setDescription("Flushes all of the framework's cached files");
    }

    /**
     * @inheritdoc
     */
    protected function doExecute(IResponse $response)
    {
        $this->flushBootstrapperCache($response);
        $this->flushViewCache($response);
        $response->writeln('<success>Framework cache flushed</success>');
    }

    /**
     * Flushes the bootstrapper cache
     *
     * @param IResponse $response The response to write to
     */
    private function flushBootstrapperCache(IResponse $response): void
    {
        // Todo: Need to make this work with new way of caching bootstrappers in 2.0

        $response->writeln('<info>Bootstrapper cache flushed</info>');
    }

    /**
     * Flushes the view cache
     *
     * @param IResponse $response The response to write to
     */
    private function flushViewCache(IResponse $response): void
    {
        $this->viewCache->flush();
        $response->writeln('<info>View cache flushed</info>');
    }
}
