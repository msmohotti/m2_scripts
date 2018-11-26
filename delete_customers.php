<?php

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

/**
 * Class DataImportCsv
 *
 */
class DataImportCsv
{
    private $filename = 'cust.csv';

    private $logFilename = 'cust_delete.log';

    private $customerRepository;

//    private $productResource;

    private $objectManager;

    public function __construct($filename)
    {
        $bootstrap = Bootstrap::create(BP, $_SERVER);

        $this->objectManager = $bootstrap->getObjectManager();

        $state = $this->objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('adminhtml');

        /** @var Magento\Catalog\Model\customerRepository $customerRepository */
        $this->customerRepository = $this->objectManager->get('Magento\Customer\Model\Customer');

        $this->objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);


        /** @var \Magento\Catalog\Model\ResourceModel\Product $resourceProduct */
//        $this->productResource = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\ProductFactory')->create();

        $this->filename = $filename;
    }

    private function process()
    {
        $csvFile = file($this->filename);
        $logFile = fopen($this->logFilename, 'w');

        foreach ($csvFile as $line) {
            $data = null;
            $data = str_getcsv($line);
            try {
                $cust = $this->customerRepository->load($data[0]);
                $cust->delete();
                echo $data[0] . PHP_EOL;
                fwrite($logFile, $data[0] . ' - deleted' . PHP_EOL);
            } catch (Exception $e) {
                echo $data[0] . ' - ERROR - ' . $e->getMessage() . PHP_EOL;
            }
        }
    }

    public function run()
    {
        $starttime = microtime(true);
        $this->process();
        echo microtime(true) - $starttime . PHP_EOL;
    }
}

$dataCsv = new DataImportCsv('cust.csv');
$dataCsv->run();
