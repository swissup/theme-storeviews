<?php
/**
 * Copyright © Swissup All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Swissup\ThemeStoreViews\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class CreateThemeStoreViews extends Command
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var StoreInterfaceFactory
     */
    private $storeFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var StoreCollectionFactory
     */
    private $storeCollectionFactory;

    /**
     * @var WebsiteCollectionFactory
     */
    private $websiteCollectionFactory;

    /**
     * @var GroupCollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var ThemeCollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var Pool
     */
    private $cacheFrontendPool;

    /**
     * List of themes to create store views for
     */
    private $themes = [
        'Swissup/absolute',
        'Swissup/argento-blank',
        'Swissup/argento-essence',
        'Swissup/argento-flat',
        'Swissup/argento-force',
        'Swissup/argento-home',
        'Swissup/argento-luxury',
        'Swissup/argento-mall',
        'Swissup/argento-marketplace',
        'Swissup/argento-pure2',
        'Swissup/argento-stripes',
        'Swissup/argentobreeze-blank',
        'Swissup/argentobreeze-business',
        'Swissup/argentobreeze-chic',
        'Swissup/argentobreeze-force',
        'Swissup/argentobreeze-stripes',
        'Swissup/breeze-blank',
        'Swissup/breeze-evolution'
    ];

    /**
     * @param State $appState
     * @param StoreInterfaceFactory $storeFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param WebsiteCollectionFactory $websiteCollectionFactory
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param ThemeCollectionFactory $themeCollectionFactory
     * @param WriterInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        State $appState,
        StoreInterfaceFactory $storeFactory,
        StoreRepositoryInterface $storeRepository,
        StoreCollectionFactory $storeCollectionFactory,
        WebsiteCollectionFactory $websiteCollectionFactory,
        GroupCollectionFactory $groupCollectionFactory,
        ThemeCollectionFactory $themeCollectionFactory,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->appState = $appState;
        $this->storeFactory = $storeFactory;
        $this->storeRepository = $storeRepository;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        parent::__construct();
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('swissup:theme:create-storeviews')
            ->setDescription('Create store views for each Swissup theme and apply corresponding theme to them')
            ->addOption(
                'reinstall',
                'r',
                InputOption::VALUE_NONE,
                'Run theme installers even for existing storeviews'
            );
    }

    /**
     * Execute the command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area code already set
        }

        $output->writeln('<info>Creating store views for Swissup themes...</info>');

        // Get default website and store group
        $website = $this->websiteCollectionFactory->create()
            ->addFieldToFilter('is_default', 1)
            ->getFirstItem();

        $storeGroup = $this->groupCollectionFactory->create()
            ->addFieldToFilter('website_id', $website->getId())
            ->getFirstItem();

        // Create progress bar
        $progressBar = new ProgressBar($output, count($this->themes));
        $progressBar->start();

        $createdStores = 0;
        $skippedStores = 0;
        $reinstall = $input->getOption('reinstall');

        foreach ($this->themes as $themeCode) {
            $progressBar->advance();

            // Generate store view code from theme code
            $storeViewCode = str_replace('Swissup/', '', $themeCode);
            $storeViewCode = str_replace('/', '_', $storeViewCode);
            $storeViewCode = str_replace('-', '_', $storeViewCode);
            
            // Ensure the code starts with a letter
            if (is_numeric($storeViewCode[0])) {
                $storeViewCode = 'theme_' . $storeViewCode;
            }

            // Check if store view already exists
            $existingStore = $this->storeCollectionFactory->create()
                ->addFieldToFilter('code', $storeViewCode)
                ->getFirstItem();

            if ($existingStore->getId() && !$reinstall) {
                $skippedStores++;
                continue;
            }

            // Find the theme
            $theme = $this->themeCollectionFactory->create()
                ->addFieldToFilter('code', $themeCode)
                ->getFirstItem();

            if (!$theme->getId()) {
                $output->writeln(PHP_EOL . "<warning>Theme '{$themeCode}' not found. Skipping...</warning>");
                $skippedStores++;
                continue;
            }

            // Create store view if it doesn't exist
            if (!$existingStore->getId()) {
                $store = $this->storeFactory->create();
                $store->setCode($storeViewCode);
                $store->setName($theme->getThemeTitle());
                $store->setWebsiteId($website->getId());
                $store->setGroupId($storeGroup->getId());
                $store->setIsActive(1);
                $store->setSortOrder(10);

                $store->save();
                $createdStores++;
            } else {
                $store = $existingStore;
            }

            // Apply theme to store view
            $this->configWriter->save(
                'design/theme/theme_id',
                $theme->getId(),
                'stores',
                $store->getId()
            );

            // Run theme installer if exists
            $this->runThemeInstaller($theme, $store, $output);
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL);

        $output->writeln("<info>Created {$createdStores} store views, skipped {$skippedStores} existing ones.</info>");

        if ($createdStores > 0) {
            $output->writeln('<info>Running setup:upgrade...</info>');
            $this->runCommand('php -d memory_limit=-1 bin/magento setup:upgrade --safe-mode=1', $output);

            $output->writeln('<info>Running reindex...</info>');
            $this->runCommand('php -d memory_limit=-1 bin/magento indexer:reindex', $output);

            $output->writeln('<info>Clearing cache...</info>');
            $this->clearCache();
        }

        $output->writeln('<info>Process completed successfully!</info>');

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Run theme installer if marketplace installer.xml exists
     */
    private function runThemeInstaller($theme, $store, OutputInterface $output)
    {
        $themePath = $theme->getThemePath(); // e.g., "Swissup/argento-stripes"
        
        // Check for installer in vendor directory first
        $vendorInstallerPath = BP . '/vendor/swissup/theme-frontend-' . str_replace('Swissup/', '', $themePath) . '/etc/marketplace/installer.xml';
        
        // Check for installer in app/design directory
        $designInstallerPath = BP . '/app/design/frontend/' . $themePath . '/etc/marketplace/installer.xml';
        
        $installerPath = null;
        if (file_exists($vendorInstallerPath)) {
            $installerPath = $vendorInstallerPath;
        } elseif (file_exists($designInstallerPath)) {
            $installerPath = $designInstallerPath;
        }
        
        if ($installerPath) {
            $output->writeln("<info>Found installer for theme '{$theme->getThemeTitle()}', running installation...</info>");
            
            // Get theme package name from installer.xml
            $xml = simplexml_load_file($installerPath);
            if ($xml && isset($xml->packages->package)) {
                foreach ($xml->packages->package as $package) {
                    $packageName = (string)$package;
                    if (strpos($packageName, 'theme-frontend-') !== false) {
                        $storeId = $store->getId();
                        $this->runCommand("php bin/magento marketplace:package:install {$packageName} --store={$storeId} --no-interaction", $output);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Run shell command
     */
    private function runCommand($command, OutputInterface $output)
    {
        $output->writeln("<comment>Running: {$command}</comment>");
        
        $process = popen($command, 'r');
        if ($process) {
            while (!feof($process)) {
                $line = fgets($process);
                if ($line) {
                    $output->write($line);
                }
            }
            pclose($process);
        }
    }

    /**
     * Clear cache
     */
    private function clearCache()
    {
        $types = [
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'compiled_config',
            'eav',
            'customer_notification',
            'config_integration',
            'config_integration_api',
            'full_page',
            'config_webservice',
            'translate'
        ];

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
