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
        'Swissup/argento-chic',
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

        $output->writeln('');
        $output->writeln('<fg=cyan>╔══════════════════════════════════════════════════════════════════════════════════════╗</>');
        $output->writeln('<fg=cyan>║</> <fg=yellow;options=bold>                    🎨 Swissup Theme Store Views Creator                           </> <fg=cyan>║</>');
        $output->writeln('<fg=cyan>╚══════════════════════════════════════════════════════════════════════════════════════╝</>');
        $output->writeln('');
        $output->writeln('<fg=green;options=bold>🚀 Starting theme store views creation process...</>');

        // Get default website and store group
        $website = $this->websiteCollectionFactory->create()
            ->addFieldToFilter('is_default', 1)
            ->getFirstItem();

        $storeGroup = $this->groupCollectionFactory->create()
            ->addFieldToFilter('website_id', $website->getId())
            ->getFirstItem();

        $output->writeln('<fg=blue>📊 Website:</> <fg=white;options=bold>' . $website->getName() . '</> <fg=gray>(ID: ' . $website->getId() . ')</>');
        $output->writeln('<fg=blue>🏪 Store Group:</> <fg=white;options=bold>' . $storeGroup->getName() . '</> <fg=gray>(ID: ' . $storeGroup->getId() . ')</>');
        $output->writeln('');
        
        // Create progress bar
        $output->writeln('<fg=magenta;options=bold>📋 Processing ' . count($this->themes) . ' themes...</>');
        $progressBar = new ProgressBar($output, count($this->themes));
        $progressBar->setFormat('<fg=cyan>%current%/%max%</> [<fg=green>%bar%</>] <fg=yellow>%percent:3s%%</> <fg=white>%message%</>');
        $progressBar->setMessage('Initializing...');
        $progressBar->start();

        $createdStores = 0;
        $skippedStores = 0;
        $processedThemes = 0;
        $reinstall = $input->getOption('reinstall');

        foreach ($this->themes as $themeCode) {
            $processedThemes++;
            $progressBar->setMessage(sprintf('Processing: %s (%d/%d) | Created: %d | Skipped: %d', 
                $themeCode, 
                $processedThemes, 
                count($this->themes), 
                $createdStores, 
                $skippedStores
            ));
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
                $progressBar->setMessage(sprintf('Skipped: %s (%d/%d) | Created: %d | Skipped: %d', 
                    $themeCode, 
                    $processedThemes, 
                    count($this->themes), 
                    $createdStores, 
                    $skippedStores
                ));
                continue;
            }

            // Find the theme
            $theme = $this->themeCollectionFactory->create()
                ->addFieldToFilter('code', $themeCode)
                ->getFirstItem();

            if (!$theme->getId()) {
                $skippedStores++;
                $progressBar->clear();
                $output->writeln('<fg=red;options=bold>⚠️  Theme not found:</> <fg=yellow>' . $themeCode . '</>');
                $progressBar->setMessage(sprintf('Theme not found: %s (%d/%d) | Created: %d | Skipped: %d', 
                    $themeCode, 
                    $processedThemes, 
                    count($this->themes), 
                    $createdStores, 
                    $skippedStores
                ));
                $progressBar->display();
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
                
                $progressBar->clear();
                $output->writeln('<fg=green;options=bold>✅ Created store view:</> <fg=white>' . $theme->getThemeTitle() . '</> <fg=gray>(' . $storeViewCode . ')</>');
                $progressBar->setMessage(sprintf('Created: %s (%d/%d) | Created: %d | Skipped: %d', 
                    $themeCode, 
                    $processedThemes, 
                    count($this->themes), 
                    $createdStores, 
                    $skippedStores
                ));
                $progressBar->display();
            } else {
                $progressBar->setMessage(sprintf('Updating: %s (%d/%d) | Created: %d | Skipped: %d', 
                    $themeCode, 
                    $processedThemes, 
                    count($this->themes), 
                    $createdStores, 
                    $skippedStores
                ));
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
            $this->runThemeInstaller($theme, $store, $output, $progressBar);
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL);

        $output->writeln('');
        $output->writeln('<fg=cyan>╔═══════════════════════════════════════════════════════════════════════════════╗</>');
        $output->writeln('<fg=cyan>║</> <fg=white;options=bold>                            📊 SUMMARY REPORT                               </> <fg=cyan>║</>');
        $output->writeln('<fg=cyan>╠═══════════════════════════════════════════════════════════════════════════════╣</>');
        $output->writeln('<fg=cyan>║</> <fg=green;options=bold>✅ Created:</> <fg=white;options=bold>' . str_pad($createdStores, 3, ' ', STR_PAD_LEFT) . '</> <fg=gray>store views</> <fg=cyan>                                                 ║</>');
        $output->writeln('<fg=cyan>║</> <fg=yellow;options=bold>⏭️  Skipped:</> <fg=white;options=bold>' . str_pad($skippedStores, 3, ' ', STR_PAD_LEFT) . '</> <fg=gray>existing ones</> <fg=cyan>                                               ║</>');
        $output->writeln('<fg=cyan>║</> <fg=blue;options=bold>📋 Total:</> <fg=white;options=bold>' . str_pad(count($this->themes), 5, ' ', STR_PAD_LEFT) . '</> <fg=gray>themes processed</> <fg=cyan>                                            ║</>');
        $output->writeln('<fg=cyan>╚═══════════════════════════════════════════════════════════════════════════════╝</>');
        $output->writeln('');

        if ($createdStores > 0) {
            $output->writeln('<fg=blue;options=bold>🔧 Post-processing steps:</> <fg=gray>Running required maintenance tasks...</>');
            $output->writeln('');
            
            $output->writeln('<fg=magenta;options=bold>🚀 Step 1/3:</> <fg=white>Running setup:upgrade...</>');
            $this->runCommand('php -d memory_limit=-1 bin/magento setup:upgrade --safe-mode=1', $output);

            $output->writeln('<fg=magenta;options=bold>🔍 Step 2/3:</> <fg=white>Running reindex...</>');
            $this->runCommand('php -d memory_limit=-1 bin/magento indexer:reindex', $output);

            $output->writeln('<fg=magenta;options=bold>🧹 Step 3/3:</> <fg=white>Clearing cache...</>');
            $this->clearCache();
            $output->writeln('<fg=green;options=bold>✅ Cache cleared successfully!</>');
        }

        $output->writeln('');
        $output->writeln('<fg=green;options=bold>🎉 SUCCESS!</> <fg=white;options=bold>Theme store views setup completed successfully!</>');
        $output->writeln('<fg=cyan>╔═══════════════════════════════════════════════════════════════════════════════╗</>');
        $output->writeln('<fg=cyan>║</> <fg=white;options=bold>                        🌟 PROCESS COMPLETE 🌟                              </> <fg=cyan>║</>');
        $output->writeln('<fg=cyan>╚═══════════════════════════════════════════════════════════════════════════════╝</>');
        $output->writeln('');

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Run theme installer if marketplace installer.xml exists
     */
    private function runThemeInstaller($theme, $store, OutputInterface $output, ProgressBar $progressBar)
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
            $progressBar->clear();
            $output->writeln('<fg=blue;options=bold>🔧 Found installer for theme:</> <fg=white>' . $theme->getThemeTitle() . '</>');
            $progressBar->display();
            
            // Get theme package name from installer.xml
            $xml = simplexml_load_file($installerPath);
            if ($xml && isset($xml->packages->package)) {
                foreach ($xml->packages->package as $package) {
                    $packageName = (string)$package;
                    if (strpos($packageName, 'theme-frontend-') !== false) {
                        $storeId = $store->getId();
                        $progressBar->clear();
                        $output->writeln('<fg=cyan>📦 Installing package:</> <fg=yellow>' . $packageName . '</>');
                        $this->runCommand("php bin/magento marketplace:package:install {$packageName} --store={$storeId} --no-interaction", $output);
                        $progressBar->display();
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
        $output->writeln('<fg=gray>💻 Executing:</> <fg=white>' . $command . '</>');
        
        $process = popen($command, 'r');
        if ($process) {
            while (!feof($process)) {
                $line = fgets($process);
                if ($line) {
                    $output->write('<fg=cyan>' . rtrim($line) . '</>' . PHP_EOL);
                }
            }
            pclose($process);
        }
        $output->writeln('<fg=green;options=bold>✅ Command completed!</>');
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
